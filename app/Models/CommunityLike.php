<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityLike extends Model
{
    protected $fillable = [
        'user_id',
        'community_post_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function communityPost()
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }
}
