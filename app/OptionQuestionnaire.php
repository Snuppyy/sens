<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OptionQuestionnaire extends Pivot
{
    //public $questionnaire_id;

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */

	public function question()
	{
		return $this->belongsTo('App\Question');
	}

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */

	public function questionnaire()
	{
		return $this->belongsTo('App\Questionnaire');
	}

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */

	public function option()
	{
		return $this->belongsTo('App\Option');
	}
}
