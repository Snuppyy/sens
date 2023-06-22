@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2>
                {{-- @lang('Хотите пройти обучение?') --}}
                @lang('Опрос завершен')
            </h2>
            <div class="card mt-4">
                <div class="card-body">
                    <p class="lead">
                        @lang('Ваш результат: :result%', ['result' => $result])
                    </p>

                    <p>
                        @if(!$module || $module->test != 1)
                            <a href="{{ route('finish', ['level' => $level]) }}?restart" class="btn btn-primary my-1 my-sm-0">@lang('Пройти ещё раз')</a>
                            <a href="{{ route('training', ['level' => $level]) }}" class="btn btn-primary my-1 my-sm-0">@lang('Пройти обучение')</a>
                        @endif
                    </p>

                    <p>
                        @if($compare)
                            <a href="{{ route('compare', ['level' => $level]) }}" class="btn btn-success my-1 my-sm-0">@lang('Сравнить с предыдущим')</a>
                        @endif

                        <a href="{{ route('finish', ['level' => $level]) }}" class="btn btn-secondary my-1 my-sm-0">@lang('Вернуться к выбору опросника')</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
