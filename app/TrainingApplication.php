<?php

namespace App;

use App;
use Illuminate\Database\Eloquent\Model;

class TrainingApplication extends Model
{
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
        'application' => 'array',
        'selected' => 'int'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'updated_at',
        'viewed_at'
    ];

    protected $appends = ['viewed'];

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function user()
	{
        return $this->belongsTo('App\User');
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function training()
	{
        return $this->belongsTo('App\Training');
    }

    public function getReadableStatusAttribute() {
        return [
            'draft' => __('черновик'),
            'applied' => __('подана'),
            'consideration' => __('рассматривается'),
            'accepted' => __('принята'),
            'rejected' => __('отклонена')
        ][$this->status];
    }

    public function getViewedAttribute() {
        return $this->updated_at->lessThanOrEqualTo($this->viewed_at);
    }
}
