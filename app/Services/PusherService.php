<?php

namespace App\Services;

use Pusher\PushNotifications\PushNotifications;

class PusherService
{
    protected $beamsClient;

    public function __construct()
    {
        $this->beamsClient = new PushNotifications([
            "instanceId" => env('PUSHER_BEAMS_INSTANCE_ID'),
            "secretKey" => env('PUSHER_BEAMS_SECRET_KEY'),
        ]);
    }

    public function notify($userId, $message)
    {
        $this->beamsClient->publishToInterests(
            ['user-' . $userId],
            [
                "web" => [
                    "notification" => [
                        "title" => "New message",
                        "body" => $message,
                    ]
                ]
            ]
        );
    }
}
