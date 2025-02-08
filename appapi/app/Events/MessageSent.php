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

    public function __construct($message, $userId, $adminId)
    {
        $this->message = $message;
        $this->sender_id = $userId;
        $this->receiver_id = $adminId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->sender_id . '.' . $this->receiver_id);
    }
}