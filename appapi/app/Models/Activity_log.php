<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity_log extends Model
{
    //
    use HasFactory;
   protected $table = 'user_activities';
    protected $fillable= [
        'user_id',
        'activity',
        'ip_address'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
