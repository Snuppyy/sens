<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\User;

class Maintain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some maintainance operation';

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
        $groups = [
            [225, 237, 232, 239, 219, 229],
            [240, 222, 234, 221, 230],
            [231, 218, 238, 242],
            [235, 223, 227, 233, 220],
            [228, 243, 159]
        ];

        $session_ids = [
            134, 139, 138, 137
        ];

        $users = [];

        foreach($groups as $group) {
            //$session_ids = \App\Session::whereIn('user_id', $group)->get()->pluck('id');

            $sessions = [];

            foreach($session_ids as $id) {
                $sessions[$id] = ['roles' => 'reviewer'];
            }

            foreach($group as $user_id) {
                $user = User::find($user_id);

                $users[$user->id] = $user->fio;

                $user->sessions()->sync($sessions);
            }
        }

        echo json_encode($users, JSON_UNESCAPED_UNICODE);

        /*
        $groups = [
            [225, 237, 232, 239, 219, 229],
            [240, 222, 234, 221, 230],
            [231, 218, 238, 242],
            [235, 223, 227, 233, 220]
        ];
        */

        echo "Finished.\n";
    }
}
