<?php

namespace App\Http\Controllers;

use App\Events\StoreMessageEvent;
use App\Http\Requests\Message\StoreRequest;
use App\Http\Resources\Message\MessageResource;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::all();
        $messages = MessageResource::collection($messages)->resolve();
        return inertia('Message/Index', ['messages' => $messages]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $message = Message::query()->create($data);

        broadcast(new StoreMessageEvent($message))->toOthers();

        return MessageResource::make($message)->resolve();
    }
}
