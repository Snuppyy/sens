<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', substr(app()->getLocale(), 0, 2)) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@lang('Sens.uz')</title>
    <link href="{{ mix('css/site.css') }}" rel="stylesheet">
    @if (!file_exists(public_path('/hot')))
        <script src="{{ mix('js/manifest.js') }}" defer></script>
        <script src="{{ mix('js/common.js') }}" defer></script>
        <script src="{{ mix('js/vendor.js') }}" defer></script>
    @endif
    <script src="{{ mix('js/site.js') }}" defer></script>
    @include('components.anal')
</head>
<body -oncontextmenu="return false;">
    <div id="frontend">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand mb-0 h1" href="{{ url('/') }}">
                    <i class="fas fa-graduation-cap fa-lg align-self-center mr-2"></i>
                    @lang('SENS.UZ')
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Меню') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">
                    </ul>

                    <ul class="navbar-nav ml-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">{{ __('Опросники') }}</a>
                            </li>
                        @endauth

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('trainings.list') }}">{{ __('Обучение') }}</a>
                        </li>

                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('history') }}">{{ __('История') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profile') }}">{{ __('Профиль') }}</a>
                            </li>
                            {{-- <li class="nav-item">
                                @if (!Auth::user()->questionnaire)
                                    <a href="{{ route('questionnaire') }}" class="nav-link">{{ __('Опрос') }}</a>
                                @elseif (Auth::user()->questionnaire->result < 100)
                                    @if (Auth::user()->questionnaire->closed && !Auth::user()->questionnaire->training_finished)
                                        <a href="{{ route('questionnaire') }}" class="nav-link">{{ __('Обучение') }}</a>
                                    @elseif (!Auth::user()->questionnaire->closed || (config(Auth::user()->training_started ? 'app.round_length' : 'app.wait_without_training') - (time() - Auth::user()->questionnaire->updated_at->timestamp)) <= 0)
                                        <a href="{{ route('questionnaire') }}" class="nav-link">{{ __('Опрос') }}</a>
                                    @endif
                                @endif
                            </li> --}}
                        @endauth

                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Вход') }}</a>
                            </li>
                            <li class="nav-item">
                                @if (Route::has('register'))
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Регистрация') }}</a>
                                @endif
                            </li>
                        @else
                            <li class="nav-item">
                                    <a class="nav-link" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Выход') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                            </li>
                        @endguest

                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ language()->getName() }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                @foreach (language()->allowed() as $code => $name)
                                <a class="dropdown-item" href="{{ language()->back($code) }}">
                                    {{ $name }}
                                </a>
                                @endforeach
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @if($errors->count())
                <div class="container">
                    @foreach($errors->all() as $error)
                        <div class="alert alert-danger">
                            {{ $error }}
                        </div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="footer border-top">
            <div class="container">
                <span class="text-muted">@lang('Технология Nova Creation Lab') &copy; {{ date('Y') }}</span>
            </div>
        </footer>
    </div>
    @include('components.jivosite')
</body>
</html>
