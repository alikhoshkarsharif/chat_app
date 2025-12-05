<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('chat', \App\Livewire\Chat::class)->name('chat')->middleware(['auth', 'verified']);
Route::get('channels', \App\Livewire\ChannelFeed::class)
    ->middleware(['auth'])
    ->name('channels');


require __DIR__ . '/auth.php';
