@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h1 class="mb-4">@lang('Результаты розыгрыша')</h1>

            <p>
                @lang('Ниже представлен перемешанный сервисом :service список кодов участников розыгрыша от :date (:level)', [
                    'service' => '<a href="https://www.random.org/">random.org</a><small class="text-muted">*</small>',
                    'date' => '<strong>' . $drawing->created_at->format('d.m.Y') . '</strong>',
                    'level' => mb_strtolower(__($drawing->level == 1 ?
                        'Базовый уровень' : ($drawing->level == 2 ? 'Продвинутый уровень' : 'Специализированный уровень')))
                ]):
            </p>

            <div class="alert alert-success lead" role="alert">
                {{ implode(', ', explode(',', $drawing->list)) }}
            </div>

            <p>
                @lang('Напоминаем, что призы достаются двоим участникам, чьи коды оказались первыми в списке.')
            </p>
            <small class="text-muted">
                * @lang('В случаях, когда количество участников менее 3, перемешивание не производится.')
            </small>
        </div>
    </div>
</div>
@endsection
