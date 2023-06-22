@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Подтверждение адреса электронной почты') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('На ваш адрес отправлено сообщение со ссылкой для подтверждения.') }}
                        </div>
                    @endif

                    {{ __('Для того, чтобы продолжить, проверьте ваш почтовый ящик.') }}
                    {{ __('Если письмо не пришло') }}, <a href="{{ route('verification.resend') }}">{{ __('нажмите сюда, чтобы отправить ещё одно') }}</a>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
