<?php

namespace App\Console\Commands;

use App;
use App\Question;
use Illuminate\Console\Command;
use Nexmo\Message\Query;
use Storage;

class ExportQuestionnaire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export-questionnaire {filename} {level} {lang}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export questionnaire';

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
        App::setLocale($this->argument('lang'));

        $output = [];

        foreach(Question::where('level', $this->argument('level'))->get() as $index => $question) {
            $output[] = ($index + 1) . '. ' . $question->text . "\n\n" .
                $question->options->map(function($option, $index) {
                    return ($option->correct ? '* ' : '  ') . ($index + 1) . ') ' . $option->text;
                })->implode("\n");
        }

        Storage::put($this->argument('filename'), implode("\n\n\n", $output));

        echo "Done!\n";
    }
}
