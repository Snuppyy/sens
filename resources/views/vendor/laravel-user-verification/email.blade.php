@lang('Если вы не проходите регистрацию на сайте :site, проигнорируйте это письмо.', ['site' => config('app.url')])

@lang('Чтобы подтвердить адрес, перейдите по этой ссылке: :link', ['link' => '<a href="' . ($link = route('email-verification.check', $user->verification_token) . '?email=' . urlencode($user->email)) . '">' . $link . '</a>'])
