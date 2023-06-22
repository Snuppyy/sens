<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Excel;
use App\Imports\TranslationsImport as Import;

class TranslationsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CSV for translation';

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
        // \Maatwebsite\Excel\Facades\
        $translations = new Import;
        Excel::import($translations, $this->argument('file'));

        file_put_contents(resource_path('lang/uz.json'), json_encode($translations->translations['uz'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        file_put_contents(resource_path('lang/uz-cyr.json'), json_encode($translations->translations['uz-cyr'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        echo "Finished.\n";
    }
}
