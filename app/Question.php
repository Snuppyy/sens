<?php

namespace App;

use App;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
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
        'multiple' => 'boolean'
    ];

    protected $appends = ['text'];

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\hasMany
	 */

	public function options()
	{
		return $this->hasMany('App\Option');
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
	 */

	public function questionnaireOptions()
	{
        return $this->belongsToMany('App\Option', 'option_questionnaire')
            ->withPivot(['selected'])
            ->orderBy('position');
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\hasOne
	 */

	public function question_text($locale = null)
	{
        if(!$locale)
        {
            $locale = App::getLocale();
        }

        return $this->hasOne('App\QuestionText')->where('locale', $locale);
    }

    public function getTextAttribute() {
        return $this->question_text->text;
    }

    public function getFragmentAttribute() {
        return $this->question_text->fragment;
    }

    public function getSourceAttribute() {
        return $this->question_text->source;
    }

    public function getSelectionsAttribute() {
        return $this->question_text->selections;
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\hasMany
	 */

	public function results()
	{
		return $this->hasMany('App\QuestionQuestionnaire')->whereHas('questionnaire', function($query) {
            $query->whereNotNull('finished_at');
        });
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\hasMany
	 */

	public function firstResults()
	{
		$table = (new Questionnaire())->getTable();

        return $this->hasMany('App\QuestionQuestionnaire')->whereHas('questionnaire', function($query) use ($table) {
            $query->leftJoin("$table as q", function($join) use ($table) {
                    $join->on('q.user_id', "$table.user_id")
                        ->on('q.level', "$table.level")
                        ->on('q.created_at', '<', "$table.created_at")
                        ->whereNotNull("q.finished_at");
                })
                ->whereNull('q.id')
                ->whereNotNull("$table.finished_at");
        });
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\hasMany
	 */

	public function lastResults()
	{
		$table = (new Questionnaire())->getTable();

        return $this->hasMany('App\QuestionQuestionnaire')->whereHas('questionnaire', function($query) use ($table) {
            $query->leftJoin("$table as q", function($join) use ($table) {
                    $join->on('q.user_id', "$table.user_id")
                        ->on('q.level', "$table.level")
                        ->on('q.created_at', '>', "$table.created_at")
                        ->whereNotNull("q.finished_at");
                })
                ->whereNull('q.id')
                ->whereNotNull("$table.finished_at");
        });
    }
}
