<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Conversation;
use Illuminate\Http\Request;


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

    public function sendMessage(Request $request)
    {
        $userId = auth()->id();
        $receiver_id=$request->receiver_id;
        $chat = new Chat();
        $conversations = Conversation::where(function ($query) use ($userId, $receiver_id) {
            $query->where('sender_id', $userId)
                ->where('receiver_id', $receiver_id);
        })
            ->orWhere(function ($query) use ($userId, $receiver_id) {
                $query->where('sender_id', $receiver_id)
                    ->where('receiver_id', $userId);
            })
            ->first();
        $chat->sender_id = $request->receiver_id;
        $chat->conversation_id = $conversations->id;
        $chat->message = $request->message;
        $chat->save();
        return response()->json([
            'status' => 'success',
        ]);
    }
}
