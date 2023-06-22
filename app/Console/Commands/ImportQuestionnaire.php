<?php

namespace App\Console\Commands;

use App;
use App\Option;
use App\OptionText;
use App\Question;
use App\QuestionText;
use Exception;
use Illuminate\Console\Command;
use Storage;

class ImportQuestionnaire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-questionnaire {level} {filenameRu} {filenameUz?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import questionnaire';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $output = [];

        $inputRu = explode("\n\n", Storage::get($this->argument('filenameRu')));
        $inputUz = explode("\n\n", Storage::get($this->argument('filenameUz')));

        foreach($inputRu as $index => $inputQuestion) {
            $matchesRu = [];

            preg_match('/\s*\d+\.\s*(.*)\n([\w\W]+)/u', $inputQuestion, $matchesRu);
            $textRu = $matchesRu[1];
            preg_match_all('/\s*(\*)?\s*\d+\)\s*(.*)\n?/u', $matchesRu[2], $matchesRu);

            $matchesUz = [];

            preg_match('/\s*\d+\.\s*(.*)\n([\w\W]+)/u', $inputUz[$index], $matchesUz);
            $textUz = $matchesUz[1];
            preg_match_all('/\s*(\*)?\s*\d+\)\s*(.*)\n?/u', $matchesUz[2], $matchesUz);

            $output[] = ($index + 1) . '. ' . $textRu . "\n" . $textUz . "\n" .
            collect($matchesRu[2])->map(function($option, $index) use ($matchesRu, $matchesUz) {
                if (!isset($matchesUz[1][$index])) {
                    dd([$matchesRu, $matchesUz]);
                    throw new Exception("shitt $index");
                }

                if ($matchesRu[1][$index] != $matchesUz[1][$index]) {
                    throw new Exception("shit $index");
                }

                return ($matchesRu[1][$index] ? '* ' : '  ') . ($index + 1) . ') ' . $option . "\n" .
                    ($matchesUz[1][$index] ? '* ' : '  ') . ($index + 1) . ') ' . $matchesUz[2][$index];
            })->implode("\n");

            continue;

            $question = new Question;
            $question->level = $this->argument('level');
            $question->save();

            $text = new QuestionText;
            $text->question_id = $question->id;
            $text->locale = 'ru';
            $text->text = $textRu;
            $text->save();

            $text = new QuestionText;
            $text->question_id = $question->id;
            $text->locale = 'uz';
            $text->text = $textUz;
            $text->save();

            $hadCorrect = false;

            foreach ($matchesRu[2] as $index => $optionTextRu) {
                $correct = (bool) $matchesRu[1][$index];

                if ($correct && $hadCorrect) {
                    $question->multiple = true;
                    $question->save();
                }

                $hadCorrect = $correct;

                $option = new Option;
                $option->question_id = $question->id;
                $option->correct = $correct;
                $option->save();

                $text = new OptionText;
                $text->option_id = $option->id;
                $text->locale = 'ru';
                $text->text = $optionTextRu;
                $text->save();

                $text = new OptionText;
                $text->option_id = $option->id;
                $text->locale = 'uz';
                $text->text = $matchesUz[2][$index];
                $text->save();
            }
        }

        echo implode("\n\n\n", $output);

        echo "Done!\n";
    }
}
