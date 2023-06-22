<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TranslationsImport implements ToCollection
{
    public $translations;

    public function __construct() {
        $this->translations = [
            'uz' => [],
            'uz-cyr' => []
        ];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if(!empty($row[1]) && strpos($row[1], '=A') !== 0) {
                $this->translations['uz'][$row[0]] = trim($row[1]);
            }

            if(!empty($row[2]) && strpos($row[2], '=A') !== 0) {
                $this->translations['uz-cyr'][$row[0]] = trim($row[2]);
            }
        }
    }
}