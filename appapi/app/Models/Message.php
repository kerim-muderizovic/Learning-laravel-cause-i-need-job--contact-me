<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'sender_id',  // Add this line
        'receiver_id',
        'message',
        'is_read',
        // Add other columns you want to allow mass assignment for
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
    ];
    
    /**
     * Get the user who sent the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    /**
     * Get the user who received the message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
