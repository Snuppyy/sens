@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <h4 class="card-header">{{ __('Регистрация') }}</h4>

                <div class="card-body">
                    <div class="alert alert-info" role="alert"{{ $phone_verified || $email_verified ? ' style="display: none"' : '' }}>
                        {{ __('Для регистрации подтвердите свой телефон или e-mail.') }}
                    </div>
                    <form method="POST" action="{{ route('register') }}" id="registration-form">
                        @csrf

                        <div class="form-group row">
                            <label for="phone" class="col-md-4 col-form-label text-md-right">{{ __('Номер телефона') }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="phone" type="tel" class="form-control{{ $phone_verified ? ' is-valid' : '' }}" value="{{ $phone }}" {{ $phone ? '' : ' autofocus' }}>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary{{ $phone_verified ? ' btn-success' : '' }}" type="button" id="confirm-phone" disabled>{{ __('Подтвердить') }}</button>
                                    </div>
                                  </div>

                                @if ($errors->has('phone'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('phone') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Адрес e-mail') }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="email" type="email" class="form-control{{ $email_verified ? ' is-valid' : '' }}" value="{{ $email }}">

                                    <div class="input-group-append">
                                        <button class="btn btn-primary{{ $email_verified ? ' btn-success' : '' }}" type="button" id="confirm-email" disabled>{{ __('Подтвердить') }}</button>
                                    </div>

                                    @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Или зайдите через соцсеть') }}</label>
                            <div class="col-md-6">
                                @include('components.social_login')
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Придумайте пароль') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Подтверждение пароля') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="promocode" class="col-md-4 col-form-label text-md-right">{{ __('Промокод') }}</label>

                            <div class="col-md-6">
                                <input id="promocode" type="password" value="{{ old('promocode') }}" class="form-control{{ $errors->has('promocode') ? ' is-invalid' : '' }}" name="promocode">
                                @if ($errors->has('promocode'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('promocode') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <input type="hidden" name="token" value="{{ old('token') }}">

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary" disabled>
                                    {{ __('Зарегистрироваться') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="phone-confirmation-modal" tabindex="-1" role="dialog" aria-labelledby="phone-confirmation-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="phone-confirmation-modal-label">{{ __('Подтверждение телефона') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Закрыть') }}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-secondary" role="alert">
            @lang('Пожалуйста, подождите...')
        </div>
        <div class="alert alert-danger fail" role="alert">
            @lang('Возникла какая-то проблема. Пожалуйста, проверьте соединение с интернетом и повторите попытку.')
        </div>
        <div class="alert alert-warning used" role="alert">
            @lang('Указанный номер <span class="phone-confirmation-number"></span> уже использован для регистрации.')
        </div>
        <div class="alert alert-success" role="alert">
            @lang('На номер <span class="phone-confirmation-number"></span> отправлено сообщение с кодом.')
        </div>
        <div class="alert alert-danger error" role="alert">
            @lang('Указан неверный код.')
        </div>
        <div class="form-group">
            <input type="number" name="code" class="form-control" placeholder="@lang('Код из SMS')" required disabled autofocus>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary my-1 my-sm-0" data-dismiss="modal">{{ __('Закрыть') }}</button>
        <button type="button" class="btn btn-secondary my-1 my-sm-0 send">{{ __('Отправить ещё раз') }} <span id="phone-confirmation-countdown"></span></button>
        <button type="button" class="btn btn-primary my-1 my-sm-0 confirm" disabled>{{ __('Подтвердить') }}</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="email-confirmation-modal" tabindex="-1" role="dialog" aria-labelledby="email-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="email-modal-label">{{ __('Подтверждение адреса e-mail') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Закрыть') }}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-secondary" role="alert">
            @lang('Пожалуйста, подождите...')
        </div>
        <div class="alert alert-danger fail" role="alert">
            @lang('Возникла какая-то проблема. Пожалуйста, проверьте соединение с интернетом и повторите попытку.')
        </div>
        <div class="alert alert-warning used" role="alert">
            @lang('Указанный адрес e-mail <span class="email-confirmation-address"></span> уже использован для регистрации.')
        </div>
        <div class="alert alert-success" role="alert">
            @lang('На адрес <span class="email-confirmation-address"></span> отправлено сообщение со ссылкой для подтверждения. Если письма нет в папке «Входящие», проверьте папку «СПАМ».<br>Не закрывайте данное окно! Зайдите в Вашу электронную почту и пройдите по ссылке подтверждения адреса электронной почты в письме. После чего нажмите «проверить» в данном окне.')
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary my-1 my-sm-0" data-dismiss="modal">{{ __('Закрыть') }}</button>
        <button type="button" class="btn btn-secondary my-1 my-sm-0 send">{{ __('Отправить ещё раз') }}</button>
        <button type="button" class="btn btn-primary my-1 my-sm-0 confirm">{{ __('Проверить') }}</button>
      </div>
    </div>
  </div>
</div>
@endsection
