<?php

namespace App\Lib;

class SMSSender {
    public static function send($number, $code) {
        $input = [
            'messages' => [
                [
                    'recipient' => $number,
                    'message-id' => '0',
                    'sms' => [
                        'originator' => '3700',
                        'content' => [
                            'text' => __('Код sens.uz: ' . $code)
                        ]
                    ]
                ]
            ]
        ];

        $url = "http://91.204.239.44/broker-api/send";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8'));
        curl_setopt($ch, CURLOPT_USERPWD, 'intilish:t6Ne4cc');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);

        return true;
    }
}