<?php

namespace App\Http\Controllers\Api;


//use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Handle the registration of a new user.
     *
     * Validates incoming request data to ensure the required fields are present,
     * properly formatted, and meet business rules (e.g., unique username/email,
     * password confirmation). If validation passes, a new user record is created
     * and persisted in the database with a hashed password.
     *
     * Upon successful registration, an API token is generated using Laravel
     * Sanctum to authenticate future requests. Returns a JSON response containing
     * the user data and access token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validated = Validator::make($request->all(), [
            "username" => "required|unique:users|max:255|min:6",
            "email" => "required|unique:users|email|max:255|min:6",
            "password" => "required|min:6|max:255|confirmed",
            "password_confirmation" => "required|same:password"
        ]);
        if ($validated->fails()) {
            return response()->json($validated->errors(), 400);
        }
        $user = User::create([
            "username" => $request->username,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $profile = Profile::create([
            "user_id" => $user->id,
            'username' => $user->username,
            'bio' => $user->bio,
            'profile_image' => $user->profile_image,
        ]);
//        $profile->save();


        return response()->json(["user" => $user, "token" => $token], 201);

    }


    /**
     * Authenticate a user and issue an access token.
     *
     * This method validates the incoming login request to ensure the username and password
     * are present and correctly formatted. It then attempts to retrieve the user by the
     * provided username. If the user exists and the password matches, an API token is
     * generated using Laravel Sanctum and returned with the user data.
     *
     * Handles validation errors, incorrect credentials, and successful login responses
     * with appropriate HTTP status codes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Validation rules:
     * - Username must exist in the users table, be alphanumeric (with dashes/underscores), and within length limits.
     * - Password must be at least 6 characters and match the hashed password stored in the database.
     *
     * Responses:
     * - 400 Bad Request: if validation fails or credentials are incorrect.
     * - 201 Created: on successful login, with user info and token.
     */

    public function login(Request $request){
        $validated = Validator::make($request->all(), [
            "username" => "required|exists:users|max:255|min:6",
            "password" => "required|min:6|max:255",
        ]);
        if ($validated->fails()) {
            return response()->json($validated->errors(), 400);
        }
        $user = User::where('username',$request->username)->first();
        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json(["message" => "Wrong username or password"], 400);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(["message"=>"Login successfully",
            "user" => $user,
            "token" => $token],
            201);
    }

    public function logout(Request $request){
        $user = auth()->user();
        if (!$user) {
            return response()->json(["message" => "User not found"], 400);
        }
        $user->tokens()->delete();
        return response()->json(["message" => "Logout successfully"], 200);
    }
}
