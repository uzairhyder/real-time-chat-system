<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
// app/Http/Controllers/ChatController.php
class ChatController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())
            ->get()
            ->map(function ($user) {
                $user->unread_count = Message::where('from_user_id', $user->id)
                    ->where('to_user_id', auth()->id())
                    ->where('is_read', false)
                    ->count();
                return $user;
            });;
        return view('chat.index', compact('users'));
    }

    public function fetchMessages($userId)
    {
        $messages = Message::where(function ($query) use ($userId) {
            $query->where('from_user_id', auth()->id())->where('to_user_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('from_user_id', $userId)->where('to_user_id', auth()->id());
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
//        dd($request->all());
        $messageText = $request->message;
        $filePath = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('chat_files', 'public');

            if (empty($messageText)) {
                $messageText = '[File]';
            }
        }


        $message = Message::create([
            'from_user_id' => auth()->id(),
            'to_user_id' => $request->to_user_id,
            'message' => $messageText,
            'file_path' => $filePath,
        ]);

        return response()->json($message);
    }

    public function checkNewMessages()
    {
        $userId = auth()->id();

//        $hasNew = Message::where('to_user_id', $userId)
//            ->where('is_read', false) // assuming you track read/unread
//            ->exists();

        $messages = Message::where('to_user_id', $userId)
            ->where('is_read', false)
            ->select('from_user_id', DB::raw('count(*) as count'))
            ->groupBy('from_user_id')
            ->get();


        return response()->json($messages);
    }

    public function markAllAsRead(Request $request)
    {
        $userId = auth()->id();

        // All messages sent to the current user and still unread
        Message::where('to_user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'updated']);
    }

    public function markAsRead($userId)
    {
        Message::where('from_user_id', $userId)
            ->where('to_user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'updated']);
    }
}
