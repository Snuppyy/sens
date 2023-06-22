<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Storage;

use App\Placename;
use App\Questionnaire;

class ProfileController extends Controller
{
    /**
     * Show profile form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $require = null)
    {
        return view('profile', [
            'user' => $request->user(),
            'require' => $require
        ]);
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

    public function store(Request $request, $require = false)
    {
        $user = $request->user();

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
            'firstname' => 'required|max:64',
            'lastname' => 'required|max:64',
            'passport' => ['nullable', 'max:255', $unique],
            'place_of_work' => 'required|max:255',
            'position' => 'required|max:255'
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
            'place_of_work' => __('Место работы'),
            'position' => __('Должность')
        ]));    

        $data = $request->all();

        $user->complete = true;

        if($require) {
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

        if($photo = $request->file('photo')) {
            $user->photo = $photo;    
        }

        $user->fill($data)
            ->save();

        return redirect()->route('coauth', [
            'token' => \Auth::guard('api')->login($user),
            'back' => '//' . config('app.main_domain') . '/home'
        ]);
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
}