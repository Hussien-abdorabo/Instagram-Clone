<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    // add comment to post
    public function addComment(Request $request, Post $post){
        $user = auth()->user();
        if(!$user){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $validated = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);
        if($validated->fails()){
            return response()->json(['error' => $validated->errors()], 400);
        }
        $comment = Comment::create([
            'user_id'=>$user->id,
            'post_id'=>$post->id,
            'comment'=>$request->comment,
        ]);
        $post->increment('comments_count');
        $post->save();
        return response()->json(['comment' => $comment], 201);
    }

    //update comment
    public function getComments(Post $post){
        $user = auth()->user();
        if(!$user){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $comment = Comment::all()
            ->load('user')
            ->where('post_id',$post->id);
        return response()->json(['comments' => $comment], 200);
    }

    public function deleteComment(Comment $comment)
    {
        $user = auth()->user();

        if (!$user || $user->id != $comment->user_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Load the related post before deleting
        $post = $comment->post;

        $comment->delete();

        if ($post && $post->comments_count > 0) {
            $post->decrement('comments_count');
        }
        $post->save();

        return response()->json(['message' => 'Comment deleted'], 200);
    }


    public function updateComment(Request $request, Comment $comment){
        $user = auth()->user();
        if(!$user || $user->id !== $comment->user_id){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $validated = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);
        if($validated->fails()){
            return response()->json(['error' => $validated->errors()], 400);
        }
        $comment->update([
            'comment'=>$request->comment,
        ]);
        return response()->json(['comment' => $comment], status: 200);
    }
}
