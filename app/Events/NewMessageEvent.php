<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Conversation $conversation;
    public Chat $chat;

    /**
     * Create a new event instance.
     */
    public function __construct(Conversation $conversation, Chat $chat)
    {
        $this->conversation = $conversation;
        $this->chat = $chat;
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->chat->id,
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->chat->sender_id,
            'is_read' => false,
            'message' => $this->chat->message
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('new_message.' . $this->conversation->id),
        ];
    }
}
