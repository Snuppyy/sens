<?php

namespace App\Http\Controllers;

use App\Session;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Training;
use App\TrainingApplication;

class TrainingController extends Controller
{
    /**
     * Show application form.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  App\Training $training
     * @return \Illuminate\Http\Response
     */
    public function application(Request $request, Training $training = null)
    {
        $user = $request->user();
        $application = null;

        if(($training && (($application = $user->trainingApplication($training->id)) &&
                    !in_array($application->status, ['draft', 'applied']))) ||
                !($trainings = Training::where('status', '<>', 'draft')
                    ->whereDoesntHave('application', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })->get())) {
            abort(404);
        }

        $answers = [''];

        return view('application', [
            'user' => $user,
            'trainings' => $training ? collect([$training]) : $trainings,
            'form' => $training->info['form'],
            'application' => collect()
                ->pad(count($training->info['form']), 0)
                ->mapWithKeys(function($item, $index) use ($answers) {
                    return ['question_' . ($index + 1) => $answers];
                })->merge([
                    'dates' => [],
                    'lang' => []
                ])->merge($application ? $application->application : [])
        ]);
    }

    /**
     * Show application form.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function apply(Request $request)
    {
        $user = $request->user();

        foreach(explode(',', $request->input('training')) as $training_id) {
            $training = Training::findOrFail($training_id);

            if(($application = $user->trainingApplication($training->id)) &&
                    !in_array($application->status, ['draft', 'applied'])) {
                continue;
            }

            if(!$application) {
                $application = new TrainingApplication;
                $application->user_id = $user->id;
                $application->training_id = $training->id;
            }

            $application->application = $request->except('_token');

            if($request->has('submit')) {
                $application->status = 'applied';
            }

            $application->save();
        }

        return redirect('trainings');
    }

    /**
     * Show first time visitor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showFirstTime(Request $request)
    {
        return $this->index($request);
    }

    public function store(Request $request, $require = null)
    {
        $user = $request->user();

        if(in_array('participant', $user->role)) {
            $request->validate([
                'firstname_lat' => 'required|max:64',
                'lastname_lat' => 'required|max:64',
                'place_of_work' => 'required|max:255',
                'position' => 'required|max:255'
            ], [

            ], array_map(function($str) {
                return '«' . mb_strtolower($str) . '»';
            }, [
                'firstname_lat' => __('Имя латиницей (как в паспорте)'),
                'lastname_lat' => __('Фамилия латиницей (как в паспорте)'),
                'place_of_work' => __('Место работы'),
                'position' => __('Должность')
            ]));
        } else {
            $unique = Rule::unique('users')->ignore($user->id);

            $request->validate($require ? [
                'firstname_lat' => 'required|max:64',
                'lastname_lat' => 'required|max:64',
                'firstname' => 'required|max:64',
                'lastname' => 'required|max:64',
                'passport' => ['required', 'max:255', $unique],
                'expire' => 'required|date',
                'issued' => 'required|max:128',
                'country' => 'required|integer',
                'province_id' => 'required|exists:placenames,id',
                'city_id' => 'required|exists:placenames,id',
            ] : [
                'passport' => ['nullable', 'max:255', $unique]
            ], [
                'passport.unique' => __('Паспорт уже указан в профиле другого пользователя.')
            ], array_map(function($str) {
                return '«' . mb_strtolower($str) . '»';
            }, [
                'firstname_lat' => __('Имя латиницей (как в паспорте)'),
                'lastname_lat' => __('Фамилия латиницей (как в паспорте)'),
                'firstname' => __('Имя кириллицей'),
                'lastname' => __('Фамилия кириллицей'),
                'passport' => __('Серия и номер паспорта'),
                'expire' => __('Паспорт действителен до'),
                'issued' => __('Паспорт выдан'),
                'country' => __('Страна'),
                'province_id' => __('Область'),
                'city_id' => __('Населённый пункт'),
            ]));
        }

        $data = $request->all();

        if($require) {
            $user->complete = true;

            foreach(Questionnaire::where([['participate', true], ['drawing', false]])->get() as $questionnaire) {
                $questionnaire->drawing = true;
                $questionnaire->timestamps = false;
                $questionnaire->save();
            }
        }

        $after_registration = !$user->level;

        if(!empty($data['firstname_lat'])) {
            $data['firstname_lat'] = mb_strtoupper($data['firstname_lat']);
        }

        if(!empty($data['lastname_lat'])) {
            $data['lastname_lat'] = mb_strtoupper($data['lastname_lat']);
        }

        if(!empty($data['firstname'])) {
            $data['firstname'] = mb_strtoupper($data['firstname']);
        }

        if(!empty($data['lastname'])) {
            $data['lastname'] = mb_strtoupper($data['lastname']);
        }

        if($after_registration) {
            $user->level = 1;
        }

        $user->fill($data)
            ->save();

        return redirect('home');
    }

    public function placenames($type, $parent_id) {
        if($type == 'province') {
            if($parent_id == 168) {
                $parent_id = null;
            } else {
                return [];
            }
        }

        return ['placenames' => Placename::where('parent_id', $parent_id)->get()];
    }

    public function addPlacename(Request $request) {
        $name = trim($request->name);

        if(!empty($name)) {
            $placename = new Placename;
            $placename->ru = $name;
            $placename->parent_id = $request->parent_id;
            $placename->save();

            return ['placename' => $placename];
        }

        return [];
    }

    public function bom(Request $request, Session $session) {
        $data = json_decode($session->dataset->data, true);

        $materials = collect($data['actions'])->pluck('materials')->flatten()->filter(); //->flatten(); //->unique();

        return view('sessions.bom', ['materials' => $materials]);
    }
}