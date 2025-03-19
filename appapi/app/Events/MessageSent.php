<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $sender_id;
    public $receiver_id;
    public $timestamp;

    public function __construct($message, $senderId, $receiverId)
    {
        $this->message = $message;
        $this->sender_id = $senderId;
        $this->receiver_id = $receiverId;
        $this->timestamp = now()->toDateTimeString();
    }

    /**
     * Get the channels the event should broadcast on.
     * 
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        // Broadcast on two channels to make sure both users receive the message
        return [
            new PrivateChannel('chat.' . $this->sender_id . '.' . $this->receiver_id),
            new PrivateChannel('chat.' . $this->receiver_id . '.' . $this->sender_id),
        ];
    }
}