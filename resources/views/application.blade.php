@extends('layouts.frontend')

@section('content')
<div class="container application">
    <form method="POST" action="{{ route('training.apply') }}" novalidate>
        @csrf
        <input type="hidden" name="training" value="{{ $trainings->pluck('id')->implode(',') }}">

        <h2 class="mb-4">@lang('Анкета кандидата на участие в ":training"', ['training' => $trainings->count() > 1 ? __('тренингах') : $trainings->get(0)->title])</h2>

        <?php /*
        <h4 class="mb-2"><strong>1.</strong> @lang('Сможете ли Вы обеспечить своё непрерывное участие в тренинге в следующие дни/время?')</h4>

        @foreach($trainings as $training)
            @foreach([
                __('с 09:00 по 18:00 (возможно продление)'),
                __('с 09:00 по 18:00 (возможно продление)'),
                __('с 09:00 по 18:00 (возможно продление)'),
                __('с 09:00 по 18:00 (возможно продление)'),
                __('с 09:00 по 18:00 (возможно продление)'),
                __('с 09:00 по 18:00')
            ] as $item)
                <h5 class="mt-4 mb-2"><strong>1.{{ $loop->parent->index * 6 + $loop->iteration }}.</strong> {{ sprintf('%02d', $loop->index + $training->start_day) }}/{{ sprintf('%02d', $training->start_month) }}/2023 {{ $item }}</h5>
                <div class="form-group">
                    <div class="custom-control form-control-lg custom-radio custom-control-inline">
                        <input class="custom-control-input" type="radio" name="dates[{{ $training->id }}][{{ $loop->iteration }}]" value="1" id="dates{{ $training->id }}_{{ $loop->iteration }}_1" required{{ isset(old('dates', $application['dates'])[$training->id][$loop->iteration]) && old('dates', $application['dates'])[$training->id][$loop->iteration] == 1 ? ' checked' : '' }}>
                        <label class="custom-control-label" for="dates{{ $training->id }}_{{ $loop->iteration }}_1">@lang('Да')</label>
                    </div>
                    <div class="custom-control form-control-lg custom-radio custom-control-inline">
                        <input class="custom-control-input" type="radio" name="dates[{{ $training->id }}][{{ $loop->iteration }}]" value="2" id="dates{{ $training->id }}_{{ $loop->iteration }}_2" required{{ isset(old('dates', $application['dates'])[$training->id][$loop->iteration]) && old('dates', $application['dates'])[$training->id][$loop->iteration] == 2 ? ' checked' : '' }}>
                        <label class="custom-control-label" for="dates{{ $training->id }}_{{ $loop->iteration }}_2">@lang('Не знаю')</label>
                    </div>
                    <div class="custom-control form-control-lg custom-radio custom-control-inline">
                        <input class="custom-control-input" type="radio" name="dates[{{ $training->id }}][{{ $loop->iteration }}]" value="3" id="dates{{ $training->id }}_{{ $loop->iteration }}_3" required{{ isset(old('dates', $application['dates'])[$training->id][$loop->iteration]) && old('dates', $application['dates'])[$training->id][$loop->iteration] == 3 ? ' checked' : '' }}>
                        <label class="custom-control-label" for="dates{{ $training->id }}_{{ $loop->iteration }}_3">@lang('Нет')</label>
                    </div>
                </div>
            @endforeach
        @endforeach

        <h4 class="mb-2 mt-4"><strong>2.</strong> @lang('В какой мере Вы владеете русским языком?')</h4>

        @foreach([
            __('Чтение'),
            __('Слушание'),
            __('Устная речь'),
            __('Письменная речь')
        ] as $item)
            <h5 class="mt-4 mb-2"><strong>2.{{ $loop->iteration }}.</strong> @lang($item)</h5>
            <div class="form-group">
                <div class="custom-control form-control-lg custom-radio custom-control-inline">
                    <input class="custom-control-input" type="radio" name="lang[{{ $loop->iteration }}]" value="1" id="lang{{ $loop->iteration }}_1" required{{ isset(old('lang', $application['lang'])[$loop->iteration]) && old('lang', $application['lang'])[$loop->iteration] == 1 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="lang{{ $loop->iteration }}_1">@lang('Хорошо')</label>
                </div>
                <div class="custom-control form-control-lg custom-radio custom-control-inline">
                    <input class="custom-control-input" type="radio" name="lang[{{ $loop->iteration }}]" value="2" id="lang{{ $loop->iteration }}_2" required{{ isset(old('lang', $application['lang'])[$loop->iteration]) && old('lang', $application['lang'])[$loop->iteration] == 2 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="lang{{ $loop->iteration }}_2">@lang('Средне')</label>
                </div>
                <div class="custom-control form-control-lg custom-radio custom-control-inline">
                    <input class="custom-control-input" type="radio" name="lang[{{ $loop->iteration }}]" value="3" id="lang{{ $loop->iteration }}_3" required{{ isset(old('lang', $application['lang'])[$loop->iteration]) && old('lang', $application['lang'])[$loop->iteration] == 3 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="lang{{ $loop->iteration }}_3">@lang('Плохо')</label>
                </div>
            </div>
        @endforeach

        @foreach([
            [__('Имеете ли Вы опыт разработки методической или нормативной документации?'), __('значимых случая, подтверждающих Ваш опыт')],
            [__('Имеете ли Вы опыт тренерской / педагогической деятельности?'), __('значимых случая, подтверждающих Ваш опыт')],
            [__('Планируете ли Вы в ближайшие 24 месяца принимать участие в разработке методической или нормативной документации в сфере иммунизации?'),
                __('значимых события, которые уже запланированы или предполагаются')],
            [__('Планируете ли Вы в ближайшие 24 месяца принимать участие в тренерской / педагогической деятельности в сфере иммунизации?'),
                __('события, которые уже запланированы или предполагаются')],
            [__('Есть ли у Вас причины, побуждающие принять участие в тренинге?'), __('значимых причин')],
            [__('Есть ли у Вас ограничения любого рода (семейные, служебные, религиозные, состояние здоровья и пр.), которые могут помешать принять участие в тренинге?'),
                __('значимых ограничений')],
            [__('Желаете ли Вы сообщить дополнительную важную информацию о себе, которую следует знать организаторам тренинга?'), __('значимых сообщений')]
        ] as $item)
            <h4 class="mb-2 mt-4"><strong>{{ $loop->iteration + 2 }}.</strong> @lang($item[0])</h4>
            <p>@lang('Отметьте ответ «НЕТ» или укажите несколько наиболее :reason.', ['reason' => $item[1]])</p>

            <div class="form-group">
                <div class="custom-control form-control-lg custom-checkbox custom-control-inline{{ $errors->has('question_' . $loop->iteration) ? ' is-invalid' : '' }}">
                    <input class="custom-control-input" type="checkbox" name="question_{{ $loop->iteration }}[no]" value="1" id="question_no_{{ $loop->iteration }}"{{ isset(old('question_' . $loop->iteration, $application['question_' . $loop->iteration])['no']) && old('question_' . $loop->iteration, $application['question_' . $loop->iteration])['no'] ? ' checked' : '' }}>
                    <label class="custom-control-label" for="question_no_{{ $loop->iteration }}">@lang('Нет')</label>
                </div>
            </div>

            @foreach(old('question_' . $loop->iteration, $application['question_' . $loop->iteration]) as $value)
                <div class="form-group">
                    <input type="text" class="form-control{{ $errors->has('question_' . $loop->parent->iteration) ? ' is-invalid' : '' }}" name="question_{{ $loop->parent->iteration }}[]" value="{{ $value }}" placeholder=""{{ $loop->index ? '' : ' required' }}>
                </div>
            @endforeach
        @endforeach
        */ ?>

        @foreach($form as $index => $field)
            @if(isset($field['question']))
                <h4 class="mb-2 mt-4"><strong>{{ $index + 1 }}.</strong> {{ $field['question'] }} </h4>

                @if(isset($field['hint']))
                    <p>{{ $field['hint'] }}</p>
                @endif

                @if($field['type'] == 0)
                    <div class="form-group">
                        @foreach($field['options'] as $index => $option)
                            <div class="custom-control form-control-lg custom-radio custom-control-inline">
                                <input class="custom-control-input" type="radio" name="question_{{ $loop->parent->iteration }}[answer]" value="{{ $loop->iteration }}" id="lang{{ $loop->parent->iteration }}_{{ $loop->iteration }}" required{{ isset(old('question_' . $loop->parent->iteration, $application['question_' . $loop->parent->iteration])['answer']) && old('question_' . $loop->parent->iteration, $application['question_' . $loop->parent->iteration])['answer'] == $loop->iteration ? ' checked' : '' }}>
                                <label class="custom-control-label" for="lang{{ $loop->parent->iteration }}_{{ $loop->iteration }}">{{ $option['text'] }}</label>
                            </div>
                        @endforeach
                        <input type="hidden" name="question_{{ $loop->iteration }}[no]" value="0">
                    </div>
                @else
                    <div class="form-group">
                        <div class="custom-control form-control-lg custom-checkbox custom-control-inline{{ $errors->has('question_' . $loop->iteration) ? ' is-invalid' : '' }}">
                            <input class="custom-control-input" type="checkbox" name="question_{{ $loop->iteration }}[no]" value="1" id="question_no_{{ $loop->iteration }}"{{ isset(old('question_' . $loop->iteration, $application['question_' . $loop->iteration])['no']) && old('question_' . $loop->iteration, $application['question_' . $loop->iteration])['no'] ? ' checked' : '' }}>
                            <label class="custom-control-label" for="question_no_{{ $loop->iteration }}">@lang('Нет')</label>
                        </div>
                    </div>

                    @foreach(old('question_' . $loop->iteration, $application['question_' . $loop->iteration]) as $value)
                        <div class="form-group">
                            <input type="text" class="form-control{{ $errors->has('question_' . $loop->parent->iteration) ? ' is-invalid' : '' }}" name="question_{{ $loop->parent->iteration }}[]" value="{{ $value }}" placeholder=""{{ $loop->index ? '' : ' required' }}>
                        </div>
                    @endforeach
                @endif
            @endif
        @endforeach

        <h4 class="mb-2 mt-4">@lang('Подтверждение')</h4>

        <div class="form-group mt-2">
            <div class="custom-control form-control-lg custom-checkbox custom-control-inline{{ $errors->has('confirm') ? ' is-invalid' : '' }}">
                <input class="custom-control-input" type="checkbox" name="confirm" value="1" id="confirm" required>
                <label class="custom-control-label" for="confirm">@lang('Подтверждаю достоверность предоставленной в анкете информации, разрешаю обработку, хранение и распространение в служебных целях данной информации.')</label>
            </div>
        </div>

        <button type="submit" class="btn btn-secondary mt-3">
            @lang('Сохранить черновик')
        </button>

        <button type="submit" name="submit" class="btn btn-primary mt-3">
            @lang('Подать заявку')
        </button>
    </form>
</div>
@endsection
