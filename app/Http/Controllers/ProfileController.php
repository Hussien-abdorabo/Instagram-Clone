<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    public function show(Profile $profile)
    {

        $auth =  auth()->user();

        if(!$auth || $auth->id !== $profile->user_id){
            return response()->json('Unauthorized', 401);
        }
        $view = Profile::find($profile->user_id);
//        dd($view);
        return response()->json($view);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        $user = auth()->user();

        if (!$user || $user->id !== $profile->user_id) {
            return response()->json('Unauthorized', 401);
        }

        $validated = Validator::make($request->all(), [
            "username" => "nullable|unique:users,username,{$user->id}|string|max:255|min:6",
            "bio" => "nullable|string|max:255|min:6",
            "profile_pic" => "nullable|mimes:jpeg,png,jpg|max:2048",
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 400);
        }


        if ($request->filled('username')) {
            $user->username = $request->username;
            $user->save();
        }


        if ($request->hasFile('profile_pic')) {
            $imagePath = $request->file('profile_pic')->store('profile_pics', 'public');
            $profile->profile_pic = $imagePath;
        }

        if ($request->filled('bio')) {
            $profile->bio = $request->bio;
        }

        $profile->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile,
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
