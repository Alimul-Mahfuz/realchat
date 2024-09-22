<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Models\Chat;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;


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
            Chat::query()->where('conversation_id',$conversations->id)->update(['is_read' => true]);
            $conversation_id=$conversations->id;
        } else {
            $chats = null;
            $conversation_id = null;
        }

        return response()->json([
            'status' => 'success',
            'chats' => $chats,
            'conversation_id'=>$conversation_id,
            'is_read'=>true
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
        $chat->sender_id = $userId;
        $chat->conversation_id = $conversations->id;
        $chat->message = $request->input('message');
        $chat->save();
        Event::dispatch(new NewMessageEvent($conversations,$chat));
        return response()->json([
            'status' => 'success',
            'data'=>[
                'id'=>$chat->id,
                'is_read'=>false,
                'conversation_id'=>$chat->conversation_id,
                'message'=>$request->input('message')
            ],
        ]);
    }
}
