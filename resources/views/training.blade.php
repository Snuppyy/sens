@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-12 question">
            <h2 class="mb-4">
                @lang('Обучение'): {{
                    $questionnaire->test ? $questionnaire->test->title :
                        mb_strtolower(__(
                            $questionnaire->level == 1 ? 'Базовый уровень' :
                                ($questionnaire->level == 2 ? 'Продвинутый уровень' :
                                    'Специализированный уровень')
                        ))
                }}
            </h2>

            <p class="lead">@lang('Ваш результат: :result%', ['result' => $questionnaire->result])</p>

            <div class="card mb-5">
                <div class="card-body text-center questions-map">
                    <h4 class="mb-0">
                        @foreach ($questionnaire->questions as $question)
                            <span class="badge badge-pill badge-{{ $question->pivot->dontknow ? 'warning' : ($question->pivot->answered ? ($question->pivot->correct ? 'success' : 'danger') : 'secondary') }}{{ $loop->index == $questionnaire->question_index ? ' current' : '' }}">{{ $loop->index + 1 }}</span>
                        @endforeach
                    </h4>
                </div>
            </div>

            <h3 class="mb-5">{{ $questionnaire->question->text }}</h3>

            <div class="answers">
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
                    @foreach($questionnaire->questions as $index => $question)
                        @if($index > $questionnaire->question->pivot->position && (!$question->pivot->answered || !$question->pivot->correct))
                            <input type="submit" name="problem" class="btn btn-danger btn-lg next-question mr-2" value="@lang('Следующая проблема')">

                            @break
                        @endif
                    @endforeach

                    @if($questionnaire->question_index < $questionnaire->questions_count - 1)
                        <button type="submit" class="btn btn-primary btn-lg next-question mr-2">
                            @lang('Следующий вопрос')
                        </button>
                    @endif

                    <a href="{{ route('finish', ['level' => $level]) }}" class="btn btn-secondary">
                        @lang('Завершить обучение')
                    </a>
                </div>
            </form>

            @if($questionnaire->question->fragment)
                <div class="alert alert-secondary mt-5">
                    <em>{{ $questionnaire->question->fragment }}</em>
                </div>
            @endif

            @if($questionnaire->question->source)
                <div class="card mt-5">
                    <div class="card-header">
                        @lang('Источник')
                    </div>
                    <div class="card-body">
                        <iframe src="{{ asset('sources/' . $questionnaire->question->source) }}" class="source"></iframe>
                    </div>
                    <script>
                        var selections = @json($questionnaire->question->selections),
                            correct = @json(!!$questionnaire->question->pivot->correct),
                            incorrect = @json(!$questionnaire->question->pivot->correct && $questionnaire->question->pivot->answered)
                            {{-- selection_data = @json($selection_data), --}}
                    </script>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
