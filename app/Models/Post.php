<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    //if facing any error than u can declare your table here
    protected $table = "post";

    public function User(){
        return $this->belongsTo(User::class);    
    }

    public function Comments(){
        return $this->hasMany(Comment::class);    
    }
    
}
