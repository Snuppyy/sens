<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QuestionsExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        $sheets = [];

        for($i = 1; $i <= 3; $i++) {
            $sheets[] = new QuestionsSheet($i);
        }

        return $sheets;
    }
}