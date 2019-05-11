<?php

namespace App\Models;
use App\Utils\Constants\VideoStatus;
use App\Models\Vote;
class Video extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'videos';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';


    protected $primaryKey = ['id'];
    
    protected $fillable = ['creator_id', 'room_id', 'url', 'total_vote'];

    protected $hidden = ['created', 'updated', 'status'];

    protected $attributes = [
        'status' => VideoStatus::VOTING,
    ];

    public function creator()
    {
        return $this->morphTo();
    }

    public function room()
    {
        return $this->morphTo();
    }


}
