<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Chat') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div style="display:flex; height:400px; border:1px solid #ccc;">

                    <!-- CONTACTS LIST -->
                    <div style="width:200px; border-right:1px solid #ccc; overflow-y:auto;">
                        <h4 style="margin:5px;">Contacts</h4>

                        @foreach($contacts as $contact)
                            <div style="padding:8px; cursor:pointer; border-bottom:1px solid #eee;"
                                 wire:click="selectContact({{ $contact['id'] }})">
                                {{ $contact['name'] }}
                            </div>
                        @endforeach
                    </div>

                    <!-- CHAT BOX -->
                    <div style="flex:1; display:flex; flex-direction:column;">

                        <!-- Messages -->
                        <div id="messages-box" style="flex:1; padding:10px; overflow-y:auto;">
                            @if($selectedContact)
                                @foreach($messages as $message)

                                    @php
                                        $isMe = $message->sender_id === auth()->id();
                                    @endphp

                                    <div style="margin-bottom:12px; display:flex; {{ $isMe ? 'justify-content:flex-end;' : 'justify-content:flex-start;' }}">

                                        <div style="
                    max-width:70%;
                    padding:10px 14px;
                    border-radius:12px;
                    {{ $isMe
                        ? 'background:#4f46e5; color:white; border-bottom-right-radius:0;'
                        : 'background:#e5e7eb; color:#111; border-bottom-left-radius:0;'
                    }}
                ">
                                            <div style="font-size:12px; opacity:0.8; margin-bottom:3px;">
                                                {{ $isMe ? 'You' : $message->sender->name }}
                                            </div>

                                            <div>
                                                {{ $message->message }}
                                            </div>
                                            <!-- Timestamp -->
                                            <div style="font-size:11px; opacity:0.6; margin-top:6px; text-align:right;">
                                                {{ $message->created_at->format('H:i') }}
                                            </div>
                                        </div>

                                    </div>

                                @endforeach
                            @else
                                <p>Select a contact to start chatting.</p>
                            @endif
                        </div>

                        <div id="typing-indicator" style="font-size:12px; color:#666; padding:4px 10px;">
                        </div>
                        <!-- Message Input -->
                        @if($selectedContact)
                            <div style="padding:10px; border-top:1px solid #ccc;">
                                <input type="text" wire:model.live="newMessage"
                                       wire:keydown.enter="sendMessage"
                                       style="width:80%;" placeholder="Type a message...">
                                <button wire:click="sendMessage">Send</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let typingIndicatorTimeout,scrollToBottomTimeout;
    function scrollToBottom() {
        const box = document.getElementById("messages-box");
        if (!box) return;
        box.scrollTop = box.scrollHeight;
    }
    document.addEventListener('livewire:initialized',function() {
        Livewire.on('userTyping',(event) => window.Echo.private(`chat.${event.selectedContactId}`).whisper('typing',{
            userId: event.userId,
            userName: event.userName
        }))

        window.Echo.private(`chat.{{$auth->id}}`).listenForWhisper('typing',(event) => {
            const typingIndicator = document.getElementById('typing-indicator')
            typingIndicator.innerHTML = `${event.userName} is typing ...`

            if(typingIndicatorTimeout) {
                clearTimeout(typingIndicatorTimeout);
            }
            typingIndicatorTimeout = setTimeout(() => {
                typingIndicator.innerHTML = ''
            },1500)
        })

        Livewire.on('scrollDown', () => {
            if(scrollToBottomTimeout) {
                clearTimeout(scrollToBottomTimeout);
            }
            scrollToBottomTimeout = setTimeout(scrollToBottom, 50);
        });
    });
</script>
