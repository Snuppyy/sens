@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-9 col-md-7 question">
            <h1 class="mb-5">
                @lang('Опросник'){{--: {{
                    mb_strtolower(__(
                        $questionnaire->level == 1 ? 'Базовый уровень' :
                            ($questionnaire->level == 2 ? 'Продвинутый уровень' : 'Специализированный уровень')
                    )) 
                --}}
            </h1>

            <hr>

            <h3>{{ $questionnaire->question->text }}</h3>
            <p class="lead">
                @if ($questionnaire->question->multiple)
                    @lang('Отметьте несколько правильных ответов')
                @else
                    @lang('Отметьте один правильный ответ')
                @endif
            </p>

            <form method="POST" action="{{ route('answer') }}" class="mt-5{{ !$questionnaire->question->multiple ? ' single-answer' : '' }}">
                @csrf
                <input type="hidden" name="level" value="{{ $level }}">
                <input type="hidden" name="questionnaire" value="{{ $questionnaire->id }}">
                <input type="hidden" name="question" value="{{ $questionnaire->question->pivot->position }}">
                @if ($ask)
                <input type="hidden" name="dont-ask">
                @endif

                @foreach ($questionnaire->options as $option)
                <div class="custom-control form-control-lg custom-checkbox">
                    <input class="custom-control-input" type="checkbox" name="answers[]" value="{{ $option->pivot->position }}" id="option{{ $option->pivot->position }}">
                    <label class="custom-control-label" for="option{{ $option->pivot->position }}">
                        {{ $option->text }}
                    </label>
                </div>
                @endforeach

                <div class="custom-control form-control-lg custom-checkbox">
                    <input class="custom-control-input" type="checkbox" name="answers[]" value="-1" id="option-1">
                    <label class="custom-control-label" for="option-1">
                        @lang('Не знаю')
                    </label>
                </div>

                
                {{-- @if ($questionnaire->question->marked)
                <div class="alert alert-danger mt-5">
                    Вопрос требует внимания
                </div>
                @endif
                --}}

                <div class="form-group mt-5">
                    <button type="submit" class="btn btn-primary btn-lg"{{ $ask ? ' data-ask="1"' : ''}}>
                        @if($questionnaire->question_index == $questionnaire->questions_count - 1)
                            @lang('Завершить опрос')
                        @else
                            @lang('Следующий вопрос')
                        @endif
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-3 col-md-5 order-md-first mt-5 mt-md-0">
            <div class="card mb-5">
                <div class="card-body text-center questions-map">
                    <div class="questionnaire-countdown rounded px-4 py-3 p-md-0">
                        <h5>@lang('Осталось')</h5>
                        <p class="display-4 mb-0 mb-md-3" id="countdown-seconds">{{ $seconds }}</p>
                    </div>

                    <h4 class="mb-5 mt-4">
                    @foreach ($questionnaire->questions as $question)
                    <span class="badge badge-pill badge-{{ $question->pivot->dontknow ? 'warning' : ($question->pivot->answered ? 'light' : 'secondary') }}{{ $loop->index == $questionnaire->question_index ? ' current' : '' }}">{{ $loop->index + 1 }}</span>
                    @endforeach
                    </h4>

                    <a href="#questionnaire-finish-confirm" data-toggle="modal"  class="btn btn-secondary">
                        @lang('Завершить опрос')
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="questionnaire-finish-confirm" tabindex="-1" role="dialog" aria-labelledby="questionnaire-finish-confirm-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionnaire-finish-confirm-label">{{ __('Завершить опрос?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Закрыть') }}">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="lead">
                    @lang('Вы не ответили на :questions вопросов.<br>Завершить опрос?', ['questions' => count($questionnaire->questions) - $questionnaire->question_index ])
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary my-1 my-sm-0" data-dismiss="modal">{{ __('Закрыть') }}</button>
                <a href="{{ route('finish', ['level' => $level]) }}" class="btn btn-primary my-1 my-sm-0 confirm">{{ __('Завершить') }}</a>
            </div>
        </div>
    </div>
</div>

@if ($ask)
<div class="modal fade" id="skip-confirm" tabindex="-1" role="dialog" aria-labelledby="skip-confirm-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="skip-confirm-label">{{ __('Пропустить вопрос?') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Закрыть') }}">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="lead">
                    @lang('Пропустив вопрос, вы не сможете набрать 100% и принять участие в розыгрыше. Вернуться к нему в рамках этого опроса будет уже невозможно.')
                </p>
                <p class="lead">
                    <strong>@lang('Уверены, что хотите перейти к следующему вопросу?')</strong>
                </p>
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input" type="checkbox" value="1" id="dont-ask">
                    <label class="custom-control-label" for="dont-ask">
                        {{ __('Больше не спрашивать') }}
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary my-1 my-sm-0" data-dismiss="modal">{{ __('Закрыть') }}</button>
                <button type="button" class="btn btn-primary my-1 my-sm-0 confirm">
                    @if($questionnaire->question_index == $questionnaire->questions_count - 1)
                        @lang('Завершить опрос')
                    @else
                        @lang('Следующий вопрос')
                    @endif
                </a>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
