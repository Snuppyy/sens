<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
	protected $touches = ['session'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function session()
	{
		return $this->belongsTo('App\Session');
	}
}
