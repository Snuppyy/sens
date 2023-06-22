<?php

namespace App\Exports;

use App\Question;
use App\Option;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class QuestionsSheet implements FromCollection, WithTitle
{
    private $level;

    public function __construct(int $level)
    {
        $this->level = $level;
    }

    public function collection()
    {
        $res = [];

        foreach(Question::where('level', $this->level)->get() as $question) {
            $q = [$question->text];

            foreach($question->options as $option) {
                $q[] = ($option->correct ? '*' : '') . $option->text;
            }

            $res[] = $q;
        }

        return collect($res);
    }

    public function title(): string
    {
        return 'Уровень ' . $this->level;
    }
}