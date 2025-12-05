<?php

namespace App\Livewire;

use App\Events\ChannelViewed;
use App\Models\Channel;
use App\Models\ChannelPost;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChannelFeed extends Component
{
    public $channels;
    public $selectedChannel;
    public $channelPosts;
    public $newPost;
    public $showCreateChannelModal = false;
    public $newChannelName;

    protected $listeners = [
        'refreshViewCount' => 'updateViewCount',
    ];

    public function mount(): void
    {
        $this->channels = Channel::all();
    }

    public function selectChannel($id): void
    {
        $this->selectedChannel = Channel::query()->findOrFail($id);
        $this->channelPosts = $this->selectedChannel->posts()->get();

        foreach ($this->channelPosts as $post) {
            $post->increment('views');
        }

        broadcast(new ChannelViewed($id))->toOthers();

        $this->dispatch('channelSelected', $id);
        $this->dispatch('scrollPostsDown');
    }

    public function createChannel(): void
    {
        Channel::query()->create([
            'name' => $this->newChannelName,
            'creator_id' => auth()->id()
        ]);

        $this->channels = \App\Models\Channel::all(); // refresh for all
        $this->newChannelName = "";
        $this->showCreateChannelModal = false;
    }


    public function createPost(): void
    {
        if (Auth::id() !== $this->selectedChannel->creator_id) return;

        ChannelPost::query()->create([
            'channel_id' => $this->selectedChannel->id,
            'user_id' => Auth::id(),
            'content' => $this->newPost,
        ]);

        $this->newPost = "";
        $this->selectChannel($this->selectedChannel->id);

        $this->dispatch('scrollPostsDown');
    }

    public function updateViewCount(): void
    {
        if ($this->selectedChannel) {
            $this->channelPosts = $this->selectedChannel->posts()->latest()->get();
        }
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        return view('livewire.channel-feed');
    }
}
