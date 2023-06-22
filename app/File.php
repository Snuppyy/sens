<?php

namespace App;

use Storage;
use Illuminate\Database\Eloquent\Model;

use App\Events\FileDeleted;

class File extends Model
{
    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'deleted' => FileDeleted::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_id', 'file', 'name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'session_id', 'file', 'created_at', 'updated_at'
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
	{
		return $this->file ? Storage::url($this->file) : null;
	}

	public function setFileAttribute($file) {
		if($this->file) {
			Storage::delete($this->file);
		}

		if($file) {
			$this->attributes['file'] = $file->store('materials');
		} else {
			$this->attributes['file'] = null;
		}
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
