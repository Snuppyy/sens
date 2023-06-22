<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserSession extends Pivot
{
    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */

	public function user()
	{
		return $this->belongsTo(User::class);
	}

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */

	public function session()
	{
		return $this->belongsTo(Session::class);
	}

    public function getRolesAttribute() {
        return explode(',', $this->attributes['roles']);
    }

    public function setRolesAttribute($roles) {
        $this->attributes['roles'] = is_array($roles) ? implode(',', $roles) : $roles;
    }
}
