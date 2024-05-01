<?php

namespace App\Http\Controllers;

use App\Http\Requests\Message\StoreRequest;
use App\Http\Resources\Message\MessageResource;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return inertia('Message/Index');
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $message = Message::query()->create($data);

        return MessageResource::make($message)->resolve();
    }
}
