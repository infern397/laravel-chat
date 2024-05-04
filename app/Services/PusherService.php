<?php

namespace App\Services;

use App\Models\User;
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

    public function notify($userId, $senderId, $message)
    {
        $otherUserName = User::query()->find($senderId)->name;
        error_log(route('index', ['otherUser' => $senderId] ));
        $this->beamsClient->publishToInterests(
            ['user-' . $userId . '-' . $senderId],
            [
                "web" => [
                    "notification" => [
                        "title" => "New message from " . $otherUserName ,
                        "body" => $message,
                        "deep_link" => route('index', ['otherUser' => $senderId] )
                    ]
                ]
            ]
        );
    }
}
