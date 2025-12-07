<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChannelPost extends Model
{
    protected $fillable = [
        'channel_id',
        'user_id',
        'content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'channel_post_view')->using(ChannelPostView::class);
    }
}
