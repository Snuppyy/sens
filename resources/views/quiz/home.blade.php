@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h1>@lang('Главная')</h1>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title">
                        {{ __('Базовый уровень') }}
                    </h4>

                    <small class="text-muted">{{ __('Базовый уровень знаний по вопросам ТБ предусматривает самую необходимую информацию о туберкулёзе, которой желательно владеть каждому человеку в интересах собственного здоровья и здоровья своих близких.') }}</small>
                </div>

                @component('components.level', [
                    'user' => $user,
                    'level' => 1,
                    'questionnaire' => $user->questionnaire1,
                    'topQuestionnaire' => $user->topQuestionnaire1,
                    'questionnaires' => $user->completeQuestionnaires1,
                    'seconds' => $seconds_1
                ])
                @endcomponent
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title">
                        {{ __('Продвинутый уровень') }}
                    </h4>

                    <small class="text-muted">{{ __('Продвинутый уровень знаний по вопросам ТБ содержит в себе расширенную информацию и является желательным для тех, кто столкнулся с туберкулёзом лично или имеет близких людей, пострадавших от туберкулёза.') }}</small>
                </div>

                @component('components.level', [
                    'user' => $user,
                    'level' => 2,
                    'questionnaire' => $user->questionnaire2,
                    'topQuestionnaire' => $user->topQuestionnaire2,
                    'questionnaires' => $user->completeQuestionnaires2,
                    'seconds' => $seconds_2
                ])
                    @lang('Чтобы получить доступ к продвинутому уровню, вы должны хотя бы раз набрать 100% в опросе базового уровня.')
                @endcomponent
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title">
                        {{ __('Специализированный уровень') }}
                    </h4>

                    <small class="text-muted">{{ __('Специализированный уровень знаний по вопросам ТБ – включает в себя углублённую информацию о туберкулёзе, которой должны владеть люди, оказывающие медицинскую, социальную и психологическую помощь людям, болеющим туберкулёзом.') }}</small>
                </div>

                @component('components.level', [
                    'user' => $user,
                    'level' => 3,
                    'questionnaire' => $user->questionnaire3,
                    'topQuestionnaire' => $user->topQuestionnaire3,
                    'questionnaires' => $user->completeQuestionnaires3,
                    'seconds' => $seconds_3
                ])
                    @lang('Чтобы получить доступ к специализированному уровню, вы должны хотя бы раз набрать 100% в опросе продвинутого уровня.')
                @endcomponent
            </div>
        </div>
    </div>
</div>
@endsection
