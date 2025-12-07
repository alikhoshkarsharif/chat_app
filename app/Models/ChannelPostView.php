<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ChannelPostView extends Pivot
{
    protected $fillable = [
        'channel_post_id',
        'user_id',
    ];
}
