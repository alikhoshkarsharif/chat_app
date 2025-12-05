<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    protected $fillable = [
        'creator_id',
        'name',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'creator_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ChannelPost::class);
    }
}
