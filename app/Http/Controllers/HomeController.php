<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use App\Questionnaire;
use App\Test;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $demo = $user->trainingApplications()->where('training_id', 55)->count();

        $tests = Test::where('enabled', 1)
            ->where(function($query) {
                $query->whereNull('starts')
                    ->orWhere('starts', '<=', Carbon::now());
            })
            ->where(function($query) {
                $query->whereNull('ends')
                    ->orWhere('ends', '>=', Carbon::now());
            })
            ->when(
                $demo,
                function($query) {
                    $query->whereNotNull('training_id');
                }
            )
            ->where(function($query) use ($user) {
                $query->whereNull('training_id')
                    ->orWhereHas('training', function($query) use ($user) {
                        $query->whereHas('applications', function($query) use ($user) {
                            $query->where('user_id', $user->id)
                                ->where('status', 'accepted');
                        });
                    });       
            })
            ->with(['training'])
            ->get();

        return view('home', [
            'user' => $user,
            'seconds_1' => $user->questionnaire1 ? (config($user->questionnaire1->training_started ? 'app.round_length' : 'app.wait_without_training')
                            - $user->questionnaire1->updated_at->diffInSeconds(Carbon::now())) : 0,
            'seconds_2' => $user->questionnaire2 ? (config($user->questionnaire2->training_started ? 'app.round_length' : 'app.wait_without_training')
                            - $user->questionnaire2->updated_at->diffInSeconds(Carbon::now())) : 0,
            'seconds_3' => $user->questionnaire3 ? (config($user->questionnaire3->training_started ? 'app.round_length' : 'app.wait_without_training')
                            - $user->questionnaire3->updated_at->diffInSeconds(Carbon::now())) : 0,
            'tests' => $tests,
            'demo' => $demo
        ]);
    }

    /**
     * Show the winners list.
     *
     * @return \Illuminate\Http\Response
     */
    public function welcome()
    {
        $winners = [
            Carbon::now()->addDay(1)->sub(CarbonInterval::fromString(config('app.begin_time')))->format('d.m.Y') => [
                1 => [__('Следующий розыгрыш'), '-'],
                2 => [__('Следующий розыгрыш'), '-'],
                3 => [__('Следующий розыгрыш'), '-']
            ],
            '14.12.2018' => [],
            '13.12.2018' => [],
            '12.12.2018' => []
        ];
        
        Questionnaire::where('win', 1)
            ->with('user.city')
            ->latest('finished_at')
            ->get()
            ->each(function ($item) use (&$winners) {
                $date = $item->finished_at->addDay(1)->sub(CarbonInterval::fromString(config('app.begin_time')))->format('d.m.Y');

                if(!isset($winners[$date])) {
                    $winners[$date] = [];
                }

                if(!isset($winners[$date][$item->level])) {
                    $winners[$date][$item->level] = [];
                }

                $winners[$date][$item->level][] = mb_convert_case($item->user->firstname, MB_CASE_TITLE) . ' ' .
                    mb_convert_case($item->user->lastname, MB_CASE_TITLE) . ($item->user->province ? ' (' . trim($item->user->province->ru) . ')' : '');
            });

        krsort($winners);

        return view('welcome', [
            'winners' => $winners
        ]);
    }
}
