<?php

namespace App;

use App;
use Carbon;
use CarbonInterval;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    protected $casts = [
        'question_index' => 'integer',
        'participate' => 'boolean',
        'drawing' => 'boolean',
        'win' => 'boolean'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'finished_at',
    ];

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

    public function user()
	{
		return $this->belongsTo('App\User');
	}

    /**
	 * @return App\Question
	 */

	public function getQuestionAttribute()
	{
		return $this->questions->get($this->question_index);
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
	 */

	public function questions()
	{
        return $this->belongsToMany('App\Question')
                ->using('App\QuestionQuestionnaire')
                ->withPivot(['position', 'answered', 'dontknow', 'correct'])
                ->orderBy('question_questionnaire.position');
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
	 */

	public function options($question_id = null)
	{
        $query = $this->belongsToMany('App\Option', 'option_questionnaire', 'questionnaire_id', 'option_id', 'id')
            ->withPivot(['question_id', 'position', 'selected'])
            ->orderBy('option_questionnaire.position');

        if($this->exists && !$question_id) {
            $question_id = $this->question->id;
        }

        if($question_id) {
            $query->wherePivot('question_id', $question_id);
        }

        return $query;
	}

    public function setClosedAttribute($value)
    {
        $this->attributes['closed'] = $value;
        $this->finished_at = Carbon::now();
    }

    public function scopeToday($query)
    {
        $interval = CarbonInterval::fromString(config('app.begin_time'));

        $today = Carbon::today()->add($interval);

        $dates = Carbon::today()->diffAsCarbonInterval(Carbon::now())->compare($interval) >= 0 ? [
            $today,
            Carbon::tomorrow()->add($interval)
        ] : [
            Carbon::yesterday()->add($interval),
            $today
        ];

        return $query->whereBetween('finished_at', $dates);
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */

	public function test()
	{
        return $this->belongsTo(Test::class, 'level');
    }
}
