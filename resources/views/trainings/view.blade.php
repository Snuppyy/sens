@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>{{ $training->title }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 order-md-8 col-xs-12">
            <div class="card mb-4">
                <ul class="list-group list-group-flush">
                    @foreach([
                        'location' => 'fa-map-marker-alt',
                        'dates' => 'fa-calendar-alt',
                        'languages' => 'fa-language',
                        'paid' => 'fa-credit-card',
                        'activity' => 'fa-fire-alt',
                        'time' => 'fa-clock',
                        'nutrition' => 'fa-pizza-slice',
                        'dress' => 'fa-tshirt',
                        'deadline' => 'fa-stopwatch'
                    ] as $key => $icon)
                        @if(!empty($training->localized_info[$key]))
                            <li class="list-group-item d-flex">
                                <i class="fas {{ $icon }} fa-lg fa-fw align-self-center mr-3"></i>
                                <div class="media-body">
                                    {{ $training->localized_info[$key] }}
                                    @if(!empty($training->localized_info[$key . 'Secondary']))
                                        <br><small>{{ $training->localized_info[$key . 'Secondary'] }}</small>
                                    @endif
                                </div>
                            </li>
                        @endif
                    @endforeach

                    {{--
                    @if(App::getLocale() == 'ru')
                    @else
                    <li class="list-group-item d-flex"><i class="fas fa-map-marker-alt fa-lg fa-fw align-self-center mr-3"></i><div class="media-body">Toshkent</div></li>
                    <li class="list-group-item d-flex"><i class="fas fa-calendar-alt fa-lg fa-fw align-self-center mr-3"></i><div class="media-body">{{ $training->id == 5 ? '2-7 dekabr' : '4-9 noyabr' }}<br><small>6 kun uzluksiz</small></div></li>
                    <li class="list-group-item d-flex"><i class="fas fa-language fa-lg fa-fw align-self-center mr-3"></i><div class="media-body">{{ $training->id == 5 ? 'o’zbek tilida' : 'rus tilida' }}</div></li>
                    <li class="list-group-item d-flex"><i class="fas fa-credit-card fa-lg fa-fw align-self-center mr-3"></i><div class="media-body">yo'l haqi, ovqatlanish, turar joy va mashg'ulotlar xarajati to'lanadi </div></li>
                    <li class="list-group-item d-flex"><i class="fas fa-fire-alt fa-lg fa-fw align-self-center mr-3"></i><div class="media-body">jadal sur'atli, doimiy ravishda bandlik va jismoniy faollik</div></li>
                    <li class="list-group-item d-flex"><i class="fas fa-clock fa-lg fa-fw align-self-center mr-3"></i><div class="media-body">birinchi besh kun 09:00 dan 22:00 gacha va 6 kun 18:00 gacha<br><small>3, 4 va 5 kunlari mashg'ulot davomiyligi rejalashtirilgan vaqtdan o’tishi mumkin</small></div></li>
                    <li class="list-group-item d-flex"><i class="fas fa-pizza-slice fa-lg fa-fw align-self-center mr-3"></i><div class="media-body">4 mahal ovqat: 2 marta kofe tanaffusi, tushlik va kechki ovqat<br><small>Treningning 6-kunida kechki ovqat rejalashtirilmagan</small></div></li>
                    <li class="list-group-item d-flex"><i class="fas fa-tshirt fa-lg fa-fw align-self-center mr-3"></i><div class="media-body">jismoniy faoliyat uchun qulay erkin kiyim shakli</div></li>
                    @endif
                    <li class="list-group-item d-flex"><i class="fas fa-stopwatch fa-lg fa-fw align-self-center mr-3"></i><div class="media-body">{{ App::getLocale() == 'ru' ? ('приём заявок завершается ' . ($training->id == 5 ? '7 ноября 2019 в 11:00' : '21 октября 2019 в 11:00')) : ('arizalarni qabul qilish 2019 yil ' . ($training->id == 5 ? '7 noyabrda soat 11.00 da tugaydi' : '21 oktyabrda soat 11.00 da tugaydi')) }}</div></li>
                    --}}
                </ul>
                @if($training->status != 'draft' && $training->status != 'published')
                    <div class="card-body">
                        @if(!$user || !$user->trainingApplication($training->id))
                            @if($training->status == 'application')
                                <a href="#before-application" data-href="{{ route('training.application', ['training' => $training->id]) }}" data-toggle="modal" class="btn btn-primary">
                                    @lang('Подать заявку на участие')
                                </a>
                            @else
                                @lang('Приём заявок завершен.')
                            @endif
                        @elseif(in_array($user->trainingApplication($training->id)->status, ['draft', 'applied']))
                            <a href="{{ route('training.application', ['training' => $training->id]) }}" class="btn btn-secondary">
                                @lang('Просмотр вашей заявки')
                            </a>
                        @elseif($user->trainingApplication($training->id)->status == 'consideration')
                            <a href="#" class="btn btn-success">
                                @lang('Ваша заявка на рассмотрении')
                            </a>
                        @elseif($user->trainingApplication($training->id)->status == 'accepted')
                            <a href="#" class="btn btn-success">
                                @lang('Ваша заявка одобрена')
                            </a>
                        @else
                            <a href="#" class="btn btn-danger">
                                @lang('Ваша заявка отклонена')
                            </a>
                        @endif
                    </div>
                    @if($training->status != 'published' && $training->status != 'application' && $training->status != 'rating')
                        <div class="card-body">
                            @lang('Рассмотрение заявок завершено.')
                        </div>
                        @if($training->status != 'selection')
                            <div class="card-body">
                                @lang('Список участников сформирован.')
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>

        <div class="col-md-8 order-md-4 col-xs-12">
            @if($training->status != 'draft' && $training->status != 'published' && $training->status != 'application' && $training->status != 'rating' && $training->status != 'selection')
                <div class="alert alert-primary" role="alert">
                    @if(App::getLocale() == 'ru')
                        Процесс рассмотрения заявок завершён. Кандидаты на участие в тренинге могут посмотреть информацию по статусу заявок в своих аккаунтах. Все, кандидаты, не прошедшие отбор на данный тренинг приглашаются подавать заявки на следующие тренинги. При подаче заявок на последующие тренинги рекомендуется заполнять анкету подробно.
                    @else
                        Arizalarni ko'rib chiqish jarayoni yakunlandi. Nomzodlar  arizalarning holati to'g'risidagi ma'lumotlarni  o’z akkauntlarida ko'rishlari mumkin. Ushbu trening  tanlovidan o'tmagan barcha nomzodlar keyingi treninglarga qatnashish uchun ariza topshirishga taklif etiladilar. Keyingi treninglarga  qatnashish uchun ariza topshirishda anketani batafsil to'ldirish tavsiya etiladi.
                    @endif
                </div>
            @endif
            <p class="lead">
                {{ $training->short }}
            </p>

            {!! $training->text !!}
        </div>
    </div>
</div>

<div class="modal fade" id="before-application" tabindex="-1" role="dialog" aria-labelledby="before-application-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="before-application-label">@lang('Подтвердите ознакомление с информацией')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="@lang('Закрыть')">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="lead">
                    @lang('Для подачи заявки на участие в данном тренинге необходимо подтвердить ознакомление с информацией о тренинге и ответить на вопросы.')
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary my-1 my-sm-0" data-dismiss="modal">@lang('Закрыть')</button>
                <a href="" class="btn btn-primary my-1 my-sm-0 confirm">@lang('Подтверждаю')</a>
            </div>
        </div>
    </div>
</div>
@endsection
