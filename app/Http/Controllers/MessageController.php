<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\User;

class MessageController
{
    public function send(Request $request, Conversation $conversation)
    {
        $user = auth()->user();
        if (!in_array(auth()->id(), [$conversation->user_one_id, $conversation->user_two_id])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $validated = $request->validate([
            'content' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:2048',
        ]);
        if($user->id ==$request->receiver_id ){
            return  response()->json(['error' => 'you can\'t send message to your self'], 401);
        }
        $receiverId = $conversation->user_one_id == $user->id ? $conversation->user_two_id : $conversation->user_one_id;

        $message = Message::create(array(
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'content' => $validated['content'],
            'attachment' => $validated['attachment'],
        ));
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['message' => $message], 201);
    }

    public function fetch(Conversation $conversation)
    {
        $userId = auth()->id();

        if (!in_array($userId, [$conversation->user_one_id, $conversation->user_two_id])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()->with('sender')->orderBy('created_at')->get();

        return response()->json($messages);
    }



    public function markAsRead($conversationId)
    {
        $userId = auth()->id();

        // Update messages where user is receiver and message is unread
        Message::where('conversation_id', $conversationId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Messages marked as read']);
    }

}
