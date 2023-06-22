@extends('layouts.frontend')

@section('content')
<div class="container">
    <h2>
        @lang('Сравнение результатов'): {{
            mb_strtolower(__(
                $level == 1 ? 'Базовый уровень' :
                            ($level == 2 ? 'Продвинутый уровень' :
                                ($level == 3 ? 'Специализированный уровень' : 'новый'))
            )) 
        }}
    </h2>

    <h5>@lang('Общий результат: :result1% и :result2%', [
        'result1' => $questionnaires[0]->result,
        'result2' => $questionnaires[1]->result
    ])</h5>

    <hr>

    <h4>@lang('Вопросы')</h4>

    <div class="row compare text-center">
        @foreach ($questionnaires[0]->questions as $question)
            <div class="col-md-2">
                <a href="{{ route('compare', ['level' => $level, 'question' => $question->pivot->position + 1]) }}#details">
                    <div class="card my-3">
                        <div class="card-body">
                            <h5 class="card-title" data-toggle="tooltip" data-placement="top" title="{{ $question->text }}">№{{ $loop->iteration }}</h5>
                            <span class="badge badge-{{ $question->pivot->dontknow ? 'warning' : ($question->pivot->answered ? ($question->pivot->correct ? 'success' : 'danger') : 'secondary') }}">
                            </span><span class="badge badge-{{ $questionnaires[1]->questions->where('id', $question->id)->first()->pivot->dontknow ? 'warning' : ($questionnaires[1]->questions->where('id', $question->id)->first()->pivot->answered ? ($questionnaires[1]->questions->where('id', $question->id)->first()->pivot->correct ? 'success' : 'danger') : 'secondary') }}">
                        </span></div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{--
    <a href="{{ route('home') }}" class="btn btn-secondary">
        @lang('Завершить')
    </a>
    --}}

    @if($viewed_question)
    <hr class="my-4" id="details">
        <div class="question">
            <h3 class="mb-5">{{ $viewed_question->text }}</h3>

            <div class="answers">
                @foreach ($questionnaires[0]->options($viewed_question->id)->get() as $option)
                    <p class="text-{{ $option->correct ? 'success' : 'danger' }}">
                        @if($option->pivot->selected)
                            <span style="position: absolute; margin-left: -90px; font-size: 0.9rem;">пре</span>
                        @endif
                        @if($questionnaires[1]->options($viewed_question->id)->where('id', $option->id)->first()->pivot->selected)
                            <span style="position: absolute; margin-left: -50px; font-size: 0.9rem">пост</span>
                        @endif
                        {{ $option->text }}
                    </p>
                @endforeach

                <p class="text-warning">
                    @if($viewed_question->pivot->dontknow)
                        <span style="position: absolute; margin-left: -90px; font-size: 0.9rem">пре</span>
                    @endif
                    @if($questionnaires[1]->questions->where('id', $viewed_question->id)->first()->pivot->dontknow)
                        <span style="position: absolute; margin-left: -50px; font-size: 0.9rem">пост</span>
                    @endif
                    Не знаю
                </p>
            </div>

            @if($viewed_question->fragment)
                <div class="alert alert-secondary mt-5">
                    <em>{{ $questionnaire->question->fragment }}</em>
                </div>
            @endif

            @if($viewed_question->source)
                <div class="card mt-5">
                    <div class="card-header">
                        @lang('Источник')
                    </div>
                    <div class="card-body">
                        <iframe src="{{ asset('sources/' . $viewed_question->source) }}" class="source"></iframe>
                    </div>
                    <script>
                        var selections = @json($viewed_question->selections)
                    </script>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
