@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-3 mb-3">@lang('Профиль')</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('profile.save', ['require' => $require]) }}">
        @csrf

        <h2 class="mb-4 mt-4">@lang('Ваш профиль')</h2>

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="firstname_lat">@lang('Имя латиницей (как в паспорте)'):</label>
                    <input type="text" class="form-control{{ $errors->has('firstname_lat') ? ' is-invalid' : '' }}" id="firstname_lat" name="firstname_lat" value="{{ old('firstname_lat', $user->firstname_lat) }}" placeholder="@lang('IVAN')">
                    @if ($errors->has('firstname_lat'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('firstname_lat') }}</strong>
                        </span>
                    @endif
                </div>
        
                <div class="form-group">
                    <label for="lastname_lat">@lang('Фамилия латиницей (как в паспорте)'):</label>
                    <input type="text" class="form-control{{ $errors->has('lastname_lat') ? ' is-invalid' : '' }}" id="lastname_lat" name="lastname_lat" value="{{ old('lastname_lat', $user->lastname_lat) }}" placeholder="@lang('PETROV')">
                    @if ($errors->has('lastname_lat'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('lastname_lat') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
  
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="firstname">@lang('Имя кириллицей'):</label>
                    <input type="text" class="form-control{{ $errors->has('firstname') ? ' is-invalid' : '' }}" id="firstname" name="firstname" value="{{ old('firstname', $user->firstname) }}" placeholder="@lang('ИВАН')">
                    @if ($errors->has('firstname'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('firstname') }}</strong>
                        </span>
                    @endif
                </div>
        
                <div class="form-group">
                    <label for="lastname">@lang('Фамилия кириллицей'):</label>
                    <input type="text" class="form-control{{ $errors->has('lastname') ? ' is-invalid' : '' }}" id="lastname" name="lastname" value="{{ old('lastname', $user->lastname) }}" placeholder="@lang('ПЕТРОВ')">
                    @if ($errors->has('lastname'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('lastname') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>
  
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <label for="passport">@lang('Серия и номер паспорта'):</label>
                    <input type="text" class="form-control{{ $errors->has('passport') ? ' is-invalid' : '' }}" id="passport" name="passport" value="{{ old('passport', $user->passport) }}" placeholder="@lang('AA 0000000')">
                    @if ($errors->has('passport'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('passport') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        
            <div class="col-lg-4">
                <div class="form-group">
                    <label for="expire">@lang('Паспорт действителен до'):</label>
                    <input type="date" class="form-control{{ $errors->has('expire') ? ' is-invalid' : '' }}" id="expire" name="expire" value="{{ old('expire', $user->expire) }}" min="{{ date_format(now(), 'Y-m-d') }}" placeholder="@lang('01.01.2028')">
                    @if ($errors->has('expire'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('expire') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        
            <div class="col-lg-4">        
                <div class="form-group">
                    <label for="issued">@lang('Паспорт выдан'):</label>
                    <input type="text" class="form-control{{ $errors->has('issued') ? ' is-invalid' : '' }}" id="issued" name="issued" value="{{ old('issued', $user->issued) }}" placeholder="@lang('Яшнабадский РОВД г. Ташкента')">
                    @if ($errors->has('issued'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('issued') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>
  
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <label for="country">@lang('Страна'):</label>
                    <select class="form-control{{ $errors->has('country') ? ' is-invalid' : '' }}" id="country" name="country">
                    @foreach (["Австралия","Австрия","Азербайджан","Албания","Алжир","Ангола","Андорра","Антигуа и Барбуда","Аргентина","Армения","Афганистан","Багамы","Бангладеш","Барбадос","Бахрейн","Белоруссия","Белиз","Бельгия","Бенин","Болгария","Боливия","Босния и Герцеговина","Ботсвана","Бразилия","Бруней","Буркина-Фасо","Бурунди","Бутан","Вануату","Великобритания","Венгрия","Венесуэла","Восточный Тимор","Вьетнам","Габон","Гаити","Гайана","Гамбия","Гана","Гватемала","Гвинея","Гвинея-Бисау","Германия","Гондурас","Гренада","Греция","Грузия","Дания","Джибути","Доминика","Доминиканская Республика","Египет","Замбия","Зимбабве","Израиль","Индия","Индонезия","Иордания","Ирак","Иран","Ирландия","Исландия","Испания","Италия","Йемен","Кабо-Верде","Казахстан","Камбоджа","Камерун","Канада","Катар","Кения","Кипр","Киргизия","Кирибати","КНР","Колумбия","Коморы","Республика Конго","Демократическая Республика Конго","КНДР","Республика Корея","Коста-Рика","Кот-д’Ивуар","Куба","Кувейт","Лаос","Латвия","Лесото","Либерия","Ливан","Ливия","Литва","Лихтенштейн","Люксембург","Маврикий","Мавритания","Мадагаскар","Македония","Малави","Малайзия","Мали","Мальдивы","Мальта","Марокко","Маршалловы Острова","Мексика","Мозамбик","Молдавия","Монако","Монголия","Мьянма","Намибия","Науру","Непал","Нигер","Нигерия","Нидерланды","Никарагуа","Новая Зеландия","Норвегия","ОАЭ","Оман","Пакистан","Палау","Панама","Папуа — Новая Гвинея","Парагвай","Перу","Польша","Португалия","Россия","Руанда","Румыния","Сальвадор","Самоа","Сан-Марино","Сан-Томе и Принсипи","Саудовская Аравия","Свазиленд","Сейшельские Острова","Сенегал","Сент-Винсент и Гренадины","Сент-Китс и Невис","Сент-Люсия","Сербия","Сингапур","Сирия","Словакия","Словения","США","Соломоновы Острова","Сомали","Судан","Суринам","Сьерра-Леоне","Таджикистан","Таиланд","Танзания","Того","Тонга","Тринидад и Тобаго","Тувалу","Тунис","Туркмения","Турция","Уганда","Узбекистан","Украина","Уругвай","Федеративные Штаты Микронезии","Фиджи","Филиппины","Финляндия","Франция","Хорватия","Центральноафриканская Республика","Чад","Черногория","Чехия","Чили","Швейцария","Швеция","Шри-Ланка","Эквадор","Экваториальная Гвинея","Эритрея","Эстония","Эфиопия","ЮАР","Южный Судан","Ямайка","Япония"] as $i => $country)
                        <option value="{{ $i + 1 }}"{{ (!old('country', $user->country) && $country == 'Узбекистан') || old('country', $user->country) == $i + 1 ? ' selected' : '' }}>{{ $country }}</option>
                    @endforeach
                    </select>
                    @if ($errors->has('country'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('country') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        
            <div class="col-lg-4">        
                <div class="form-group">
                    <label for="province">@lang('Область'):</label>
                    <select class="form-control{{ $errors->has('province_id') ? ' is-invalid' : '' }}" id="province" name="province_id">
                    @if ($user->province)
                        <option value="{{ $user->province->id }}" selected>{{ $user->province->ru }}</option>
                    @endif
                    </select>
                    @if ($errors->has('province_id'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('province_id') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">        
                <div class="form-group">
                    <label for="city">@lang('Населённый пункт'):</label>
                    <select class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}" id="city" name="city_id">
                    @if ($user->city)
                        <option value="{{ $user->city->id }}" selected>{{ $user->city->ru }}</option>
                    @endif
                    </select>
                    @if ($errors->has('city_id'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('city_id') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>
  
        <div class="row">
            {{--
            <div class="col-lg-6">
                <div class="form-group">
                    <label>@lang('Статус уязвимости'):</label>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="vulnerability[]" value="injured" id="vulnerability-injured"{{ in_array('injured', $user->vulnerability) ? ' checked' : '' }}>
                        <label class="custom-control-label" for="vulnerability-injured">
                            @lang('Пострадавший от туберкулёза')
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="vulnerability[]" value="ljvs" id="vulnerability-ljvs"{{ in_array('ljvs', $user->vulnerability) ? ' checked' : '' }}>
                        <label class="custom-control-label" for="vulnerability-ljvs">
                            @lang('ЛЖВС')
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="vulnerability[]" value="drugs-user" id="vulnerability-drugs-user"{{ in_array('drugs-user', $user->vulnerability) ? ' checked' : '' }}>
                        <label class="custom-control-label" for="vulnerability-drugs-user">
                            @lang('Потребитель инъекционных наркотиков')
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="vulnerability[]" value="confined" id="vulnerability-confined"{{ in_array('confined', $user->vulnerability) ? ' checked' : '' }}>
                        <label class="custom-control-label" for="vulnerability-confined">
                            @lang('Отбывающий наказание в местах лишения свободы / освободившийся')
                        </label>
                    </div>
                </div>
            </div>
            --}}

            <div class="col-lg-6">
                <div class="form-group">
                    <label>@lang('Членство'):</label>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="membership" value="intilish" id="membership-intilish"{{ $user->membership == 'intilish' ? ' checked' : '' }}>
                        <label class="custom-control-label" for="membership-intilish">
                            @lang('Член ННО РИОЦ "INTILISH"')
                        </label>
                    </div>
                </div>
            </div>
        </div>
  
        <button type="submit" class="btn btn-primary mt-3">
            @if ($user->level)
                @lang('Сохранить')
            @else
                @lang('Продолжить')
            @endif
        </button>

        {{--
        <h2 class="mb-4 mt-5">@lang('Аккаунты в соцсетях')</h2>

        <div class="social">
            <a href="{{ url('/auth/facebook') }}" class="btn btn-secondary disabled">
                <i class="fab fa-facebook fa-lg"></i>
                @lang('Facebook')
            </a>
            <a href="{{ url('/auth/google') }}" class="btn btn-secondary disabled">
                <i class="fab fa-google fa-lg"></i>
                @lang('Google')
            </a>
            <a href="{{ url('/auth/odnoklassniki') }}" class="btn btn-secondary disabled">
                <i class="fab fa-odnoklassniki fa-lg"></i>
                @lang('Odnoklassniki')
            </a>
            <a href="{{ url('/auth/twitter') }}" class="btn btn-secondary disabled">
                <i class="fab fa-twitter fa-lg"></i>
                @lang('Twitter')
            </a>
            <a href="{{ url('/auth/vk') }}" class="btn btn-secondary disabled">
                <i class="fab fa-vk fa-lg"></i>
                @lang('Vkontakte')
            </a>
        </div>
        --}}
    </form>

    <div class="row mt-5">
        <div class="col-md-12">
            <small class="text-muted">
                * @lang('Указание паспортных данных требуется для предоставления призов и связи с вами.')
                <br>
                ** @lang('Паспортные данные конфиденциальны и не предоставляются третьим лицам.')
            </small>
        </div>
    </div>

    <div class="modal fade" id="social-dettach" tabindex="-1" role="dialog" aria-labelledby="social-dettach-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="social-dettach-label">{{ __('Подключение учётной записи') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Закрыть') }}">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="lead">
                        @lang('Отсоединить учётную запись :name от текущей учётной записи?', ['name' => '<span class="social-name"></span>'])
                    </p>
                    <ul>
                        <li>@lang('Вы больше не сможете использовать :name для входа', ['name' => '<span class="social-name"></span>'])</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Закрыть') }}</button>
                    <a href="" class="btn btn-primary confirm">{{ __('Продолжить') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="social-attach" tabindex="-1" role="dialog" aria-labelledby="social-attach-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="social-attach-label">{{ __('Отключение учётной записи') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Закрыть') }}">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="lead">
                        @lang('Соединить текущую учётную запись с учётной записью :name?', ['name' => '<span class="social-name"></span>'])
                    </p>
                    <ul>
                        <li>@lang('Вы сможете использовать :name для входа', ['name' => '<span class="social-name"></span>'])</li>
                        <li>@lang('Данные учётных записей будут объединены')</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Закрыть') }}</button>
                    <a href="" class="btn btn-primary confirm">{{ __('Продолжить') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
