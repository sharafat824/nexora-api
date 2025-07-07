<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Resources\ChatMessageResource;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
   public function index(Request $request)
{
    $user = $request->user();

    if ($user->is_admin) {
        // Admin must provide user_id to load specific user's chat
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $messages = ChatMessage::where('user_id', $request->user_id)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();
    } else {
        // User: Load all messages related to them
        $messages = ChatMessage::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();
    }

    return ChatMessageResource::collection($messages);
}


  public function store(Request $request)
{
    $request->validate([
        'message' => 'required|string|max:1000',
        'user_id' => $request->user()->is_admin ? 'required|exists:users,id' : 'nullable',
    ]);

    $user = $request->user();
    $isAdmin = $user->is_admin;

    $message = new ChatMessage();
    $message->message = $request->message;
    $message->direction = $isAdmin ? 'in' : 'out';
    $message->read = false;

    if ($isAdmin) {
        $message->admin_id = $user->id;
        $message->user_id = $request->input('user_id');
    } else {
        $message->user_id = $user->id;
    }

    $message->save();

    return new ChatMessageResource($message);
}
public function markAsRead(Request $request)
{
    ChatMessage::where('admin_id', '!=', null)
        ->where('user_id', $request->user()->id)
        ->where('direction', 'in')
        ->where('read', false)
        ->update(['read' => true]);

    return response()->json(['message' => 'Marked as read']);
}

}
