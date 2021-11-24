<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FriendRequest extends Model
{
    use HasFactory;  
    protected $fillable = [
        'sender',
        'reciever',
        'status',
    ];
    protected $table = 'friend_request';

    public function users(){
        return $this->hasMany(User::class);
    }
}
