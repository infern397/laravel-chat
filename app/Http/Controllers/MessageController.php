<?php

namespace App\Http\Controllers;

use App\Events\StoreMessageEvent;
use App\Http\Requests\Message\StoreRequest;
use App\Http\Resources\Message\MessageResource;
use App\Models\Message;
use App\Models\User;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index(User $otherUser = null)
    {
        $user = auth()->user();
        if (!$otherUser) {
            $otherUser = $user;
        }
        $messages = Message::all();
        $users = User::all(['name', 'id']);
        $messages = MessageResource::collection($messages)->resolve();
        return inertia('Message/Index',
            ['messages' => $messages, 'users' => $users, 'otherUser' => $otherUser]
        );
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $message = Message::query()->create($data);

        broadcast(new StoreMessageEvent($message))->toOthers();

        return MessageResource::make($message)->resolve();
    }
}
