@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h1>
                @if($result < 100)
                    @lang('Хотите пройти обучение?')
                @else
                    @lang('Хотите участвовать в розыгрыше?')
                @endif
            </h1>
            <div class="card mt-4">
                <div class="card-body">
                    <p class="lead">
                        @lang('Ваш результат: :result%', ['result' => $result])
                    </p>
                    @if($result < 100)
                        <p>
                            @lang('Вы сможете повторно пройти опрос через :retry_after или пройти обучение и вернуться к опросу через :retry_after_training.',
                                [
                                    'retry_after' => $retry_after,
                                    'retry_after_training' => $retry_after_training
                                ]
                            )
                        </p>

                        <a href="{{ route('training', ['level' => $level]) }}" class="btn btn-primary my-1 my-sm-0">@lang('Пройти обучение')</a>
                        <a href="{{ route('finish', ['level' => $level]) }}" class="btn btn-secondary my-1 my-sm-0">@lang('Не проходить обучение')</a>
                    @else
                        <p>
                            @lang('Вы можете участвовать в следующем розыгрыше призов (:level). Напоминаем, что:', [
                                'level' => mb_strtolower(__($level == 1 ?
                                    'Базовый уровень' : ($level == 2 ? 'Продвинутый уровень' : 'Специализированный уровень')))
                            ])
                        </p>
                        <ul class="mb-4">
                            <li>@lang('Набрав 100% в опросниках нескольких уровней вы можете участвовать в нескольких розыгрышах в один день.')</li>
                            <li>@lang('Сначала разыгрываются призы специализированного уровня, затем продвинутого и, наконец, базового.')</li>
                            <li>@lang('Выиграв в одном из розыгрышей, вы не участвуете в последующих.')</li>
                            <li>@lang('За всё время акции вы можете получить только один приз.')</li>
                            <li><strong>@lang('Для участия в розыгрыше в вашем профиле должны быть указаны паспортные данные.')</strong></li>
                        </ul>
                        <a href="{{ route('participate', ['level' => $level, 'answer' => 'yes']) }}" class="btn btn-primary my-1 my-sm-0">@lang('Участовать в розыгрыше')</a>
                        <a href="{{ route('participate', ['level' => $level, 'answer' => 'no']) }}" class="btn btn-secondary my-1 my-sm-0">@lang('Не участвовать')</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
