<?php

namespace App;

class QuestionText extends Model
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
        'selections' => 'array'
    ];

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */

	public function question()
	{
		return $this->belongsTo('App\Question');
	}
}
