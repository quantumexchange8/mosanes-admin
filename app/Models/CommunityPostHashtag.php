<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityPostHashtag extends Model
{
    protected $fillable = [
        'community_post_id',
        'hashtag_id',
    ];
}
