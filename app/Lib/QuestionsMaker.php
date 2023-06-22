<?php

namespace App\Lib;

use App\Questionnaire;
use App\Question;
use App\QuestionText;
use App\Option;
use App\OptionText;

class QuestionsMaker {
    public static function makeQuestions($id, $dataset) {
		// $data = Source::find(0)->selections;

        // foreach($data['sources'] as $source) {
        //     Source::where('id', $source['id'])->update([
        //         'selections' => json_encode($source['selections'])
        //     ]);
		// }

        Question::where('level', $id)->delete();
        Questionnaire::where('level', $id)->delete();

        $sources = collect($dataset['sources']);

        foreach($dataset['items'] as $item) {
			if(empty($item['questions'])) {
				continue;
			}

			foreach($item['questions'] as $question_data) {
				if(empty($question_data) ||
					empty($question_data['text']['ru']) ||
					empty($question_data['options'])
				) {
					continue;
				}

				$question = new Question;
                $question->level = $id;
                $question->knowledge = $item['knowledge'] ?? null;

                $question->save();

                $question_text = new QuestionText;
                $question_text->question_id = $question->id;
                $question_text->locale = 'ru';
                $question_text->text = $question_data['text']['ru'];

                if(isset($item['knowledge'])) {
					$knowledge = collect($dataset['knowledges'])->where('id', $item['knowledge'])->first();

					if($knowledge && !empty($knowledge['selections']['ru'])) {
						$selections = collect($knowledge['selections']['ru']);

						$source = $selections->first()['source'];

						$selections = $selections
							->where('source', $source)
							->pluck('id');

						$source = $sources->where('id', $source)->first();

						$question_text->source = $source['file']['ru'];
						$question_text->selections = collect(
								$source['selections']['ru']
							)
							->whereIn('id', $selections)
							->values();
					}
                }

                $question_text->save();

                $correct = false;

                foreach($question_data['options'] as $option_data) {
                    if(empty($option_data['text']['ru'])) {
                        continue;
                    }

                    $option = new Option;
                    $option->question_id = $question->id;
                    $option->correct = $option_data['correct'] ?? false;
                    $option->save();

                    if($option->correct) {
                        if($correct) {
                            $question->multiple = true;
                        } else {
                            $correct = true;
                        }
                    }

                    $option_text = new OptionText;
                    $option_text->option_id = $option->id;
                    $option_text->locale = $question_text->locale;
                    $option_text->text = $option_data['text']['ru'];
                    $option_text->save();
                }

                $question->save();
            }
        }

        //return config('app.url') . route('questionnaire', ['level' => $id], false);
	}
}