<?php

namespace App\Models;

use App\Enums\ChannelUserRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ChannelUser extends Pivot
{
    protected $fillable = [
        'channel_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => ChannelUserRole::class,
    ];
}
