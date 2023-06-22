<?php

namespace App\Imports;

use App\Question;
use App\QuestionText;
use App\Option;
use App\OptionText;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\OnEachRow;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;

class QuestionsImport implements OnEachRow, WithEvents
{
    use RegistersEventListeners;

    private static $level;
    private static $question;

    public static function beforeSheet(BeforeSheet $event)
    {
        if(!self::$level) {
            self::$level = 3;
        } else {
            self::$level--;
        }
    }
    
    public function onRow(\Maatwebsite\Excel\Row $row)
    {
        $row = $row->getDelegate();

        if(1 == $row->getRowIndex()) {
            return;
        }

        if(100 == $row->getRowIndex()) {
            sleep(0);
        }

        $cells = $row->getCellIterator('C');
        $cells->setIterateOnlyExistingCells(true);

        $new_question = ($row->getRowIndex() - 2) % 3 == 0;

        if($new_question) {
            if(self::$question) {
                self::$question->save();
            }

            self::$question = new Question;
            self::$question->level = self::$level;
            self::$question->save();
        }

        $question_text = new QuestionText;
        $question_text->question_id = self::$question->id;
        $question_text->locale = ['РУС' => 'ru', 'ЛАТ' => 'uz', 'УЗБ' => 'uz-cyr'][$cells->current()->getValue()];

        $cells->next();
        $cells->next();

        $question_text->fragment = trim($cells->current()->getValue());

        $cells->next();

        if($new_question && 'C00000' == $cells->current()->getStyle()->getFill()->getStartColor()->getRGB()) {
            self::$question->marked = 1;
        }

        $text = $cells->current()->getValue();

        if($text instanceof RichText) {
            $question_text->text = '';

            foreach($text->getRichTextElements() as $element) {
                if($element instanceof Run && 'FF0000' == $element->getFont()->getColor()->getRGB()) {
                    if($new_question) {
                        self::$question->multiple = strpos($element->getText(), 'один') === false &&
                                                    strpos($element->getText(), 'битта') === false &&
                                                    strpos($element->getText(), 'bitta') === false;
                    }
                } else {
                    $question_text->text .= $element->getText();
                }
            }

            $question_text->text = trim($question_text->text);
        } else {
            $question_text->text = trim($text);
        }

        $question_text->save();

        $cells->resetStart('G');

        $i = 0;
        foreach($cells as $cell) {
            $text = trim($cell->getValue());

            if(empty($text) || mb_strtolower($text) == 'не знаю' ||
                    mb_strtolower($text) == 'билмайман' || mb_strtolower($text) == 'bilmayman') {
                continue;
            }

            if($new_question) {
                $option = new Option;
                $option->question_id = self::$question->id;
                $option->correct = 'FF12DC17' == $cell->getStyle()->getFill()->getStartColor()->getARGB();
                $option->save();
            } else {
                $option = self::$question->options->get($i++);
            }

            $option_text = new OptionText;
            $option_text->option_id = $option->id;
            $option_text->locale = $question_text->locale;
            $option_text->text = $text;
            $option_text->save();
        }
    }
}