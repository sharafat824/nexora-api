<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatMessageResource;

class ChatController extends Controller
{
     public function index(Request $request)
    {
        $messages = ChatMessage::where('user_id', $request->user()->id)
            ->orWhere('admin_id', '!=', null)
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        return ChatMessageResource::collection($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = $request->user()->chatMessages()->create([
            'message' => $request->message,
            'direction' => 'out'
        ]);

        // In a real app, you would notify admins here

        return new ChatMessageResource($message);
    }
}
