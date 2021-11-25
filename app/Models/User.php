<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Posts;
use App\Models\Comments;


class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_name',
        'first_name',
        'last_name',
        'email',
        'email_varified_token',
        'phone_number',
        'gender',
        'date_of_birth',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * The Table name.
     *
     */
    protected $table = 'users';


    /**
     * Get the Posts for the blog User.
     */
    public function Posts()
    {
        return $this->hasMany(Posts::class);
    }

    /**
     * Get the comments for the blog User.
     */

    public function Comments()
    {
        return $this->hasMany(Comments::class);
    }


}
