<?php

namespace App\Http\Controllers;


use App\Events\NewNotification;
use Illuminate\Http\Request;
use App\Models\User;

class FollowController extends Controller
{
    public function Follow(Request $request, User $user)
    {
        $authUser = auth()->user();
//        dd($authUser);
        if(!$authUser){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if ($authUser->id === $user->id) {
            return response()->json(['error' => 'You cannot follow yourself'], 422);
        }

        // استخدم exists() مع الجدول المحدد users.id
        $isFollowing = $authUser->followings()->where('users.id', $user->id)->exists();

        if ($isFollowing) {
            $authUser->followings()->detach($user->id);
            return response()->json(['following' => false]);
        } else {
            $authUser->followings()->attach($user->id);
            broadcast(new NewNotification($user->id,[
                'message'=> $user->username.' followed you',
                'type'=> 'follow',
                'user_id'=> $user->id,
            ]));
            \Log::info("Broadcasting Follow you {$user->id} by user {$authUser->id}");
            return response()->json(['following' => true]);
        }
    }

    public function MyFollowers(User $user)
    {
        $authUser = auth()->user();
        if(!$authUser){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $followers = $user->followers()->get(['users.id', 'users.username']);
        if ($followers->count() === 0) {
            return response()->json(['error' => 'No followers'], 404);
        }else {
            return response()->json(['followers' => $followers]);
        }
    }

    public function MyFollowing()
    {
        $authUser = auth()->user();
        if(!$authUser){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $following = $authUser->followings()->get(['users.id', 'users.username']);

        if ($following->count() === 0) {
            return response()->json(['message'=>'you are not following anyone yet']);
        }else {
            return response()->json(['following' => $following]);
        }

    }
}
