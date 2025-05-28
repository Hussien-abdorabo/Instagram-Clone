<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use App\Models\User;



class UserController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->input('query');

        if (!$search) {
            return response()->json([], 404 );
        }

        $users = Profile::where('name', 'like', "%{$search}%")
            ->orWhere('username', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'username','name' ,'profile_pic']);
        if($users->isEmpty()){
            return response()->json(['message'=>'user not found'], 404 );
        }
        return response()->json($users);
    }
}
