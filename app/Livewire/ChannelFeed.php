<?php

namespace App\Livewire;

use App\Enums\ChannelUserRole;
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
    public $showAddMemberModal = false;
    public $emailToAdd;
    public $channelMembers = [];

    protected $listeners = [
        'refreshViewCount' => 'updateViewCount',
    ];

    public function mount(): void
    {
        $this->channels = Auth::user()->channels;
    }

    public function selectChannel($id): void
    {
        $this->selectedChannel = Channel::query()->findOrFail($id);
        $this->channelPosts = $this->selectedChannel->posts()->withCount('views')->get();
        $this->channelMembers = $this->selectedChannel->users()->get();

        foreach ($this->channelPosts as $post) {
            $post->views()->syncWithoutDetaching(Auth::id());
        }

        broadcast(new ChannelViewed($id))->toOthers();

        $this->dispatch('channelSelected', $id);
        $this->dispatch('scrollPostsDown');
    }

    public function createChannel(): void
    {
        $channel = Channel::query()->create([
            'name' => $this->newChannelName,
            'creator_id' => auth()->id()
        ]);

        Auth::user()->channels()->attach($channel->id, [
            'role' => ChannelUserRole::OWNER,
        ]);

        $this->channels = Auth::user()->channels; // refresh for all
        $this->newChannelName = "";
        $this->showCreateChannelModal = false;
    }

    public function addMember(): void
    {
        $this->resetErrorBag();
        if (Auth::id() !== $this->selectedChannel->creator_id || empty($this->emailToAdd)) {
            return;
        }

        $user = \App\Models\User::where('email', $this->emailToAdd)->first();

        if (!$user) {
            $this->addError('emailToAdd', 'User not found.');
            return;
        }

        if ($this->selectedChannel->users()->where('user_id', $user->id)->exists()) {
            $this->addError('emailToAdd', 'User already in channel.');
            return;
        }

        $this->selectedChannel->users()->attach($user->id, [
            'role' => ChannelUserRole::MEMBER,
        ]);

        $this->emailToAdd = "";
        $this->showAddMemberModal = false;
        $this->channelMembers = $this->selectedChannel->users()->get();
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
            $this->channelPosts = $this->selectedChannel->posts()->withCount('views')->get();
        }
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        return view('livewire.channel-feed');
    }
}
