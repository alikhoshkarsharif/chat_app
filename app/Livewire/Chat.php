<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public Collection $contacts;

    public ?User $selectedContact = null;
    public ?User $auth = null;

    public Collection $messages;

    public $newMessage = "";

    public function mount(): void
    {
        $this->contacts = User::query()->whereNot('id',Auth::id())->get();
        $this->auth = Auth::user();
    }

    public function selectContact($id): void
    {
        $this->selectedContact = User::query()->findOrFail($id);
        $this->messages = ChatMessage::query()->where(function(Builder $query) {
            $query->where('sender_id', $this->selectedContact->id)
                ->where('receiver_id', $this->auth->id);
        })->orWhere(function(Builder $query) {
            $query->where('sender_id', $this->auth->id)
                ->where('receiver_id', $this->selectedContact->id);
        })->get();
        $this->dispatch('scrollDown');
    }

    public function sendMessage(): void
    {
        if (!$this->selectedContact || trim($this->newMessage) == '') return;

        $newChat = ChatMessage::query()->create([
            'sender_id' => $this->auth->id,
            'receiver_id' => $this->selectedContact->id,
            'message' => $this->newMessage,
        ]);
        $this->messages->push($newChat);
        $this->newMessage = "";

        broadcast(new MessageSent($newChat));

        $this->dispatch('scrollDown');
    }

    protected function getListeners(): array
    {
        $authId = $this->auth->id;
        return [
            "echo-private:chat.{$authId},MessageSent" => 'receiveMessage',
        ];
    }

    public function receiveMessage(array $payload): void
    {
         if($payload['sender_id'] === $this->selectedContact->id) {
             $chatMessage = ChatMessage::query()->find($payload['id']);
             $this->messages->push($chatMessage);
             $this->dispatch('scrollDown');
         }
    }

    public function updatedNewMessage(): void
    {
        $this->dispatch('userTyping',userId: $this->auth->id,userName: $this->auth->name,selectedContactId: $this->selectedContact->id);
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        return view('livewire.chat');
    }
}
