<?php

namespace App;

use App;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
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
        'correct' => 'boolean'
    ];

    protected $appends = ['text'];

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function question()
	{
		return $this->belongsTo('App\Question');
	}
    
    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
	 */

	public function questions()
	{
		return $this->belongsToMany('App\Question')->withPivot('position');
	}

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\hasOne
	 */

	public function option_text($locale = null)
	{
        if(!$locale)
        {
            $locale = App::getLocale();
        }

        return $this->hasOne('App\OptionText')->where('locale', $locale);
    }

    public function getTextAttribute() {
        return $this->option_text ? $this->option_text->text : $this->option_text(config('app.fallback_locale'))->first()->text;
    }
}
