@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2>@lang('Средние результаты')</h2>
        </div>
    </div>

    <div class="table-responsive mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">@lang('Вопрос')</th>
                    <th scope="col">@lang('Пре')</th>
                    <th scope="col">@lang('Пост')</th>
                    <th scope="col">@lang('Вопрос')</th>
                </tr>
            </thead>
            <tbody>
                @foreach(App\Question::where('level', $level)->get() as $question)
                @php
                    $all1 = $question->results()->whereHas(
                        'questionnaire',
                        function($query) use ($time) {
                            $query->whereTime('finished_at', '<', $time);
                        }
                    )->count();

                    $all2 = $question->results()->whereHas(
                        'questionnaire',
                        function($query) use ($time) {
                            $query->whereTime('finished_at', '>=', $time);
                        }
                    )->count();
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $all1 ? round($question->results()->where('correct', 1)->whereHas(
                            'questionnaire',
                            function($query) use ($time) {
                                $query->whereTime('finished_at', '<', $time);
                            }
                        )->count() / $all1 * 100) . '%' : '' }}
                        ({{ $all1 }})
                    </td>
                    <td>
                        {{ $all2 ? round($question->results()->where('correct', 1)->whereHas(
                            'questionnaire',
                            function($query) use ($time) {
                                $query->whereTime('finished_at', '>=', $time);
                            }
                        )->count() / $all2 * 100) . '%' : '' }}
                        ({{ $all2 }})
                    </td>
                    <td style="white-space: nowrap">
                        {{ $question->text }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
