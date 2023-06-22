@component('mail::message')

@lang('Если вы не проходите регистрацию на сайте :site, проигнорируйте это письмо.', ['site' => config('app.url')])
<br>
<br>
@component('mail::button', ['url' => route('email-verification.check', $user->verification_token) . '?email=' . urlencode($user->email) ])
@lang('Подтвердить адрес')
@endcomponent

@endcomponent
