<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'selections' => 'array'
    ];

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */

	public function knowledges()
	{
        return $this->hasMany(Knowledge::class);
	}
}
