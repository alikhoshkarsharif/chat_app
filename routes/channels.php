<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{receiver_id}', function ($user, $receiver_id) {
    return (int)$user->id === (int)$receiver_id;
});

Broadcast::channel('channel.{channelId}', function ($user, $channelId) {
    return !!$user; // OR add channel access rules here
});
