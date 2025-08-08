<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CommunityComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
    ];

    protected $appends = ['formatted_created_at'];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function replies()
    {
        return $this->hasMany(CommunityComment::class, 'parent_id')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');
    }
}
