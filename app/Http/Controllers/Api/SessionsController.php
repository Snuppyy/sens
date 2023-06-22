<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App;
use Validator;
use DB;
use Carbon;

use App\User;
use App\Source;
use App\Session;
use App\Dataset;
use App\Overlay;
use App\Question;
use App\Questionnaire;
use App\File;

use App\Events\SessionEditorIntercepted;

class SessionsController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $roles = $request->input('roles', '');
        if(!empty($roles)) {
            $roles = explode(',', $roles);
        }
        $owned = $request->has('owned');

        $sessions = Session::with('user')
            ->orderBy($request->input('sort', 'id'),
                        $request->input('desc') ? 'desc' : 'asc')
            ->when($search, function ($query) use ($search) {
                $query->where(function($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
            })
            ->when($owned, function($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->with([
                'users' => function($query) use ($request) {
                    $query->select(['session_user.roles'])->where('session_user.user_id', $request->user()->id);
                }
            ])
            ->when(!in_array('superuser', $request->user()->role) && !$owned, function($query) use ($request, $roles) {
                $query->where(function($query) use ($request, $roles) {
                    $query->when(empty($roles) || in_array('author', $roles),
                                function($query) use ($request) {
                                    $query->where('user_id', $request->user()->id);
                                }
                            )
                            ->orWhereHas('users', function($query) use ($request, $roles) {
                                $query->where('id', $request->user()->id)
                                    ->when(!empty($roles), function($query) use ($request, $roles) {
                                        $query->where(function($query) use ($roles) {
                                            foreach($roles as $role) {
                                                $query->orWhereRaw('FIND_IN_SET(?,roles)', [$role]);
                                            }
                                        });
                                    });
                                });
                });
            });

        $per_page = (int) $request->input('per_page');

        return $sessions->paginate($per_page != -1 && empty($search) ? $per_page : $sessions->count());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validator($request);

        $data = $request->all();

        $session = new Session($data);
        $session->user_id = $request->user()->id;
        $session->save();

        return [
            'id' => $session->id
        ];
    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \App\Session             $session
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Session $session)
	{
        $this->validator($request);

		$data = $request->input();
		$session->update($data);

		return $session;
	}

    /**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\User $user
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, Session $session)
	{
        $user = $request->user();

        if(in_array('superuser', $request->user()->role) || $user->id == $session->user_id) {
            if ($session->delete()) {
                return [];
            }
        }

		return error();
	}

    private function validator(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|max:255',
        ])->validate();
    }

    public function sources() {
        return Source::with('knowledges')->get();
    }

    public function source(Source $source) {
        return $source;
    }

    public function dataset(Request $request, Session $session) {
        /*
        $standalone = $request->user()->id == 13;

        if($standalone) {
        */

        $session->load([
            'users' => function($query) use ($request) {
                $query->select(['session_user.roles'])->where('session_user.user_id', $request->user()->id);
            },
        ]);

        $editor = null;

        if($session->editing_user_id != $request->user()->id) {
            if(config('app.echo_server_id')) {
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', 'http://localhost:'  . config('app.echo_server_port') . '/apps/' . config('app.echo_server_id') .
                    '/channels/presence-session.' . $session->id . '/users?auth_key=' . config('app.echo_server_key'));
                $users = json_decode($response->getBody(), true)['users'];
                $index = array_search($session->editing_user_id, array_column($users, 'id'));

                if($index !== false) {
                    $editor = User::find($users[$index])[0]->fio;
                }
            }
        }

        if($request->query('intercept') || !$editor) {
            $editor = null;
            $session->editing_user_id = $request->user()->id;
            $session->save();
            $session->load('editor');
            broadcast(new SessionEditorIntercepted($session))->toOthers();
        }

        $overlays = DB::table('overlays')
            ->select('*')
            ->where('session_id', $session->id)
            ->get()
            ->pluck('data')
            ->implode(',');

        return response('{"dataset":' . ($session->dataset ? $session->dataset->data : '[]') .
                            ',"session":' . $session->toJson() .
                            ',"editor":' . json_encode($editor) .
                            ',"overlays":[' . $overlays . ']' .
                            '}', 200)
            ->header('Content-Type', 'application/json');

        /*
        }
        */

        $dataset = $session->dataset ?
            json_decode($session->dataset->data, true) :
            [
                'editedKnowledge' => null,
                'knowledges' => [],
                'items' => [],
                'actions' => [],
                'columns' => [0, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'maxOptions' => 0
            ];

        $data = Source::find(0)->selections;

        if(isset($dataset['sources'])) {
            $sources = /*$standalone ? [] :*/ $data['sources'];
            $knowledges = /*$standalone ? [] :*/ $data['knowledges'] ?? [];

            foreach($dataset['sources'] as $local_source) {
                foreach($sources as &$source) {
                    if($source['id'] == $local_source['id']) {
                        foreach($local_source['selections'] as $local_lang => $local_selections) {
                            foreach($local_selections as $local_selection) {
                                foreach($source['selections'] as $lang => &$selections) {
                                    if($lang == $local_lang) {
                                        $skip = false;

                                        foreach($selections as $selection) {
                                            if($selection['id'] == $local_selection['id']) {
                                                $skip = true;
                                            }
                                        }

                                        if(!$skip) {
                                            $selections[] = $local_selection;
                                        }
                                    }
                                }
                            }
                        }

                        foreach($local_source['knowledges'] ?? [] as $local_knowledge) {
                            $skip = false;

                            foreach($knowledges as $knowledge) {
                                if($knowledge['id'] == $local_knowledge['id']) {
                                    $skip = true;
                                }
                            }

                            if(!$skip) {
                                $local_knowledge['source'] = $source['id'];

                                $local_knowledge['adapted'] = [];

                                foreach($dataset['items'] as $item) {
                                    if(isset($item['source']) &&
                                            $item['source'] == $source['id'] &&
                                            isset($item['knowledge']) &&
                                            $item['knowledge'] == $local_knowledge['id'] &&
                                            !empty($item['text'])) {
                                        $local_knowledge['adapted'] = $item['text'];
                                        break;
                                    }
                                }

                                $knowledges[] = $local_knowledge;
                            }
                        }

                        foreach($dataset['items'] as &$item) {
                            unset($item['text']);
                        }
                    }
                }
            }

            $dataset['sources'] = $sources;
            //$dataset['knowledges'] = $knowledges;
        } else {
            $dataset['sources'] = $data['sources'];

            if($session->dataset && !isset($dataset['knowledges'])) {
                $dataset['knowledges'] = $data['knowledges'];
            }
        }

        return ['dataset' => $dataset];
    }

    public function datasave(Request $request, Session $session) {
        //$standalone = $request->user()->id == 13;

        //$update_data = json_decode($request->input('dataset'), true);

        /*
        //if(!$standalone) {
        $data = Source::find(0)->selections;

        foreach($update_data['knowledges'] as $update) {
            $append = true;

            foreach($data['knowledges'] as $i => $knowledge) {
                if($knowledge['id'] == $update['id']) {
                    array_splice($data['knowledges'], $i, 1, [$update]);
                    $append = false;
                    break;
                }
            }

            if($append) {
                $data['knowledges'][] = $update;
            }
        }

        foreach($update_data['sources'] as $update_source) {
            foreach($data['sources'] as &$source) {
                if($update_source['id'] == $source['id']) {
                    foreach($update_source['selections'] as $update_lang => $update_selections) {
                        foreach($source['selections'] as $lang => &$selections) {
                            if($update_lang == $lang) {
                                foreach($update_selections as $update) {
                                    $append = true;

                                    foreach($selections as $i => $selection) {
                                        if($selection['id'] == $update['id']) {
                                            array_splice($selections, $i, 1, [$update]);
                                            $append = false;
                                            break;
                                        }
                                    }

                                    if($append) {
                                        $selections[] = $update;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        Source::where('id', 0)->update([
            'selections' => json_encode([
                'sources' => $data['sources'],
                'knowledges' => $data['knowledges']
            ])
        ]);

        unset($update_data['sources']);
        //unset($update_data['knowledges']);
        //}
        */

        $dataset = new Dataset;
        $dataset->session_id = $session->id;
        $dataset->data = $request->input('dataset');
        $dataset->save();

        $session->items = $request->input('items');
        $session->knowledges = $request->input('knowledges');
        $session->skills = $request->input('skills');
        $session->actions = $request->input('actions');
        $session->save();
    }

    public function overlay(Request $request, Session $session) {
        $dataset = new Overlay;
        $dataset->session_id = $session->id;
        $dataset->user_id = $request->user()->id;
        $dataset->data = $request->input('overlay');
        $dataset->save();
    }

    public function comments(Request $request, Session $session) {
        $items = collect($request->items);
        $data = json_decode($session->dataset->data, true);

        foreach ($data['items'] as &$item) {
            $update = $items->where('num', $item['num'])->get(0);

            if ($update) {
                $item['comments'] = $update['comments'];
            }
        }

        $dataset = new Dataset;
        $dataset->session_id = $session->id;
        $dataset->data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $dataset->save();
    }

    public function upload(Request $request, Session $session) {
        if($prevId = $request->input('prevId')) {
            File::destroy($prevId);
        }

        $upload = $request->file('file');

        $file = new File([
            'file' => $upload,
            'session_id' => $session->id,
            'name' => $upload->getClientOriginalName()
        ]);

        $file->save();

        return $file;
    }

    public function results($id) {
        return Question::where('level', $id - 2)->withCount([
                'results as answered',
                'results as answered_correctly' => function($query) {
                    $query->where('correct', 1);
                }
            ])
            ->get()
            ->map(function($question) {
                return [
                    'selections' => $question->question_text('ru')->first()->selections,
                    'result' => $question->answered_correctly / $question->answered
                ];
            });
    }

    public function questionnaireResults(Request $request, $id, $duration) {
        $session = Session::find($id);
        $session->test = $session->test ? 2 : 0;
        $session->save();

        $time = Carbon::now()->subSeconds($duration);
        $results = [];

        foreach(Questionnaire::where('level', $id) as $questionnaire) {
            $questionnaire->closed = 1;
            $questionnaire->save();
        }

        foreach(Question::where('level', $id)->get() as $question) {
            $all = $question->results()->whereHas(
                'questionnaire',
                function($query) use ($time) {
                    $query->where('finished_at', '>', $time)
                            ->where('finished_at', '<', Carbon::now());
                }
            )->count();

            $result = $all ? round($question->results()->where('correct', 1)->whereHas(
                'questionnaire',
                function($query) use ($time) {
                    $query->where('finished_at', '>', $time)
                            ->where('finished_at', '<=', Carbon::now());
                }
            )->count() / $all * 100) : 0;

            $results[] = [
                'id' => $question->knowledge,
                'count' => $all,
                'result' => $result
            ];
        }

        return [
            'results' => $results,
            'timepoint' => $time->timestamp
        ];
    }

    public function convert() {
        DB::transaction(function() {
            foreach(User::all() as $user) {
                if(Dataset::where('user_id', $user->id)->count()) {
                    $first = Dataset::where('user_id', $user->id)->oldest()->first();
                    $last = Dataset::where('user_id', $user->id)->latest()->first();

                    $session = new Session;
                    $session->user_id = $user->id;
                    $session->created_at = $first->created_at;
                    $session->updated_at = $last->created_at;
                    $session->save();

                    $session->name = 'Сессия №' . $session->id . ' (' .
                        ($user->email ?: $user->phone) . ')';
                    $session->save();

                    Dataset::where('user_id', $user->id)->update([
                        'user_id' => null,
                        'session_id' => $session->id
                    ]);
                }
            }
        });

        return 'ok';
    }

    public function exportPDF(Request $request, Session $session) {
        set_time_limit(0);
        ini_set('memory_limit', '1G');

        if(in_array($session->id, [175, 203])) {
            \App::setLocale('uz');
        }

        $dataset = json_decode($session->dataset->data);

        $actions = collect($dataset->actions);
        $dataset->actions = [];
        $actionId = null;
        foreach($dataset->items as $item) {
            if(!empty($item->action) && $item->action != $actionId) {
                $actionId = $item->action;
                $dataset->actions[] = $actions->where('id', $actionId)->first();
            }
        }

        if(isset($_GET['knowledges'])) {
            $output = '';

            $output .= "Знание,Формулировка,Выдержка,Источник\n";

            foreach($dataset->actions as $index => $action) {
                if(empty($action->text) || empty($action->text->ru)) {
                    continue;
                }

                // $output .= $action->text->ru . "\n";
                // $output .= "Знание,Источник,Выдержка,Формулировка\n";

                $items = collect($dataset->items)
                    ->filter(function($item) use ($action) {
                        return isset($item->action) &&
                            $item->action == $action->id &&
                            strpos($item->key, 'У') === false &&
                            isset($item->knowledge) &&
                            $item->knowledge;
                    });

                foreach($items as $item) {
                    $knowledge = collect($dataset->knowledges)->where('id', $item->knowledge)->first();

                    $sources = collect($dataset->sources)->whereIn(
                        'id',
                        collect($knowledge->selections->ru)->pluck('source')->unique()
                    );

                    if($sources->count()) {
                        if($sources->count() > 1) {
                            \Log::debug('Multiple sources');
                        }

                        $output .= '"' . str_replace('"', '""', $knowledge->name->ru) . "\",\"" .
                            str_replace('"', '""', $knowledge->adapted->ru) . "\",\"" .
                            str_replace('"', '""', trim(preg_replace('/\s+/', ' ', $knowledge->text->ru))) . "\",\"" .
                            str_replace('"', '""', $sources->first()->name->ru) . "\"\n";
                    }
                }

                $output .= "\n";
            }

            return response($output, 200)->header('Content-Type', 'text/plain');
        }

        $GLOBALS['pdf_first_pass'] = true;
        $GLOBALS['pdf_page_offset'] = 0;
        $GLOBALS['pdf_insert_pages'] = [];

        if(!isset($_GET['test'])) {
            $pdf = App::make('dompdf.wrapper');
            $pdf->getDomPDF()->setBasePath('Материалы/');
            $pdf->loadView('sessions.pdf', [
                'session' => $session,
                'dataset' => $dataset,
                'notes' => request()->has('notes'),
                'margin' => request('margin', 15) / 10,
                'print' => !$request->print
            ]);

            $pdf = $pdf->stream();

            if($request->print) {
                return $pdf;
            }

            $GLOBALS['pdf_first_pass'] = false;

            $pdf = App::make('dompdf.wrapper');
            $pdf->getDomPDF()->setBasePath('Материалы/');

            $pdf->loadView('sessions.pdf', [
                'session' => $session,
                'dataset' => $dataset,
                'notes' => request()->has('notes'),
                'margin' => request('margin', 15) / 10,
                'print' => !$request->print
            ]);

            return $pdf->stream();

            //->download(preg_replace('/\s+/', ' ', preg_replace('/[\/\\\:*?"<>|]/u', ' ', $session->name)) . '.pdf');
        }

        $dir = storage_path('app/materials/' . $session->id . '/');

        if(!file_exists($dir)) {
            mkdir($dir);

            foreach($dataset->actions as &$action ) {
                foreach($action->materials as &$material) {
                    if(!empty($material->ru->file)) {
                        $parts = pathinfo($material->ru->file->name);
                        $filename = $parts['basename'];
                        $name = $parts['filename'];
                        $ext = $parts['extension'];

                        $path = \Storage::path(substr($material->ru->file->url, 9));
                        $parts = pathinfo($path);

                        $i = 1;
                        while(file_exists($dir . $filename)) {
                            $filename = "$name $i.$ext";
                            $i++;
                        }

                        rename($path, $dir . $filename);
                        $material->ru->file->url = "/storage/materials/{$session->id}/$filename";
                    }
                }
            }

            $session->dataset->data = json_encode($dataset, JSON_UNESCAPED_UNICODE);
            $session->dataset->save();
        }

        return view('sessions.pdf', [
            'session' => $session,
            'dataset' => $dataset,
            'notes' => request()->has('notes'),
            'margin' => request('margin', 15) / 10,
            'print' => !$request->print
        ]);
    }
}
