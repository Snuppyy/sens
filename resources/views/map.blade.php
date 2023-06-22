@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-12 question">
            <h2 class="mb-4">
                @lang('Карта знаний'): {{ $questionnaire->test->title }}
            </h2>

            <p class="lead">@lang('Ваш результат: :result%', ['result' => $questionnaire->result])</p>

            <script>
                var map = [];
            </script>

            @foreach($sources as $source => $questions)
                <iframe src="{{ asset('sources/' . $source) }}" class="source"></iframe>
                <script>
                    map.push(@json($questions));
                </script>
            @endforeach
        </div>
    </div>
</div>
@endsection
