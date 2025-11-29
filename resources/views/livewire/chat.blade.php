<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Chat') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">

                <div class="flex h-[400px] border border-gray-300">

                    <!-- CONTACT LIST -->
                    <div class="w-[200px] border-r border-gray-300 overflow-y-auto">
                        <h4 class="m-1.5 font-semibold">Contacts</h4>

                        @foreach($contacts as $contact)
                            <div
                                wire:click="selectContact({{ $contact['id'] }})"
                                class="p-2 cursor-pointer border-b border-gray-200 hover:bg-gray-100"
                            >
                                {{ $contact['name'] }}
                            </div>
                        @endforeach
                    </div>

                    <!-- CHAT BOX -->
                    <div class="flex flex-col flex-1">

                        <!-- Messages -->
                        <div id="messages-box" class="flex-1 p-3 overflow-y-auto">
                            @if($selectedContact)

                                @foreach($this->messagesGroupedByDate as $date => $dayMessages)

                                    <!-- Sticky Date Header -->
                                    <div class="sticky top-0 z-10 mb-2">
                                        <div
                                            class="mx-auto w-max bg-gray-300 text-gray-800 text-xs px-3 py-1 rounded-full shadow">
                                            {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                                        </div>
                                    </div>

                                    @foreach($dayMessages as $message)
                                        @php
                                            $isMe = $message->sender_id === auth()->id();
                                        @endphp

                                        <div class="mb-3 flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                                            <div class="max-w-[70%] px-4 py-2 rounded-xl
                    {{ $isMe
                        ? 'bg-indigo-600 text-white rounded-br-none'
                        : 'bg-gray-200 text-gray-900 rounded-bl-none'
                    }}">

                                                <div class="text-xs opacity-80 mb-1">
                                                    {{ $isMe ? 'You' : $message->sender->name }}
                                                </div>

                                                <!-- File + Message Content -->
                                                <div class="space-y-1">

                                                    @if ($message->file_path)
                                                        <a href="{{ asset('storage/' . $message->file_path) }}"
                                                           target="_blank"
                                                           class="font-semibold underline inline-block mb-1
              {{ $isMe ? 'text-indigo-200' : 'text-blue-600' }}"
                                                        >
                                                            @if(str_contains($message->file_type, 'image'))
                                                                <img src="{{ asset('storage/' . $message->file_path) }}"
                                                                     class="max-w-[150px] rounded-lg">
                                                            @else
                                                                📎 Download File
                                                                ({{ strtoupper(explode('/', $message->file_type)[1] ?? '') }}
                                                                )
                                                            @endif
                                                        </a>
                                                    @endif


                                                    @if ($message->message)
                                                        <div>{{ $message->message }}</div>
                                                    @endif

                                                </div>

                                                <!-- Time -->
                                                <div class="text-[11px] opacity-60 mt-1 text-right">
                                                    {{ $message->created_at->format('H:i') }}
                                                </div>
                                            </div>
                                        </div>

                                    @endforeach

                                @endforeach

                            @else
                                <p>Select a contact to start chatting.</p>
                            @endif
                        </div>

                        <!-- Typing Indicator -->
                        <div id="typing-indicator" class="text-xs text-gray-600 px-2 py-1"></div>

                        <!-- Message Input -->
                        @if($selectedContact)
                            <div class="p-3 border-t border-gray-300">
                                <input type="file" wire:model="file" class="mb-2 text-sm">

                                <input type="text"
                                       wire:model.live="newMessage"
                                       wire:keydown.enter="sendMessage"
                                       class="w-4/5 border border-gray-300 rounded px-3 py-2"
                                       placeholder="Type a message..."
                                >

                                <button wire:click="sendMessage"
                                        class="ml-2 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                                    Send
                                </button>

                                @error('file')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    let typingIndicatorTimeout, scrollToBottomTimeout;

    function scrollToBottom() {
        const box = document.getElementById("messages-box");
        if (!box) return;
        box.scrollTop = box.scrollHeight;
    }

    document.addEventListener('livewire:initialized', function () {

        Livewire.on('userTyping', (event) =>
            window.Echo.private(`chat.${event.selectedContactId}`)
                .whisper('typing', {
                    userId: event.userId,
                    userName: event.userName
                })
        );

        window.Echo.private(`chat.{{$auth->id}}`).listenForWhisper('typing', (event) => {
            const typingIndicator = document.getElementById('typing-indicator');
            typingIndicator.innerHTML = `${event.userName} is typing ...`;

            clearTimeout(typingIndicatorTimeout);
            typingIndicatorTimeout = setTimeout(() => typingIndicator.innerHTML = '', 1500);
        });

        Livewire.on('scrollDown', () => {
            clearTimeout(scrollToBottomTimeout);
            scrollToBottomTimeout = setTimeout(scrollToBottom, 50);
        });
    });
</script>
