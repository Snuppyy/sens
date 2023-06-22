@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2>@lang('Ваша история')</h2>
        </div>
    </div>

    <div class="table-responsive mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">@lang('Дата')</th>
                    <th scope="col">@lang('Опросник')</th>
                    <th scope="col">@lang('Результат')</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($questionnaires as $questionnaire)
                <tr>
                    <td>{{ $questionnaire->created_at }}</td>
                    <td>{{ $questionnaire->test ? $questionnaire->test->title :
                            __($questionnaire->level == 1 ? 'базовый' :
                                ($questionnaire->level == 2 ? 'продвинутый' :
                                'специализированный')) }}</td>
                    <td>{{ $questionnaire->result }}%</td>
                    <td>
                        @if($questionnaire->test)
                            <a href="{{ route('map', ['questionnaire' => $questionnaire->id]) }}">
                                @lang('Карта знаний')
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
