<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function PersonalizedFeed(Request $request)
    {
        $authUser = auth()->user();
        if(!$authUser){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $postID = $authUser->followings()->with('post')->get()
            ->pluck('post')->flatten()->pluck('id');
        return response()->json(['feeds'=>Post::with(['user','likes','comments'])
            ->whereIn('id', $postID)
            ->latest()
            ->paginate(10)]);
    }
}
