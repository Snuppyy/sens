<?php

namespace App\Http\Controllers;

use Cookie;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonInterval;

use App\User;
use App\Questionnaire;
use App\Question;
use App\QuestionQuestionnaire;
use App\OptionQuestionnaire;
use App\Session;
use App\Test;

use App\Events\TestResultsUpdated;

use App\Imports\QuestionsImport;
use App\Training;

class QuestionnaireController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth')->except(['index', 'answer', 'finish', 'training', 'compare']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $level)
    {
        $test = Test::find($level);

        if(!$test ? $level > 3 :
            ($test->starts && $test->starts->greaterThan(Carbon::now())) ||
            ($test->ends && $test->ends->lessThan(Carbon::now()))
        ) {
            // return redirect()->route('home')->withErrors(__('Опросник не доступен'));
        }

        $user = $this->getUser($request);

        if(!$request->user()) {
            $level = 1;
        }

        $questionnaire = $user->questionnaire($level)->first();

        // if($questionnaire && $questionnaire->closed && $test) {
        //     return redirect()->route('home')->withErrors(__('Вы уже прошли этот опросник'));
        // }

        /*
        $topQuestionnaire = $user->topQuestionnaire($level)->first();

        if($level > $user->level || ($topQuestionnaire && $topQuestionnaire->result == 100 &&
                ($questionnaire && $questionnaire->id != $topQuestionnaire->id))) {
            return redirect('/home');
        }
        */

        if(!$questionnaire || ((!$test || !$test->training || $test->training_id == 55) && $questionnaire->training_finished)) {
            $questions = Question::where('level', $level)->get();

            $questionnaire = new Questionnaire;
            $questionnaire->user_id = $user->id;
            $questionnaire->level = $level;
            $questionnaire->questions_count = count($questions);
            $questionnaire->question_index = 0;
            $questionnaire->save();

            $questions = $questions->shuffle()->map(function($item, $key) use ($questionnaire) {
                return [
                    'questionnaire_id' => $questionnaire->id,
                    'question_id' => $item->id,
                    'position' => $key,
                ];
            })->toArray();

            QuestionQuestionnaire::insert($questions);

            $questionnaire->load('questions.options');

            $options = $questionnaire->questions->reduce(function($options, $question) {
                return array_merge(
                    $options,
                    $question->options->shuffle()->map(function($item, $key) use ($question) {
                        return [
                            'option_id' => $item->id,
                            'question_id' => $question->id,
                            'questionnaire_id' => $question->pivot->questionnaire_id,
                            'position' => $key
                        ];
                    })->toArray()
                );
            }, []);

            OptionQuestionnaire::insert($options);
        } elseif($questionnaire->training_finished) {
            // return redirect()->route('questionnaire', ['level' => $level]);
            return redirect('/home');
        } elseif($questionnaire->training_started) {
            $elapsed_time = $questionnaire->updated_at->diffInSeconds(Carbon::now());

            $questionnaire->load([
                'options' => function($query) use ($questionnaire) {
                    $query->wherePivot('question_id', $questionnaire->question->id);
                },
                'options.option_text'
            ]);

            $questionnaire->touch();

            return view('training', [
                'level' => $level,
                'result' => $questionnaire->result,
                'questionnaire' => $questionnaire,
                'seconds' => config('app.training_timeout') - $elapsed_time,
            ]);
        } elseif($questionnaire->closed) {
            $retry_after = config('app.wait_without_training');
            $retry_after_training = config('app.round_length');

            return view('finished', [
                'level' => $level,
                'result' => $questionnaire->result,
                'compare' => $user->questionnaires($level)->count() > 1,
                'retry_after' => CarbonInterval::hours(floor($retry_after / 3600))->minutes(floor(($retry_after % 3600) / 60))->seconds($retry_after % 60),
                'retry_after_training' => CarbonInterval::hours(floor($retry_after_training / 3600))->minutes(floor(($retry_after_training % 3600) / 60))->seconds($retry_after_training % 60),
                'module' => Session::find($questionnaire->level)
            ]);
        }

        $elapsed_time = $questionnaire->updated_at->diffInSeconds(Carbon::now());

        $questionnaire->load([
            'options' => function($query) use ($questionnaire) {
                $query->wherePivot('question_id', $questionnaire->question->id);
            },
            'options.option_text'
        ]);

        return view('questionnaire', [
            'level' => $level,
            'questionnaire' => $questionnaire,
            'seconds' => config('app.question_timeout') - $elapsed_time,
            'ask' => $user->skip_ask
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function answer(Request $request)
    {
        $level = $request->input('level');

        $test = Test::find($level);

        if(!$test ? $level > 3 :
            ($test->starts && $test->starts->greaterThan(Carbon::now())) ||
            ($test->ends && $test->ends->lessThan(Carbon::now()))
        ) {
            // return redirect()->route('home')->withErrors(__('Опросник не доступен'));
        }

        $user = $this->getUser($request);

        /*
        if($level > $user->level) {
            return redirect('/home');
        }

        if($request->input('dont-ask')) {
            $user->skip_ask = 0;
            $user->save();
        }
        */

        $questionnaire = $user->questionnaire($level)->first();

        if($questionnaire && $questionnaire->id == $request->input('questionnaire') &&
                $questionnaire->question->pivot->position == $request->input('question')) {

            if(!$questionnaire->closed) {
                $answers = $request->input('answers');

                if(!empty($answers)) {
                    $selected = 0;
                    $correct_selected = 0;

                    $questionnaire->options()
                        ->wherePivotIn('position', $request->input('answers'))
                        ->get()
                        ->each(function($option) use ($questionnaire, &$selected, &$correct_selected) {
                            $questionnaire->options()->updateExistingPivot($option->id, ['selected' => 1]);

                            $selected++;
                            if($option->correct) {
                                $correct_selected++;
                            }
                        });

                    $dontknow = !empty($request->input('answers')) && in_array('-1', $request->input('answers')) ? 1 : null;

                    $correct = $selected == $questionnaire->question->options()->where('correct', '1')->count() &&
                                    !$dontknow && $selected == $correct_selected ?
                                1 : null;

                    $questionnaire->questions()->updateExistingPivot($questionnaire->question->id, [
                        'answered' => 1,
                        'dontknow' => $dontknow,
                        'correct' => $correct
                    ]);

                    if($correct) {
                        $questionnaire->result = round($questionnaire->questions()->wherePivot('correct', 1)->count() * (1000 / $questionnaire->questions_count)) / 10;

                        if($questionnaire->result >= 100) {
                            if(false && !$user->winner) {
                                $questionnaire->code = 1 + Questionnaire::where('level', $questionnaire->level)->max('code');
                            }

                            $user->level = $questionnaire->level + 1;
                            $user->save();
                        }
                    }
                }
            }

            if($questionnaire->question_index < $questionnaire->questions_count - 1) {
                $questionnaire->question_index++;

                if($request->problem) {
                    foreach($questionnaire->questions as $index => $question) {
                        if($questionnaire->question_index <= $index && (
                            !$question->pivot->answered ||!$question->pivot->correct
                        )) {
                            $questionnaire->question_index = $index;
                            break;
                        }
                    }
                } else {
                }
            } elseif(!$questionnaire->closed) {
                $questionnaire->closed = 1;
                $questionnaire->question_index = 0;
            } else {
                $questionnaire->training_finished = 1;
            }

            $questionnaire->save();

            if($questionnaire->closed) {
                $this->updateResults($level);
            }

            if($questionnaire->training_finished) {
                $this->updateTrainingCount($level);
            }
        }

        return $questionnaire->training_finished ? redirect()->route('home') :
            redirect()->route('questionnaire', ['level' => $questionnaire->level]);
    }

    public function finish(Request $request, $level) {
        $user = $this->getUser($request);

        /*
        if($level > $user->level) {
            return redirect('/home');
        }
        */

        $questionnaire = $user->questionnaire($level)->first();

        if($questionnaire) {
            if(!$questionnaire->closed) {
                $questionnaire->closed = 1;
                $questionnaire->question_index = 0;
                $questionnaire->save();

                $this->updateResults($level);
            } elseif(!$questionnaire->training_finished) {
                $questionnaire->training_finished = 1;
                $questionnaire->question_index = 0;
                $questionnaire->save();

                $this->updateTrainingCount($level);

                return $request->has('restart') ? redirect()->route('questionnaire', ['level' => $level]) : redirect()->route('home');
            }
        }

        return redirect()->route('questionnaire', ['level' => $level]);
    }

    private function updateTrainingCount($level) {
        if($test = Test::find($level)) {
            $test->trainings = Questionnaire::where('level', $level)
                ->where('training_started', 1)
                ->count();

            $test->save();
        }
    }

    private function updateResults($level) {
        if($test = Test::find($level)) {
            $takes = Questionnaire::where('level', $level)
                ->where('closed', 1)
                ->count();

            $test->takes = $takes;

            $test->participants = Questionnaire::where('level', $level)
                ->where('closed', 1)
                ->distinct('user_id')
                ->count('user_id');

            $table = (new Questionnaire())->getTable();

            $test->first_result = Questionnaire::where("$table.level", $level)
                ->where("$table.closed", 1)
                ->leftJoin("$table as q", function($join) use ($table) {
                    $join->on('q.user_id', "$table.user_id")
                        ->on('q.created_at', '<', "$table.created_at")
                        ->on('q.level', "$table.level");
                    })
                ->whereNull('q.id')
                ->avg("$table.result");

            $last_result = Questionnaire::where("$table.level", $level)
                ->where("$table.closed", 1)
                ->leftJoin("$table as q", function($join) use ($table) {
                    $join->on('q.user_id', "$table.user_id")
                        ->on('q.created_at', '>', "$table.created_at")
                        ->on('q.level', "$table.level");
                    })
                ->whereNull('q.id')
                ->avg("$table.result");

            $test->last_result = $last_result;

            $test->save();

            if($test->training_id) {
                $dataset = $test->training->dataset;

                foreach($dataset['items'] as $index => &$item) {
                    if(!empty($item['test_id']) && $item['test_id'] == $test->id) {
                        $item['results'] = [
                            'count' => $takes,
                            'total' => round($last_result)
                        ];
                    }
                }

                $test->training->dataset = $dataset;
                $test->training->timestamps = false;
                $test->training->save();

                broadcast(new TestResultsUpdated($test->training_id, $level, $takes, round($last_result)))->toOthers();
            }
        }
    }

    public function training(Request $request, $level) {
        $user = $this->getUser($request);

        /*
        if($level > $user->level) {
            return redirect('/home');
        }
        */

        $questionnaire = $user->questionnaire($level)->first();

        if($questionnaire &&
                $questionnaire->closed &&
                !$questionnaire->training_started &&
                !$questionnaire->training_finished) {

            $questionnaire->training_started = 1;
            $questionnaire->save();
        }

        return redirect()->route('questionnaire', ['level' => $level]);
    }

    public function compare(Request $request, $level, $question = null) {
        $user = $request->user();

        $questionnaires = $user->questionnaire($level)->limit(2)->get()
                            ->reverse()->values()->all();

        return view('compare', [
            'level' => $level,
            'questionnaires' => $questionnaires,
            'viewed_question' => $question ? $questionnaires[0]->questions->get($question - 1) : null,
        ]);
    }

    public function participate(Request $request, $level, $answer = 'yes') {
        $user = $request->user();

        if($answer == 'yes') {
            $questionnaire = $user->questionnaire($level)->first();

            if($user->complete) {
                $questionnaire->drawing = true;
                $questionnaire->save();

                return redirect('/home');
            } else {
                $questionnaire->participate = true;
                $questionnaire->save();

                return redirect()->route('profile', ['require' => 'require']);
            }
        } else {
            return redirect('/home');
        }
    }

    private function getUser($request) {
        $user = $request->user();

        if(!$user) {
            if($user = $request->cookie('nova-user')) {
                $user = User::find($user);
            }

            if(!$user) {
                $user = new User;
                $user->save();

                Cookie::queue('nova-user', $user->id, 60 * 24 * 90);
            }
        }

        return $user;
    }

    public function history(Request $request) {
        return view('history', [
            'questionnaires' => $request->user()->questionnaires
        ]);
    }

    public function import()
    {
        \Maatwebsite\Excel\Facades\Excel::import(new QuestionsImport, '2018-12-11.xlsx');

        return 'ok';
    }

    public function groupCompare(Training $training, Question $viewedQuestion1 = null, Question $viewedQuestion2 = null, $user_id = null) {
        if ($training->tests->count() < 2) {
            return redirect()->route('home')->withErrors(__('Неверная конфигурация'));
        }

        $results = [];

        $questions1 = Question::where('level', $training->tests[0]->id)->get();
        $questions2 = Question::where('level', $training->tests[1]->id)->get();

        foreach($questions1 as $i => $question1) {
            $all1 = $question1->firstResults->count();

            $result1 = $all1 ? round($question1->firstResults()->where('correct', 1)->count() / $all1 * 100) : 0;

            $question2 = $questions2[$i];

            $all2 = $question2->firstResults()->count();

            $result2 = $all2 ? round($question2->firstResults()->where('correct', 1)->count() / $all2 * 100) : 0;

            $results[] = [
                'id' => $question1->id,
                'id2' => $question2->id,
                'text' => $question1->text,
                'count1' => $all1,
                'result1' => $result1,
                'count2' => $all2,
                'result2' => $result2
            ];
        }

        $details = [];

        if($viewedQuestion1) {
            foreach($viewedQuestion1->firstResults()->with(['questionnaire', 'questionnaire.user'])->get() as $result) {
                $user = $result->questionnaire->user;

                if(!isset($details[$user->id])) {
                    $details[$user->id] = [];
                }

                $selected = $result->questionnaire->options($viewedQuestion1->id)->where('selected', 1)->first();
                $result['selected'] = $selected ? $selected->text : null;

                $details[$user->id]['result1'] = $result;
            }

            foreach($viewedQuestion2->firstResults()->with(['questionnaire', 'questionnaire.user'])->get() as $result) {
                $user = $result->questionnaire->user;

                if(!isset($details[$user->id])) {
                    $details[$user->id] = [];
                }

                $selected = $result->questionnaire->options($viewedQuestion2->id)->where('selected', 1)->first();
                $result['selected'] = $selected ? $selected->text : null;

                $details[$user->id]['result2'] = $result;
            }
        }

        return view('group-compare', [
            'session' => $training->id,
            'results' => $results,
            'details' => $details,
            'viewed_question' => $viewedQuestion1,
            'viewed_question2' => $viewedQuestion2,
            'user' => $user_id,
        ]);
    }

    public function map(Questionnaire $questionnaire) {
        $sources = collect($questionnaire->questions)
            ->map(function($item) {
                return !empty($item->source) && !empty($item->selections) ? [
                    'source' => $item->source,
                    'selections' => $item->selections,
                    'correct' => !!$item->pivot->correct,
                    'incorrect' => !$item->pivot->correct && $item->pivot->answered
                ] : null;
            })
            ->filter()
            ->mapToGroups(function($item) {
                $source = $item['source'];
                unset($item['source']);

                return [$source => $item];
            });

        return view ('map', [
            'questionnaire' => $questionnaire,
            'sources' => $sources
        ]);
    }
}
