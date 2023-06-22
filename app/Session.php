<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = ['name', 'info'];
	
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    //protected $hidden = ['pivot'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'info' => 'array',
    ];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function editor()
	{
		return $this->belongsTo(User::class, 'editing_user_id');
	}

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */

	public function dataset()
	{
		return $this->hasOne(Dataset::class)->latest();
	}

	/**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */

    public function users($role = null) {
        $relation = $this->belongsToMany(User::class)->using(UserSession::class);

        if($role) {
            $relation->whereRaw('FIND_IN_SET(?, session_user.roles)', [$role]);
        }

        return $relation;
    }

	/**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function overlays() {
        return $this->hasMany(Overlay::class);
    }

	/**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function trainings() {
        return $this->hasMany(Training::class, 'module_id');
    }
}
