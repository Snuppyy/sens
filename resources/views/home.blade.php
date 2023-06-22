@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2>@lang('Опросники')</h2>
        </div>
    </div>

    <div class="row mt-4">
        @if(!$demo)
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
                        'level' => 6,
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
                        'level' => 7,
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
                        'level' => 8,
                        'questionnaire' => $user->questionnaire3,
                        'topQuestionnaire' => $user->topQuestionnaire3,
                        'questionnaires' => $user->completeQuestionnaires3,
                        'seconds' => $seconds_3
                    ])
                        @lang('Чтобы получить доступ к специализированному уровню, вы должны хотя бы раз набрать 100% в опросе продвинутого уровня.')
                    @endcomponent
                </div>
            </div>
        @endif

        @foreach($tests as $test)
            @php
            $questionnaire = $user->questionnaire($test->id)->first();
            $topQuestionnaire = $user->topQuestionnaire($test->id)->first()
            @endphp

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ $test->title }}
                        </h4>

                        <small class="text-muted">{{ $test->training ? $test->training->title_ru : $test->description ?? __('Опросник для самообучения') }}</small>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            @if ($topQuestionnaire)
                                <strong>@lang('Ваш лучший результат: :result%', ['result' => $topQuestionnaire->result])</strong>
                            @else
                                @lang('Вы ещё не проходили этот опросник')
                            @endif
                        </li>
                        @if ($questionnaire && !$questionnaire->closed)
                            <li class="list-group-item">
                                {{ $questionnaire->question_index + 1 }}
                                @lang('из')
                                {{ $questionnaire->questions_count }}
                            </li>
                        @endif
                    </ul>

                    <div class="card-body">
                        {{-- @if(!$topQuestionnaire || !$test->training)
                            <a href="/questionnaire/{{ $test->id }}" class="btn btn-primary">@lang('Пройти опрос')</a>
                        @endif --}}

                        @if (!$questionnaire || $questionnaire->closed)
                            <a href="/questionnaire/{{ $test->id }}" class="btn btn-primary">@lang('Пройти опрос')</a>
                        @else
                            <a href="/questionnaire/{{ $test->id }}" class="btn btn-primary">@lang('Продолжить прохождение')</a>
                        @endif

                        @if ($questionnaire && $questionnaire->closed && !$questionnaire->training_finished)
                            <a href="/questionnaire/{{ $test->id }}" class="btn btn-primary">@lang('Пройти обучение')</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
