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
    
    protected $fillable = ['creator_id', 'room_id', 'url', 'total_vote'];

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
