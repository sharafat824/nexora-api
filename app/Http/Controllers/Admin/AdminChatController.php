<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatMessageResource;

class AdminChatController extends Controller
{
public function getChatUsers()
{
    $latestMessages = ChatMessage::selectRaw('MAX(id) as id')
        ->groupBy('user_id');

    $users = ChatMessage::whereIn('id', $latestMessages)
        ->with(['user'])
        ->orderByDesc('created_at')
        ->get()
        ->pluck('user')
        ->unique('id')
        ->values();

    // Attach unread count
    foreach ($users as $user) {
        $user->unread_count = ChatMessage::where('user_id', $user->id)
            ->where('direction', 'out')
            ->where('read', false)
            ->count();
    }

    return success($users);
}


public function sendMessage(Request $request, User $user)
{
    $request->validate([
        'message' => 'required|string|max:1000'
    ]);

    $message = ChatMessage::create([
        'user_id' => $user->id,
        'admin_id' => $request->user()->id,
        'message' => $request->message,
        'direction' => 'in',
        'read' => false,
    ]);

    return new ChatMessageResource($message);
}


public function getMessages(User $user)
{
    $messages = ChatMessage::where('user_id', $user->id)
        ->orderBy('created_at', 'asc')
        ->limit(100)
        ->get();

    return ChatMessageResource::collection($messages);
}
public function markAsRead(User $user)
{
    ChatMessage::where('user_id', $user->id)
        ->where('direction', 'out')
        ->where('read', false)
        ->update(['read' => true]);

    return response()->json(['message' => 'Marked as read']);
}

}
