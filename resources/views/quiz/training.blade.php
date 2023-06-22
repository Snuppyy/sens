@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-9 col-md-7 question">
            <h1 class="mb-5">
                @lang('Обучение'){{--: {{
                    mb_strtolower(__(
                        $questionnaire->level == 1 ? 'Базовый уровень' :
                            ($questionnaire->level == 2 ? 'Продвинутый уровень' : 'Специализированный уровень')
                    )) 
                --}}
            </h1>

            <hr>

            <h3 class="mb-5">{{ $questionnaire->question->text }}</h3>

            <div style="margin-left: 20px" class="answers">
                @foreach ($questionnaire->options as $option)
                <p class="text-{{ $option->correct ? 'success' : 'danger' }}"><span style="position: absolute; margin-left: -20px; font-weight: bold">{{ ($option->pivot->selected ? '✓' : '') }}</span>{{ $option->text }}</p>
                @endforeach

                <p class="text-warning"><span style="position: absolute; margin-left: -20px; font-weight: bold">{{ ($questionnaire->question->pivot->dontknow ? '✓' : '') }}</span>Не знаю</p>
            </div>

            <form method="POST" action="{{ route('answer') }}">
                @csrf
                <input type="hidden" name="level" value="{{ $level }}">
                <input type="hidden" name="questionnaire" value="{{ $questionnaire->id }}">
                <input type="hidden" name="question" value="{{ $questionnaire->question->pivot->position }}">

                <div class="form-group mt-5">
                    <button type="submit" class="btn btn-primary btn-lg next-question">
                        @if($questionnaire->question_index == $questionnaire->questions_count - 1)
                            @lang('Завершить обучение')
                        @else
                            @lang('Следующий вопрос')
                        @endif
                    </button>
                </div>
            </form>

            {{-- <div class="alert alert-secondary mt-5">
                <em>{{ $questionnaire->question->fragment }}</em>
            </div> --}}

            <div class="card mt-5">
                <div class="card-header">
                    @lang('Источник')
                </div>
                <div class="card-body">
                    <iframe src="about:blank{{-- asset('sources/' . json_decode($questionnaire->question->fragment)->source App::getLocale() . '/' . $questionnaire->level . '.html'*/) --}}" class="source"></iframe>
                </div>
                <script>
                    var fragment = {!! $questionnaire->question->fragment !!};
                </script>
            </div>
        </div>

        <div class="col-lg-3 col-md-5 order-md-first mt-5 mt-md-0">
            <div class="card">
                <div class="card-body text-center questions-map">
                    <h5 class="mb-2">@lang('Ваш результат: :result%', ['result' => $questionnaire->result])</h5>

                    {{--
                    <small class="text-muted">@lang('Обучение будет автоматически завершено через')</small>

                    <p class="display-4" id="countdown-training" data-seconds="{{ $seconds }}">10:00</p>
                    --}}

                    <h4 class="mb-5">
                    @foreach ($questionnaire->questions as $question)
                    <span class="badge badge-pill badge-{{ $question->pivot->dontknow ? 'warning' : ($question->pivot->answered ? ($question->pivot->correct ? 'success' : 'danger') : 'secondary') }}{{ $loop->index == $questionnaire->question_index ? ' current' : '' }}">{{ $loop->index + 1 }}</span>
                    @endforeach
                    </h4>

                    <a href="{{ route('finish', ['level' => $level]) }}" class="btn btn-secondary">
                        @lang('Завершить обучение')
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
