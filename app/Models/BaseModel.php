<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $hidden = ['created, updated'];

}
