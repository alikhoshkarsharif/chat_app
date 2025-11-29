<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Chat extends Component
{
    use WithFileUploads;

    public Collection $contacts;

    public ?User $selectedContact = null;
    public ?User $auth = null;

    public Collection $messages;

    public $newMessage = "";
    #[Validate('file|max:1024')] // 1MB Max
    public ?UploadedFile $file = null;

    public function mount(): void
    {
        $this->contacts = User::query()->whereNot('id', Auth::id())->get();
        $this->auth = Auth::user();
    }

    public function selectContact($id): void
    {
        $this->selectedContact = User::query()->findOrFail($id);
        $this->messages = ChatMessage::query()->where(function (Builder $query) {
            $query->where('sender_id', $this->selectedContact->id)
                ->where('receiver_id', $this->auth->id);
        })->orWhere(function (Builder $query) {
            $query->where('sender_id', $this->auth->id)
                ->where('receiver_id', $this->selectedContact->id);
        })->get();
        $this->dispatch('scrollDown');
    }

    public function sendMessage(): void
    {
        if (!$this->selectedContact) return;

        if (trim($this->newMessage) == '' && empty($this->file)) return;

        $fileType = null;
        $filePath = null;
        if ($this->file) {
            $fileType = $this->file->getMimeType();
            $filePath = $this->file->store('chat_files', 'public');
        }

        $newChat = ChatMessage::query()->create([
            'sender_id' => $this->auth->id,
            'receiver_id' => $this->selectedContact->id,
            'message' => $this->newMessage,
            'file_type' => $fileType,
            'file_path' => $filePath,
        ]);
        $this->messages->push($newChat);
        $this->newMessage = "";
        $this->file = null;

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
        if ($payload['sender_id'] === $this->selectedContact->id) {
            $chatMessage = ChatMessage::query()->find($payload['id']);
            $this->messages->push($chatMessage);
            $this->dispatch('scrollDown');
        }
    }

    public function updatedNewMessage(): void
    {
        $this->dispatch('userTyping', userId: $this->auth->id, userName: $this->auth->name, selectedContactId: $this->selectedContact->id);
    }

    public function getMessagesGroupedByDateProperty()
    {
        return $this->messages->groupBy(function ($msg) {
            return $msg->created_at->format('Y-m-d');
        });
    }


    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        return view('livewire.chat');
    }
}
