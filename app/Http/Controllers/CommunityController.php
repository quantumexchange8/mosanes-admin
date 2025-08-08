<?php

namespace App\Http\Controllers;

use App\Models\CommunityComment;
use App\Models\CommunityLike;
use App\Models\CommunityPost;
use App\Models\CommunityPostHashtag;
use App\Models\Hashtag;
use App\Models\User;
use App\Notifications\CommunityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CommunityController extends Controller
{

    public function index($userId = null)
    {
        $user = Auth::user();
        $user->profile_photo = $user->getFirstMediaUrl('profile_photo');
        $user->community_cover = $user->getFirstMediaUrl('community_cover');

        return Inertia::render('Member/Community/MemberCommunity', [
            'postCounts' => CommunityPost::where('user_id', Auth::id())->count(),
            'likeCounts' => $this->getUserTotalLikes(),
            'commentCounts' => $this->getUserTotalComments(),
        ]);
    }

    public function communityProfile($userId = null)
    {
        $userId = $userId ?? Auth::id();
        $user = User::find($userId);

        $user->profile_photo = $user->getFirstMediaUrl('profile_photo');
        $user->community_cover = $user->getFirstMediaUrl('community_cover');

        return Inertia::render('Member/Community/CommunityProfile', [
            'postCounts' => CommunityPost::where('user_id', $userId)->count(),
            'likeCounts' => $this->getUserTotalLikes($userId),
            'commentCounts' => $this->getUserTotalComments($userId),
            'user' =>  $user,
        ]);
    }
    private function getUserTotalLikes($userId = null)
    {
        $userId = $userId ?: Auth::id();
        
        return CommunityLike::whereHas('communityPost', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();
    }
    private function getUserTotalComments($userId = null)
    {
        $userId = $userId ?: Auth::id();
        
        return CommunityComment::whereHas('post', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();
    }

    public function updateCommunityIntro(Request $request){
        $request->validate([
            'community_intro' => 'nullable|string',
        ]);

        $user = auth()->user();
        $user->community_intro = $request->community_intro;
        $user->save();

        return response()->json([
            'success' => true,
        ]);
    }

    public function updateCoverImage(Request $request)
    {        
        $request->validate([
            'community_cover' => 'required|image|max:2048',
        ]);

        $user = Auth::user();
        $user->clearMediaCollection('community_cover');
        $user->addMediaFromRequest('community_cover')->toMediaCollection('community_cover');

        return response()->json([
            'success' => true,
            'cover_url' => $user->getFirstMediaUrl('community_cover'),
        ]);
    }

    function syncHashtags($post, $hashtags) {
        $hashtagIds = collect($hashtags)->map(function ($tag) {
            return Hashtag::firstOrCreate(['name' => $tag])->id;
        });
        $post->hashtags()->sync($hashtagIds);
    }

    public function createCommunityPost(Request $request)
    {
        $user = Auth::user();

        $post = CommunityPost::create([
            'user_id' => Auth::id(),
            'content' => $request->captions,
        ]);

        $hashtags = json_decode($request->input('hashtags', '[]'), true);
            $this->syncHashtags($post, $hashtags);

        if ($request->hasFile('community_post')) {
            foreach ($request->file('community_post') as $file) {
                $post->addMedia($file)->toMediaCollection('community_post');
            }
        }

        $post->load('media');

        return response()->json([
            'success' => true,
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
                ],
                'formatted_created_at' => $post->formatted_created_at,
                'comments_count' => 0,
                'likes_count' => 0,
                'liked' => false,
                'media' => $post->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'original_url' => $media->getUrl(), 
                    ];
                })
            ],
            'toast' => [
                'title' => trans('public.toast_create_community_post_success'),
                'type' => 'success'
            ]
        ]);
    }

    public function getCommunityPosts(Request $request)
    {
        $query = CommunityPost::with([
            'user:id,name',
            'media'
        ])
        ->withCount(['likes', 'comments']);

        // view user profile
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Search in content
                $q->where('content', 'like', "%{$search}%")
                    // Or join to search user name
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->has('filters')) {
            $filters = is_array($request->filters) ? $request->filters : [$request->filters];
            foreach ($filters as $filter) {
                switch ($filter) {
                    case 'with-media':
                        $query->whereHas('media');
                        break;
                    case 'text-only':
                        $query->whereDoesntHave('media');
                        break;
                    case 'my-posts':
                        $query->where('user_id', Auth::id());
                        break;
                    case 'liked-posts':
                        $query->whereHas('likes', function($q) {
                            $q->where('user_id', Auth::id());
                        });
                        break;
                }
            }
        }
        
        $posts = $query->latest()->paginate(10);
        foreach ($posts as $post) {
            if ($post->user) {
                $post->user->profile_photo = $post->user->getFirstMediaUrl('profile_photo');
            }
        }

        return response()->json($posts);
    }

    public function updateCommunityPost(Request $request)
    {
        $user = Auth::user();
        $post = CommunityPost::findOrFail($request->id);

        // Delete media files
        if ($request->has('clear_images')) {
            foreach ($request->clear_images as $mediaId) {
                $media = $post->media()->find($mediaId);
                if ($media) {
                    $media->delete();
                }
            }
        }

        $post->update([
            'content' => $request->captions,
        ]);

        if ($request->hasFile('community_post')) {
            foreach ($request->file('community_post') as $file) {
                $post->addMedia($file)->toMediaCollection('community_post');
            }
        }
        return response()->json([
            'success' => true,
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
                ],
                'formatted_created_at' => $post->formatted_created_at,
                'comments_count' =>  $post->comments()->count(),
                'likes_count' =>  $post->likes()->count(),
                'liked' => $post->likes()->where('user_id', $user->id)->exists(),
                'media' => $post->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'original_url' => $media->getUrl(),
                    ];
                })
            ]
        ]);
    }

    public function deleteCommunityPost(Request $request)
    {
        $post = CommunityPost::find($request->id);

        if ($post->hasMedia('community_post')) {
            $post->clearMediaCollection('community_post');
        }
        
        $post->delete();

        // delete all notifications related to this post
        $post->user->notifications()
        ->where('type', CommunityNotification::class)
        ->where(function($query) use ($post) {
            $query->where('data->post_id', $post->id)
                  ->orWhere('data->post_id', (string)$post->id); // Check both formats
        })
        ->delete();
    
        // Find all comments on this post to delete their notifications too
        $commentIds = CommunityComment::where('post_id', $post->id)->pluck('id')->toArray();
        
        // Delete notifications related to these comments (replies)
        if (!empty($commentIds)) {
            // Delete all notifications related to comments on this post
            DB::table('notifications')
                ->where('type', CommunityNotification::class)
                ->where(function($query) use ($commentIds) {
                    foreach ($commentIds as $commentId) {
                        $query->orWhere('data', 'like', '%"comment_id":' . $commentId . '%');
                        $query->orWhere('data', 'like', '%"comment_id":"' . $commentId . '"%');
                    }
                })
                ->delete();
        }

        return response()->json([
            'success' => true,
        ]);
    }
    
    public function likeCommunityPost(Request $request)
    {
        $user = Auth::user();
        $post = CommunityPost::findOrFail($request->post_id);
        $like = CommunityLike::where('user_id', $user->id)
            ->where('community_post_id', $post->id)
            ->first();
        if ($like) {
            $like->delete(); // Dislike

            $post->user->notifications()
                ->where('type', \App\Notifications\CommunityNotification::class)
                ->where('data->type', 'like')
                ->where('data->user_name', $user->name)
                ->where('data->post_id', $post->id)
                ->delete();
        } else {
            CommunityLike::create([
                'user_id' => $user->id,
                'community_post_id' => $post->id,
            ]);
            if ($post->user_id !== $user->id) {
                $post->user->notify(new CommunityNotification('like', [
                    'user' => $user,
                    'post' => $post,
                    // 'route' => route('member.community'),
                ]));
            }
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function createComment(Request $request)
    {
        // dd($request->all());
        // Validate the request
        $request->validate([
            'comment' => 'required',
        ]);
        $user = Auth::user();
        $post = CommunityPost::findOrFail($request->post_id);

        $comment = CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $request->parent_id,
            'content' => $request->comment,
        ]);

        
        if ($comment->parent_id) {//notify when reply comment
            $parentComment = CommunityComment::find($comment->parent_id);
            if ($parentComment && $parentComment->user_id !== $user->id) {
                $parentComment->user->notify(new CommunityNotification('reply', [
                    'user' => $user,
                    'post' => $post,
                    'comment' => $comment,
                ]));
            }
        } else { //notify when comment
            if ($post->user_id !== $user->id) {
                $post->user->notify(new CommunityNotification('comment', [
                    'user' => $user,
                    'post' => $post,
                    'comment' => $comment,
                ]));
            }
        }
        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'parent_id' => $comment->parent_id,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
                ],
                'formatted_created_at' => $comment->formatted_created_at,
            ]
        ]);
    }

    private function isReplyOf($reply, $parent, $allComments)
    {
        while ($reply->parent_id) {
            if ($reply->parent_id === $parent->id) {
                return true;
            }
            $reply = $allComments->firstWhere('id', $reply->parent_id);
        }
        return false;
    }
    public function getComments($postId)
    {
        // sleep(5);
        $allComments = CommunityComment::where('post_id', $postId)
            ->with(['user:id,name'])
            // ->orderBy('created_at', 'desc')
            ->get();
        
        $allComments = $allComments->map(function ($comment) {
            if ($comment->user) {
                $comment->user->profile_photo = $comment->user->getFirstMediaUrl('profile_photo');
            }
            return $comment;
        });

        $comments = $allComments->whereNull('parent_id')
            ->sortByDesc('created_at')
            ->values();

        foreach ($comments as $comment) {
            $comment->all_replies = $allComments->filter(function ($reply) use ($comment, $allComments) {
                return $this->isReplyOf($reply, $comment, $allComments);
            })->values();
        }

        return response()->json([
            'comments' => $comments,
        ]);
    }

    public function updateComment(Request $request){
        $request->validate([
            'id' => 'required|exists:community_comments,id',
            'content' => 'required', 
        ]);

        $comment = CommunityComment::findOrFail($request->id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $comment->content = $request->content;
        $comment->save();
        
        return response()->json([
            'success' => true,
        ]);
    }

    public function deleteComment(Request $request)
    {
        $comment = CommunityComment::findOrFail($request->id);
        
        // Get post ID to get updated count later
        $postId = $comment->post_id;
        
        // Delete all replies using a while loop approach
        $commentsToCheck = [$comment->id];
        $allCommentsToDelete = [$comment->id];
        
        while (!empty($commentsToCheck)) {
            $currentId = array_shift($commentsToCheck);
            
            // Find all direct replies to this comment
            $replies = CommunityComment::where('parent_id', $currentId)->pluck('id')->toArray();
            
            // Add these replies to both arrays
            $allCommentsToDelete = array_merge($allCommentsToDelete, $replies);
            $commentsToCheck = array_merge($commentsToCheck, $replies);
        }

        // Delete all comments in a single query
        CommunityComment::whereIn('id', $allCommentsToDelete)->delete();
        
        // Get new comment count for the post
        $newCount = CommunityComment::where('post_id', $postId)->count();
        
        return response()->json([
            'success' => true,
            'deletedCount' => count($allCommentsToDelete),
            'newCommentsCount' => $newCount,
            'commentId' => $comment->id
        ]);
    }

    public function getTrendingPosts(Request $request)
    {
        $posts = CommunityPost::with([
            'user:id,name',
            'media'
        ])
            ->withCount(['likes', 'comments'])
            ->orderByDesc('likes_count')
            ->limit(5)
            ->get()
            ->map(function ($post) {
            // Add profile photo URL to each post's user
            if ($post->user) {
                $post->user->profile_photo = $post->user->getFirstMediaUrl('profile_photo');
            }
            return $post;
        });
       
        return response()->json($posts);
    }

    public function getHashtags(Request $request)
    {
        $q = $request->input('q');
        $hashtags = Hashtag::where('name', 'like', "%{$q}%")
            ->limit(10)
            ->pluck('name');
        return response()->json($hashtags);
    }

    public function getPopularTags(Request $request)
    {
        $topTags = CommunityPostHashtag::select('hashtag_id', DB::raw('COUNT(*) as total'))
            ->groupBy('hashtag_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $hashtags = Hashtag::whereIn('id', $topTags->pluck('hashtag_id'))->get()->keyBy('id');

        $result = $topTags->map(function ($tag) use ($hashtags) {
            if (!isset($hashtags[$tag->hashtag_id])) {
                return null; // skip if hashtag not found
            }
            return [
                'name' => $hashtags[$tag->hashtag_id]->name,
                'count' => $tag->total,
            ];
        })->filter()->values();

        return response()->json($result);
    }

    public function getActivityFeed()
    {
        $notifications = auth()->user()
            ->notifications() // You can also use ->unreadNotifications()
            ->latest() // Sort by most recent
            ->limit(12)  // Limit to 12
            ->get();

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    public function markFeedAsRead(Request $request)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($request->id);
        
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }
}