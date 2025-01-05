<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
// use Illuminate\Mail\Events\MessageSent;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
class MessageController extends Controller
{
    public function index(Request $request)
{
    $messages = Message::where(function ($query) use ($request) {
        $query->where('sender_id', auth()->id())
              ->where('receiver_id', $request->receiver_id);
    })->orWhere(function ($query) use ($request) {
        $query->where('sender_id', $request->receiver_id)
              ->where('receiver_id', auth()->id());
    })->orderBy('created_at')->get();

    return response()->json($messages);
}

public function store(Request $request)
{
    $request->validate([
        'receiver_id' => 'required|exists:users,id',
        'message' => 'required|string',
    ]);

    $message = Message::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $request->receiver_id,
        'message' => $request->message,
    ]);

    // Broadcast the event
    event(new MessageSent($message));

    return response()->json($message, 201);
}
}
