<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'training_id',
        'index'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'starts',
        'ends',
    ];

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function training()
	{
        return $this->belongsTo(Training::class);
	}

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */

	public function user()
	{
        return $this->belongsTo(User::class);
	}

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */

	public function questions()
	{
        return $this->hasMany(Question::class, 'level');
	}

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */

	public function questionnaires()
	{
        return $this->hasMany(Questionnaire::class, 'level');
	}

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */

	public function participants()
	{
		$table = (new Questionnaire())->getTable();

        return $this->belongsToMany(User::class, $table, 'level')
            ->withPivot(['result', 'finished_at'])
            ->as('questionnaire');
    }
}
