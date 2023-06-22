<?php

namespace App;

use App;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
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
    protected $fillable = ['title_ru', 'short_ru', 'text_ru', 'module_id', 'info', 'status', 'dataset', 'comments'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'info' => 'array',
        'dataset' => 'array',
        'comments' => 'array'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['dataset'];

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function module()
	{
        return $this->belongsTo(Session::class);
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function user()
	{
        return $this->belongsTo(User::class);
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */

	public function application()
	{
        return $this->hasOne('App\TrainingApplication');
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */

	public function applications()
	{
        return $this->hasMany('App\TrainingApplication');
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */

	public function tests()
	{
        return $this->hasMany('App\Test');
    }

    public function getTitleAttribute()
    {
        return $this->attributes['title_' . App::getLocale()];
    }

    public function getShortAttribute()
    {
        return $this->attributes['short_' . App::getLocale()];
    }

    public function getTextAttribute()
    {
        return $this->attributes['text_' . App::getLocale()];
    }

    public function getLocalizedInfoAttribute()
    {
        return $this->attributes['info' . (App::getLocale() == 'uz' ? '_uz' : '')];
    }
}
