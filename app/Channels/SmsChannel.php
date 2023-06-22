<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Exception;
use Illuminate\Notifications\Events\NotificationFailed;

class SmsChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if($notifiable->phone && ($message = $notification->toSms($notifiable))) {
            $input = [
                'messages' => [
                    [
                        'recipient' => $notifiable->routeNotificationFor('Sms'),
                        'message-id' => '0',
                        'sms' => [
                            'originator' => '3700',
                            'content' => [
                                'text' => $message
                            ]
                        ]
                    ]
                ]
            ];
    
            try {
                $url = "http://91.204.239.44/broker-api/send";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8'));
                curl_setopt($ch, CURLOPT_USERPWD, 'intilish:t6Ne4cc');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($ch);
                curl_close($ch);
            } catch (Exception $exception) {
                $event = new NotificationFailed($notifiable, $notification, 'sms', ['message' => $exception->getMessage(), 'exception' => $exception]);
                $this->events->fire($event);
            }
        }
    }
}