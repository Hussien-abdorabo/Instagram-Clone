<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function toggleLikeToPost(Request $request ,Post $post)
    {

        $result = DB::transaction(function () use ($request, $post){
            $user = auth()->user();
            if(!$user){
                return response('Unauthorized',401);
            }
            $like =$post->likes()->where('user_id',$user->id)->first();

            if($like){
                $like->delete();
                return ['Liked'=> false];
            }
            if($post->likes_count >0){
                $post->decrement('likes_count');
            }
            $post->likes()->create([
                'user_id'=>$user->id
            ]);
            $post->increment('likes_count');
            broadcast(new NewNotification($post->user_id,[
                'message'=> $user->username . ' liked your post',
                'type'=> 'like',
                'post_id'=>$post->post_id
            ]));
            return ['Liked'=> true];

        });
        return response($result);
    }

    // Add like to Comment

    public function toggleLikeToComment(Request $request, Comment $comment)
    {
        $result = DB::transaction(function () use ($request, $comment){
            $user  = auth()->user();
            if(!$user){
                return response('Unauthorized',401);
            }
            $like =$comment->likes()->where('user_id',$user->id)->first();

            if($like){
                $like->delete();
                return ['Liked'=> false];
            }
            if($comment->likes_count >0){
                $comment->decrement('likes_count');
            }
            $comment->likes()->create([
                'user_id'=>$user->id
            ]);
            $comment->increment('likes_count');
            broadcast(new NewNotification($comment->user_id, [
                'message' => $user->username . ' liked your post.',
                'type' => 'like',
                'comment_id' => $comment->id,
            ]));
            \Log::info("Broadcasting CommentAdded on post {$comment->id} by user {$user->id}");
            return ['Liked'=> true];

        });

        return response($result);
    }
}
