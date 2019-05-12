<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $hidden = ['created, updated'];

    public static function boot()
    {
        parent::boot();

        self::created(function($model){
            $model->created = Carbon::now(new CarbonTimeZone(0));
            $model->updated = Carbon::now(new CarbonTimeZone(0));
        });

        self::updated(function($model){
            $model->updated = Carbon::now(new CarbonTimeZone(0));
        });
    }

}
