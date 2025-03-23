<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $sender_id;
    public $receiver_id;
    public $timestamp;

    public function __construct($message, $senderId, $receiverId)
    {
        // Handle both message string and Message model instances
        if (is_object($message) && get_class($message) === 'App\Models\Message') {
            $this->message = $message;  // Use whole message object
            $this->sender_id = $message->sender_id;
            $this->receiver_id = $message->receiver_id;
        } else {
            // Legacy support for string message
            $this->message = $message;
            $this->sender_id = $senderId;
            $this->receiver_id = $receiverId;
        }
        
        $this->timestamp = now()->toDateTimeString();
    }

    /**
     * Get the channels the event should broadcast on.
     * 
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        // Get sender and receiver to determine if they are admin or user
        $sender = User::find($this->sender_id);
        $receiver = User::find($this->receiver_id);
        
        $channels = [
            // Direct channels between sender and receiver (bidirectional)
            new PrivateChannel('chat.' . $this->sender_id . '.' . $this->receiver_id),
            new PrivateChannel('chat.' . $this->receiver_id . '.' . $this->sender_id),
        ];
        
        // Add role-specific channels
        if (strtolower($sender->role) === 'admin') {
            // Admin sending to user - add admin channel
            $channels[] = new PrivateChannel('chat.admin.' . $this->sender_id);
            $channels[] = new PrivateChannel('chat.user.' . $this->receiver_id);
        } else if (strtolower($receiver->role) === 'admin') {
            // User sending to admin - add admin channel
            $channels[] = new PrivateChannel('chat.admin.' . $this->receiver_id);
            $channels[] = new PrivateChannel('chat.user.' . $this->sender_id);
        }
        
        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Ensure consistent format whether we have a string message or a Message model
        if (is_string($this->message)) {
            return [
                'message' => [
                    'message' => $this->message,
                    'sender_id' => $this->sender_id,
                    'receiver_id' => $this->receiver_id,
                    'created_at' => $this->timestamp,
                    'updated_at' => $this->timestamp,
                    'is_read' => false,
                ],
                'sender_id' => $this->sender_id,
                'receiver_id' => $this->receiver_id,
                'timestamp' => $this->timestamp
            ];
        } else {
            // Return the full message model as a consistent object
            return [
                'message' => $this->message,
                'sender_id' => $this->sender_id,
                'receiver_id' => $this->receiver_id,
                'timestamp' => $this->timestamp
            ];
        }
    }
}