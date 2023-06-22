<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class QuestionQuestionnaire extends Pivot
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
}
