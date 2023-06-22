<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Overlay extends Model
{
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function session()
	{
		return $this->belongsTo(Session::class);
	}
}
