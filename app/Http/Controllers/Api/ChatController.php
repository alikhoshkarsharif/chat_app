<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function contacts()
    {
        return User::where('id', '!=', Auth::id())->get();
    }

    public function messages(User $receiver)
    {
        return ChatMessage::where(function ($q) use ($receiver) {
            $q->where('sender_id', Auth::id())
                ->where('receiver_id', $receiver->id);
        })->orWhere(function ($q) use ($receiver) {
            $q->where('sender_id', $receiver->id)
                ->where('receiver_id', Auth::id());
        })->orderBy('created_at')->get();
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:10240'
        ]);

        $file = $request->file('file');
        $filePath = $file ? $file->store('chat_files', 'public') : null;

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'file_path' => $filePath,
            'file_type' => $file ? $file->getMimeType() : null,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }
}
