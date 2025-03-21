<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class admin extends Model
{
    //
    use HasFactory;
    protected $fillable=['id'];
    public $incrementing=false;
    public $primaryKey='id';
    public function user()
    {
        $this->hasOne('users','id','id');
    }
}
