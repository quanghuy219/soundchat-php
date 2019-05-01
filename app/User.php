<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends BaseModel
{
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password_salt', 'password_hash',
    ];
}
