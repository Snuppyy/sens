<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Knowledge extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function source()
	{
        return $this->belongsTo(Source::class);
	}
}
