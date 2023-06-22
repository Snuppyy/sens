@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="h2 font-weight-light mb-4">@lang('Информационно-образовательная платформа')</h1>

            @switch (App::getLocale())
                @case('uz')
                    <p class="lead">
                        Ushbu platformada o'quv materiallarini tayyorlash, treninglar, so'rovnomalar va mustaqil ta’lim olish vositalari taqdim etiladi. 
                    </p>

                    <p><a href="{{ route('trainings.list') }}">Treningda</a> qatnashish yoki <a href="{{ route('questionnaire', ['level' => 1]) }}">so'rovnoma va ta’limda</a> qatnashish uchun siz </a>ro'yxatdan o'tishingiz</a> yoki <a href="{{ route('login') }}">ijtimoiy tarmoq orqali tizimga kirishingiz</a> va o'zingiz haqingizda qisqacha ma'lumot berishingiz kerak.</p>

                    <h4>Treninglar</h4>

                    <p>JSST Vakilligi immunizasiya sohasidagi mutaxassislar uchun <a href="{{ route('trainings.view', ['training' => 4]) }}">ToT «Trenerlar uchun kirish kursi»ni</a> tashkil etadi.	

                    <img src="/img/training-uz.jpg" class="img-fluid my-3">
                    @break

                @case('uz-cyr')
                    <p class="lead">
                        Ушбу платформада уқув материалларини тайёрлаш, тренинглар, сўровномалар ва мустақил таълим олиш воситалари тақдим этилади. 
                    </p>

                    <p><a href="{{ route('trainings.list') }}">Тренингда</a> қатнашиш ёки <a href="{{ route('questionnaire', ['level' => 1]) }}">сўровнома ва таълимда</a> қатнашиш учун сиз <a href="{{ route('register') }}">рўйхатдан ўтишингиз</a> ёки <a href="{{ route('login') }}">ижтимоий тармоқ орқали тизимга киришингиз</a> ва ўзингиз хақингизда қисқача маълумот беришингиз керак.</p>

                    <h4>Тренинглар</h4>
                    

                    <p>ЖССТ Вакиллиги иммунизация соҳасидаги мутахассислар учун <a href="{{ route('trainings.view', ['training' => 4]) }}">ТуТ «Тренерлар учун кириш курси»ни</a> ташкил этади.</p>

                    <img src="/img/training-uz.jpg" class="img-fluid my-3">
                    @break

                @default
                    <p class="lead">
                        Платформа предоставляет инструменты для подготовки образовательных материалов, проведения тренингов, опросов и самостоятельного обучения. 
                    </p>

                    <p>Чтобы подать заявку на участие в <a href="{{ route('trainings.list') }}">тренинге</a> или <a href="{{ route('questionnaire', ['level' => 1]) }}">пройти опросы и обучение</a> необходимо <a href="{{ route('register') }}">зарегистрироваться</a> или <a href="{{ route('login') }}">войти</a> через соцсеть и предоставить немного сведений о себе.</p>

                    <h4>Тренинги</h4>
                    

                    <p>Представительство ВОЗ организовывает <a href="{{ route('trainings.view', ['training' => 4]) }}">ТоТ «Вводный курс для тренеров»</a> для специалистов в сфере иммунизации.</p>

                    <img src="/img/training.jpg" class="img-fluid my-3">

            @endswitch
        </div>

        <div class="col-md-4">
            @guest
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Вход') }}</h5>
                    <small><a href="{{ route('register') }}">@lang('Нет учётной записи?')</a></small>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group">
                            <label for="email">{{ __('Номер телефона или e-mail') }}</label>
                            <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password">{{ __('Пароль') }}</label>
                            <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="remember">
                                    {{ __('Запомнить на этом устройстве') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Войти') }}
                            </button>

                            @if (Route::has('password.request'))
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    {{ __('Забыли пароль?') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            <div class="card mt-4">
                <h5 class="card-header">@lang('Войти через соцсеть')</h5>

                <div class="card-body text-center">
                    @include('components.social_login')
                </div>
            </div>
            @else
            <div class="card mt-4">
                <h5 class="card-header">@lang('Добро пожаловать')</h5>

                <div class="card-body text-center">
                    @if(trim($fio = Auth::user()->fio))
                        @lang('Вы вошли в систему как') {{ $fio }}.
                    @else
                        @lang('Вы вошли в систему').
                    @endif
                </div>
            </div>
            @endguest
        </div>
    </div>
</div>
@endsection
