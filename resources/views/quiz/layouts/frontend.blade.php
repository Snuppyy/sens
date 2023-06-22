<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', substr(app()->getLocale(), 0, 2)) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@lang('TB INTILISH - Викторина')</title>
    <meta property="og:title" content="@lang('TB INTILISH - Викторина')" />
    <meta name="description" content="@lang('Викторина для всех желающих, где можно пройти обучение по вопросам туберкулеза и принять участие в опросе. Пользователи, ответившие на все вопросы правильно, получают шанс выиграть приз.')">
    <meta property="og:description" content="@lang('Викторина для всех желающих, где можно пройти обучение по вопросам туберкулеза и принять участие в опросе. Пользователи, ответившие на все вопросы правильно, получают шанс выиграть приз.')">
    <script src="{{ mix('js/frontend.js') }}" defer></script>
    <link href="{{ mix('css/frontend.css') }}" rel="stylesheet">
    @include('components.anal')
</head>
<body oncontextmenu="return false;">
    <div id="frontend">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand mb-0 h1" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Меню') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        @guest
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">{{ __('Главная') }}</a>
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
                        @endguest
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
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
            @yield('content')
        </main>

        <footer class="footer border-top">
            <div class="container">
                <span class="text-muted">&copy; {{ date('Y') }} Intilish</span>
            </div>
        </footer>
    </div>
    @include('components.jivosite')
</body>
</html>
