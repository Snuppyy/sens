<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use App\Channels\SmsChannel;

class ResetPassword extends BaseResetPassword
{
    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return array_merge(
            parent::via($notifiable),
            [SmsChannel::class]
        );
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    public function toSms($notifiable)
    {
        return 'Ваш код для восстановления пароля: ' . $this->token;
    }
}
