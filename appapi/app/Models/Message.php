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
        // Add other columns you want to allow mass assignment for
    ];
}
