<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user){
            return response()->json('unauthorized', 401);
        }
        $posts = Post::with(['user'])->latest()->paginate(10);

        return response()->json(['view'=>$posts],status: 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user){
            return response()->json('unauthorized', 401);
        }
        $validated = Validator::make($request->all(), [
            'post_image' =>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'caption' => 'nullable|string|max:255|min:5',
        ]);
        if ($validated->fails()) {
            return response()->json($validated->errors(), 400);
        }
        $post_image = $request->file('post_image')->store('posts','public');
        $post = Post::create([
            'user_id'=>$user->id,
            'profile_id'=>$user->id,
            'post_image'=>$post_image,
            'caption'=>$request->caption,
        ]);
        return response()->json(['message'=>'Post created','posts'=>$post],status: 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $user = auth()->user();
        if (!$user || $user->id !== $post->user_id){
            return response()->json('unauthorized', 401);
        }
        $post = Post::where('id',$post->id)->first();
        return response()->json(['view'=>$post],status: 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $user = auth()->user();
        if (!$user || $user->id !== $post->user_id){
            return response()->json('unauthorized', 401);
        }
        $validated = Validator::make($request->all(), [
            'post_image'=>'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'caption' => 'nullable|string|max:255|min:5',
        ]);
        if ($validated->fails()) {
            return response()->json($validated->errors(), 400);
        }

        if($request->hasFile('post_image')){
            $post_image = $request->file('post_image')->store('posts','public');
            $post->post_image =  $post_image;
        }
        if($request->hasAny('caption')){
            $post->caption =  $request->caption;
        }
        $post->save();
        return response()->json(['message'=>'Post updated','posts'=>$post],status: 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $user = auth()->user();
        if (!$user || $user->id !== $post->user_id){
            return response()->json('unauthorized', 401);
        }
        $post->delete();
        return response()->json(['message'=>'Post deleted'],status: 200);
    }
}
