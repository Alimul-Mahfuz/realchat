<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Conversation;

class ConversationController extends Controller
{
    public function getChat($receiver_id): \Illuminate\Http\JsonResponse
    {
        $userId = auth()->id();
        $conversations = Conversation::where(function ($query) use ($userId, $receiver_id) {
            $query->where('sender_id', $userId)
                ->where('receiver_id', $receiver_id);
        })
            ->orWhere(function ($query) use ($userId, $receiver_id) {
                $query->where('sender_id', $receiver_id)
                    ->where('receiver_id', $userId);
            })
            ->first();
        if ($conversations) {
            $chats = Chat::where('conversation_id', $conversations->id)->get();
        } else {
            $chats = null;
        }

        return response()->json([
            'status' => 'success',
            'chats' => $chats,
        ]);


    }
}
