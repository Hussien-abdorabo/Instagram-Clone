<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;

class ConversationController
{
    public function index()
    {
        $userId = auth()->id();

        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['userOne', 'userTwo'])
            ->latest()
            ->get();

        return response()->json($conversations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id|not_in:' . auth()->id(),
        ]);

        $userOne = auth()->id();
        $userTwo = $request->receiver_id;

        $existing = Conversation::where(function ($q) use ($userOne, $userTwo) {
            $q->where('user_one_id', $userOne)->where('user_two_id', $userTwo);
        })->orWhere(function ($q) use ($userOne, $userTwo) {
            $q->where('user_one_id', $userTwo)->where('user_two_id', $userOne);
        })->first();

        if ($existing) {
            return response()->json(['message' => 'Conversation already exists.', 'conversation' => $existing], 200);
        }

        $conversation = Conversation::create([
            'user_one_id' => $userOne,
            'user_two_id' => $userTwo,
        ]);

        return response()->json(['message' => 'Conversation created.', 'conversation' => $conversation], 201);
    }
}
