<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ForumController extends Controller
{
    public function index()
    {
        return Inertia::render('Member/Forum/MemberForum');
    }

    public function createPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'display_avatar' => ['required'],
            'display_name' => ['required'],
        ])->setAttributeNames([
            'display_avatar' => trans('public.display_avatar'),
            'display_name' => trans('public.display_name'),
        ]);
        $validator->validate();

        try {
            $post = ForumPost::create([
                'user_id' => \Auth::id(),
                'display_name' => $request->display_name,
                'subject' => $request->subject,
                'message' => $request->message,
            ]);

            if ($request->display_avatar) {
                $path = public_path($request->display_avatar);
                $post->copyMedia($path)->toMediaCollection('display_avatar');
            }

            if ($request->attachment) {
                $post->addMedia($request->attachment)->toMediaCollection('post_attachment');
            }

            // Redirect with success message
            return redirect()->back()->with('toast', [
                "title" => trans('public.toast_create_post_success'),
                "type" => "success"
            ]);
        } catch (\Exception $e) {
            // Log the exception and show a generic error message
            Log::error('Error updating asset master: '.$e->getMessage());

            return redirect()->back()->with('toast', [
                'title' => 'There was an error creating the post.',
                'type' => 'error'
            ]);
        }
    }

    public function getPosts(Request $request)
    {
        $posts = ForumPost::with([
            'user:id,name',
            'media'
        ])
            ->latest()
            ->get()
            ->map(function ($post) {
                $post->profile_photo = $post->user->getFirstMediaUrl('profile_photo');
                $post->display_avatar = $post->getFirstMediaUrl('display_avatar');
                $post->post_attachment = $post->getFirstMediaUrl('post_attachment');
                return $post;
            });

        return response()->json($posts);
    }
}
