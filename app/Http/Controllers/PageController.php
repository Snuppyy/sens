<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Graze\GuzzleHttp\JsonRpc\Client;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use App\Questionnaire;
use App\Drawing;
use App\Training;

class PageController extends Controller
{
    /**
     * Show the welcome page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('welcome', [
            //'winners' => $winners
        ]);

        if($request->user()) {
            return redirect()->route('home');
        }

        $participants = Questionnaire::today()
            ->where([
                ['result', 100],
                ['code', '<>', 0],
                ['drawing', true]
            ])
            ->whereHas('user', function($query) {
                $query->where('winner', false);
            })
            ->get();

        $winners = [
            /*Carbon::now()->addDay(1)->sub(CarbonInterval::fromString(config('app.begin_time')))->format('d.m.Y') => [
                1 => [
                    ['winner' => __('Следующий розыгрыш'), 'link' => ''],
                    ['winner' => __('Уже :number участников розыгрыша', ['number' => $participants->where('level', 1)->count()]), 'link' => ''],
                ],
                2 => [
                    ['winner' => __('Следующий розыгрыш'), 'link' => ''],
                    ['winner' => __('Уже :number участников розыгрыша', ['number' => $participants->where('level', 2)->count()]), 'link' => ''],
                ],
                3 => [
                    ['winner' => __('Следующий розыгрыш'), 'link' => ''],
                    ['winner' => __('Уже :number участников розыгрыша', ['number' => $participants->where('level', 3)->count()]), 'link' => ''],
                ]
            ],*/
            '14.12.2018' => [],
            '13.12.2018' => [],
            '12.12.2018' => []
        ];

        Questionnaire::where('win', 1)
            ->with('user.province')
            ->latest('finished_at')
            ->get()
            ->each(function ($item) use (&$winners) {
                $date = $item->finished_at->addDay(1)->sub(CarbonInterval::fromString(config('app.begin_time')));
                $day = $date->format('d');
                $date = $date->format('d.m.Y');

                if(!isset($winners[$date])) {
                    $winners[$date] = [];
                }

                if(!isset($winners[$date][$item->level])) {
                    $winners[$date][$item->level] = [];
                }

                $winners[$date][$item->level][] = [
                    'winner' => mb_convert_case($item->user->firstname, MB_CASE_TITLE) . ' ' .
                                mb_convert_case($item->user->lastname, MB_CASE_TITLE) . ($item->user->province ? ' (' . trim($item->user->province->ru) . ')' : ''),
                    'link' => '<a href="' . route('drawing', ['day' => $day, 'level' => $item->level]) . '">' .
                                __('#:code', ['code' => $item->code]) . '</a>'
                ];
            });

        krsort($winners);

        return view('welcome', [
            'winners' => $winners
        ]);
    }

    /**
     * Show the drawing page.
     *
     * @return \Illuminate\Http\Response
     */
    public function drawing($day, $level)
    {
        return view('drawing', [
            'drawing' => Drawing::where('level', $level)->whereDay('created_at', $day)->first()
        ]);
    }

    /**
     * Make the drawing page.
     *
     * @return \Illuminate\Http\Response
     */
    /*
    public function makeDrawing()
    {
        if(Drawing::whereDay('created_at', Carbon::now()->day)->count()) {
            return 'already';
        }

        $out = '';

        $interval = CarbonInterval::fromString(config('app.begin_time'));

        $client = Client::factory('https://api.random.org/json-rpc/1/invoke');

        for($level = 3; $level > 0; $level--) {
            $questionnaires = Questionnaire::where([
                    ['result', 100],
                    ['level', $level],
                    ['code', '<>', 0],
                    ['drawing', true]
                ])
                ->whereBetween('finished_at', [
                    Carbon::yesterday()->add($interval),
                    Carbon::today()->add($interval)
                ])
                ->whereHas('user', function($query) {
                    $query->where('winner', false);
                })
                ->get();

            $min = $questionnaires->min('code');
            $max = $questionnaires->max('code');

            $res = $client->send($client->request(1, 'generateSignedIntegers', [
                'apiKey' => '610dd3ac-6b0c-4ca8-8659-e01a538b5e0b', //'4be32f90-b28d-4801-8a37-f6aa398db32e'
                'n' => $max - $min + 1,
                'min' => $min,
                'max' => $max,
                'replacement' => false,
                'base' => 10
            ]));

            $drawing = new Drawing;
            $drawing->level = $level;
            $drawing->result = $res->getRpcResult();
            $list = collect($drawing->result['random']['data'])->intersect($questionnaires->pluck('code'));
            $drawing->list = $list->implode(',');
            $drawing->save();

            foreach($questionnaires->whereIn('code', $list->take(2)) as $questionnaire) {
                $questionnaire->win = true;
                $questionnaire->timestamps = false;
                $questionnaire->save();
                $questionnaire->user->winner = true;
                $questionnaire->user->save();
                $out .= $questionnaire->user->id . ' ' . $questionnaire->user->firstname . ' ' .
                        $questionnaire->user->lastname . ' ' . $questionnaire->user->phone . ' ' . $questionnaire->user->email . "<br>\n";
            }
        }

        return $out;
    }
    */

    public function dump() {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\QuestionsExport, 'questions.xlsx');
    }

    public function stats($level) {
        return view('stats', [
            'level' => $level,
            'time' => $level == 4 ? '17:45:00' : '21:45:00'
        ]);
    }

    public function trainings(Request $request) {
        return view('trainings.list', [
            'user' => $request->user(),
            'trainings' => Training::where('status', '<>', 'draft')->where('visible', true)->latest()->get()
        ]);
    }

    public function training(Request $request, Training $training) {
        return view('trainings.view', [
            'user' => $request->user(),
            'training' => $training
        ]);
    }
}
