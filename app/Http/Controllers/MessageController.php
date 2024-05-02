<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Events\StoreMessageEvent;
use App\Http\Requests\Message\StoreRequest;
use App\Http\Resources\Message\MessageResource;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index(User $otherUser = null)
    {
        $currentUser = auth()->user();
        if (!$otherUser) {
            $otherUser = $currentUser;
        }

        $messages = Message::where(function ($query) use ($currentUser, $otherUser) {
            $query->where('sender_id', $currentUser->id)
                ->where('receiver_id', $otherUser->id);
        })->orWhere(function ($query) use ($currentUser, $otherUser) {
            $query->where('sender_id', $otherUser->id)
                ->where('receiver_id', $currentUser->id);
        })->get();

        $users = User::all(['name', 'id']);
        foreach ($users as $user) {
            $user->lastMessage = $user->lastMessageWith($currentUser);
        }
        $messages = MessageResource::collection($messages)->resolve();

        return inertia('Message/Index',
            ['messages' => $messages, 'users' => $users, 'otherUser' => $otherUser]
        );
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $data['sender_id'] = auth()->id();
        $data['receiver_id'] = $request->receiver_id;

        $message = Message::query()->create($data);

        broadcast(new NewMessageEvent($message))->toOthers();
        broadcast(new StoreMessageEvent($message))->toOthers();

        return MessageResource::make($message)->resolve();
    }
}
