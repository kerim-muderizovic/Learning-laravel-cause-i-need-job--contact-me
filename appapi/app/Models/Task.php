<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Task extends Model
{
    // Define the fields that can be mass-assigned
    protected $fillable = [
        'title',
        'description',
        'progress',
        'due_date',
        'priority',
    ];

    // Define the relationship with users
    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user');
    }

    public function assignedUsers()
{
    return $this->belongsToMany(User::class, 'task_user', 'task_id', 'user_id');
}

}
