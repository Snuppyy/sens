<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Lib\QuestionsMaker;
use App\Session;
use App\Test;

class MakeTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sens:make-tests {session}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make tests';

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
        $test = Test::create(['index' => 0]);

        $session = Session::findOrFail($this->argument('session'));

        if ($test->wasRecentlyCreated) {
            QuestionsMaker::makeQuestions($session->id, json_decode($session->dataset->data, true));
        }

        $test->title = $session->name;
        $test->enabled = true;
        $test->save();

        echo "Done!\n";
    }
}
