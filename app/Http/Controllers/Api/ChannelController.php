<?php

namespace App\Http\Controllers\Api;

use App\Enums\ChannelUserRole;
use App\Events\ChannelViewed;
use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChannelController extends Controller
{
    public function index()
    {
        return response()->json([
            'channels' => Auth::user()->channels
        ]);
    }

    public function show($id)
    {
        $channel = Channel::with(['posts' => function ($query) {
            $query->withCount('views');
        }])->findOrFail($id);

        foreach ($channel->posts as $post) {
            $post->views()->syncWithoutDetaching(Auth::id());
        }

        broadcast(new ChannelViewed($id))->toOthers();

        return response()->json([
            'channel' => $channel,
            'posts' => $channel->posts
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $channel = Channel::create([
            'name' => $request->name,
            'creator_id' => Auth::id()
        ]);

        Auth::user()->channels()->attach($channel->id, [
            'role' => ChannelUserRole::OWNER
        ]);

        return response()->json([
            'message' => 'Channel created successfully',
            'channel' => $channel
        ], 201);
    }

    public function createPost(Request $request, $channelId)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $channel = Channel::findOrFail($channelId);

        if (Auth::id() !== $channel->creator_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post = ChannelPost::create([
            'channel_id' => $channel->id,
            'user_id' => Auth::id(),
            'content' => $request->content
        ]);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post
        ], 201);
    }

    public function refreshViewCount($channelId)
    {
        $channel = Channel::with(['posts' => fn($q) => $q->withCount('views')])
            ->findOrFail($channelId);

        return response()->json([
            'posts' => $channel->posts
        ]);
    }
}
