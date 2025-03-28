<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;
    
    public $incrementing = false;
    public $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id',
        'require_strong_password',
        'allow_creating_accounts',
        'user_deletion_days',
        'enable_audit_logs',
        'enable_reset_password'
    ];
    
    /**
     * Get the user associated with this admin.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
