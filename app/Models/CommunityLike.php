<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityLike extends Model
{
    protected $fillable = [
        'user_id',
        'community_post_id',
    ];
}
