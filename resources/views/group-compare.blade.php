@extends('layouts.frontend')

@section('content')
<div class="container">
    <h2>
        @lang('Результаты')
    </h2>

    <hr>

    <h4>@lang('Вопросы')</h4>

    <div class="row compare text-center">
        @foreach ($results as $result)
            <div class="col-md-3">
                <a href="{{ route('results', ['session' => $session, 'viewedQuestion1' => $result['id'], 'viewedQuestion2' => $result['id2']]) }}#details">
                    <div class="card my-3">
                        <div class="card-body">
                            <h5 class="card-title" data-toggle="tooltip" data-placement="top" title="{{ $result['text'] }}">№{{ $loop->iteration }}</h5>
                            <span class="badge badge-{{ $result['result1'] > 50 ? 'success' : 'danger' }}"><span>{{ $result['result1'] }}%</span>
                            </span><span class="badge badge-{{ $result['result2'] > 50 ? 'success' : 'danger' }}"><span>{{ $result['result2'] }}%</span>
                        </span></div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    @if(count($details))
        <hr class="my-4" id="details">

        <h4>{{ $viewed_question->text }}</h4>

        <div class="row compare text-center">
        @foreach ($details as $user_id => $detail)
            @if(isset($detail['result1']) && isset($detail['result2']))
            <div class="col-md-3">
                <a href="{{ route('results', ['session' => $session, 'viewedQuestion1' => $detail['result1']->question_id, 'viewedQuestion2' => $detail['result2']->question_id, 'user_id' => $user_id]) }}#details2">
                    <div class="card my-3">
                        <div class="card-body">
                            <h5 class="card-title" data-toggle="tooltip" data-placement="top" title="">
                                {{ $detail['result1']->questionnaire->user->firstname }}
                                <br>
                                {{ $detail['result1']->questionnaire->user->lastname }}
                            </h5>
                            <div class="mb-3" style="background-image: url({{ $detail['result1']->questionnaire->user->photo_url }})">
                            </div>
                            <span class="badge badge-{{ $detail['result1']->dontknow ? 'warning' : ($detail['result1']->answered ? ($detail['result1']->correct ? 'success' : 'danger') : 'secondary') }}"
                                @if($detail['result1']['selected'])
                                    data-toggle="tooltip" data-placement="top" title="{{ $detail['result1']['selected'] }}"
                                @endif
                            >
                            </span><span
                                class="badge badge-{{ isset($detail['result2']) ? ($detail['result2']->dontknow ? 'warning' : ($detail['result2']->answered ? ($detail['result2']->correct ? 'success' : 'danger') : 'secondary')) : 'secondary' }}"
                                @if(isset($detail['result2']) && $detail['result2']['selected'])
                                    data-toggle="tooltip" data-placement="top" title="{{ $detail['result2']['selected'] }}"
                                @endif
                            >
                        </span></div>
                    </div>
                </a>
            </div>
            @endif
        @endforeach
        </div>
    @endif

    @if($user && isset($details[$user]) && isset($details[$user]['result1']))
    <hr class="my-4" id="details2">

    <div class="row">
        <div class="question">
            <h3 class="mb-5">{{ $viewed_question->text }} ({{ $details[$user]['result1']->questionnaire->user->firstname }})</h3>

            <div style="margin-left: 20px" class="answers">
                @foreach ($details[$user]['result1']->questionnaire->options($viewed_question->id)->get() as $option)
                    <p class="text-{{ $option->correct ? 'success' : 'danger' }}">
                        @if($option->pivot->selected)
                            <span style="position: absolute; margin-left: -90px; font-size: 0.9rem;">пре</span>
                        @endif
                        @if(isset($details[$user]['result2']) && $details[$user]['result2']->questionnaire->options($viewed_question2->id)->whereHas('option_text', function($query) use ($option) { $query->where('text', $option->text); })->first()->pivot->selected)
                            <span style="position: absolute; margin-left: -50px; font-size: 0.9rem">пост</span>
                        @endif
                        {{ $option->text }}
                    </p>
                @endforeach

                <p class="text-warning">
                    @if($details[$user]['result1']->questionnaire->questions->where('id', $viewed_question->id)->first()->pivot->dontknow)
                        <span style="position: absolute; margin-left: -90px; font-size: 0.9rem">пре</span>
                    @endif
                    @if(isset($details[$user]['result2']) && $details[$user]['result2']->questionnaire->questions->where('id', $viewed_question2->id)->first()->pivot->dontknow)
                        <span style="position: absolute; margin-left: -50px; font-size: 0.9rem">пост</span>
                    @endif
                    Не знаю
                </p>
            </div>

            {{--
            <div class="card mt-5">
                <div class="card-header">
                    @lang('Источник')
                </div>
                <div class="card-body">
                    <iframe src="{{ asset('sources/' . App::getLocale() . '/' . $viewed_question->level . '.html') }}" class="source"></iframe>
                </div>
                <script>
                    var selection_data = @json($selection_data),
                        selections = @json($viewed_question->selections);
                </script>
            </div>
            --}}
        </div>
    </div>
    @endif
</div>
@endsection
