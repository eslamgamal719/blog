<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Nicolaslopezj\Searchable\SearchableTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Mindscms\Entrust\Traits\EntrustUserWithPermissionsTrait;


class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, EntrustUserWithPermissionsTrait, SearchableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    protected $searchable = [
        'columns' => [
            'users.name'       => 10,
            'users.username'   => 10,
            'users.email'      => 10,
            'users.mobile'     => 10,
            'users.bio'        => 10,
        ]
    ];


    public function receivesBroadcastNotificationsOn()
    {
        return 'App.User.'.$this->id;
    }




    public function posts() {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function comments() {
        return $this->hasMany(Comment::class, 'user_id');
    }


    public function status() {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }
}
