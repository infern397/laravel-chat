<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('messages.{senderId}.{receiverId}', function ($user, $senderId, $receiverId) {
    return $user->id === (int) $senderId || $user->id === (int) $receiverId;
});

Broadcast::channel('new_messages.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('online_status', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
}, ['guards' => ['web']]);
