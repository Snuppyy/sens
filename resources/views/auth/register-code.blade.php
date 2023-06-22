@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <h4 class="card-header">{{ __('Регистрация') }} ({{ old('phone') }})</h4>

                <div class="card-body">
                    <form method="POST" action="{{ route('register-code') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="phone" class="col-md-4 col-form-label text-md-right">{{ __('Код') }}</label>

                            <div class="col-md-6">
                                <input name="code" type="number" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}" autofocus>
                                @if ($errors->has('code'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('code') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <input type="hidden" name="phone" value="{{ old('phone') }}">
                        <input type="hidden" name="token" value="{{ old('token') }}">

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary mb-2">
                                    {{ __('Подтвердить') }}
                                </button>
                                <button type="submit" name="resend" class="btn btn-secondary mb-2">
                                    {{ __('Отправить ещё раз') }}
                                </button>
                                <button type="submit" name="change" class="btn btn-secondary mb-2">
                                    {{ __('Изменить номер') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
