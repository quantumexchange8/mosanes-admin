<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CommunityPost extends Model implements HasMedia

{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'content',
    ];

    protected $appends = ['formatted_created_at', 'liked'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedCreatedAtAttribute()
    {
        $created = $this->created_at;
        $now = Carbon::now();

        if ($created->gt($now->copy()->subMinutes(5))) {
            return 'just now';
        } elseif ($created->gt($now->copy()->subHour())) {
            return round($created->diffInMinutes($now)) . ' minutes ago';
        } elseif ($created->gt($now->copy()->subHours(23))) {
            return round($created->diffInHours($now)) . ' hours ago';
        } elseif ($created->gt($now->copy()->subDays(2))) {
            return 'yesterday';
        } elseif ($created->gt($now->copy()->subDays(6))) {
            return round($created->diffInDays($now)) . ' days ago';
        } elseif ($created->gt($now->copy()->subWeeks(3))) {
            return round($created->diffInWeeks($now)) . ' weeks ago';
        }

        return $created->format('d M Y');
    }

    public function likes()
    {
        return $this->hasMany(CommunityLike::class);
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'community_likes');
    }

    public function getLikedAttribute()
    {
        $user = Auth::user();
        if (!$user) return false;

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function comments()
    {
        return $this->hasMany(CommunityComment::class, 'post_id');
    }

    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'community_post_hashtags')->withTimestamps();
    }
}

