<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Channels') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">

                <div class="flex h-[400px] border border-gray-300">

                    <!-- CHANNEL LIST -->
                    <div class="w-[200px] border-r border-gray-300 overflow-y-auto">
                        <h4 class="m-1.5 font-semibold">Channels</h4>

                        @foreach($channels as $channel)
                            <div
                                wire:click="selectChannel({{ $channel->id }})"
                                class="p-2 cursor-pointer border-b border-gray-200 hover:bg-gray-100"
                            >
                                # {{ $channel->name }}
                            </div>
                        @endforeach

                        <button wire:click="$set('showCreateChannelModal', true)"
                                class="mt-2 bg-indigo-600 text-white px-2 py-1 rounded text-xs block w-full">
                            + Create Channel
                        </button>

                    </div>

                    <!-- POSTS FEED -->
                    <div class="flex flex-col flex-1">

                        <div id="post-box" class="flex-1 p-3 overflow-y-auto space-y-3">
                            @if($selectedChannel)

                                <h3 class="font-semibold text-lg text-gray-800">
                                    # {{ $selectedChannel->name }}
                                </h3>

                                @foreach($channelPosts as $post)
                                    <div class="flex">
                                        <div
                                            class="max-w-[90%] bg-gray-200 text-gray-900 px-4 py-2 rounded-xl rounded-bl-none">
                                            <div class="text-xs opacity-80 mb-1">
                                                {{ $post->user->name }}
                                            </div>

                                            <div>{{ $post->content }}</div>

                                            <div class="text-[11px] opacity-60 mt-1 flex justify-between">
                                                <span>{{ $post->created_at->format('H:i') }}</span>
                                                <span>👁 {{ $post->views }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            @else
                                <p>Select a channel.</p>
                            @endif
                        </div>

                        <!-- INPUT BAR (only for creator) -->
                        @if($selectedChannel && auth()->id() === $selectedChannel->creator_id)
                            <div class="p-3 border-t border-gray-300">
                                <input type="text"
                                       wire:model="newPost"
                                       wire:keydown.enter="createPost"
                                       class="w-4/5 border border-gray-300 rounded px-3 py-2"
                                       placeholder="Write a post..."
                                >
                                <button wire:click="createPost"
                                        class="ml-2 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                                    Post
                                </button>
                            </div>
                        @endif

                    </div>

                </div>

            </div>
        </div>
    </div>

    @if($showCreateChannelModal)
        <div class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
            <div class="bg-white p-5 rounded-lg w-80 shadow-lg">
                <h3 class="text-lg font-semibold mb-3">Create Channel</h3>

                <input type="text"
                       wire:model="newChannelName"
                       class="w-full border rounded px-3 py-2 mb-3"
                       placeholder="Channel Name">

                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('showCreateChannelModal', false)"
                            class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">
                        Cancel
                    </button>

                    <button wire:click="createChannel"
                            class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Create
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function scrollChannelDown() {
        const box = document.getElementById("post-box");
        if (!box) return;
        box.scrollTop = box.scrollHeight;
    }

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('scrollPostsDown', scrollChannelDown);
    });

</script>
<script>
    document.addEventListener('livewire:initialized', () => {

        let activeChannel = null;

        Livewire.on('channelSelected', channelId => {

            activeChannel = channelId;

            window.Echo.private(`channel.${channelId}`)
                .listen('.view-added', () => {
                    Livewire.dispatch('refreshViewCount');
                });
        });

        Livewire.on('scrollPostsDown', () => {
            const box = document.getElementById("post-box");
            if (box) box.scrollTop = box.scrollHeight;
        });
    });
</script>


