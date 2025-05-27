<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\Conversation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConversationController
{
    public function index()
    {
        $userId = auth()->id();

        // 1. Get all conversations for the user
        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['messages' => function ($q) {
                $q->latest()->limit(1); // Only latest message
            }])
            ->get();
        // When fetching conversations
        $conversation = Cache::remember("user_{$userId}_conversations", 3600, function() use ($userId) {
            return Conversation::with('messages')->latest()->whereHas('users', fn($q) => $q->where('user_id', $userId))->get();
        });

        // ✅ 2. Calculate unread counts
        $unreadCounts = DB::table('messages')
            ->select('conversation_id', DB::raw('COUNT(*) as unread'))
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->groupBy('conversation_id')
            ->pluck('unread', 'conversation_id');

        // ✅ 3. Attach unread counts to each conversation
        $conversations->map(function ($conv) use ($unreadCounts) {
            $conv->unread_count = $unreadCounts[$conv->id] ?? 0;
            return $conv;
        });

        // ✅ 4. Return response
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
