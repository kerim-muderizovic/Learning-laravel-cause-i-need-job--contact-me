<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Task;
use App\Models\User;

class TaskUser extends Model
{
     use HasFactory;
    protected $table = 'task_user';

    // The attributes that are mass assignable.
    protected $fillable = [
        'task_id',
        'user_id',
    ];

    // Define the relationship to the Task model
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Define the relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
