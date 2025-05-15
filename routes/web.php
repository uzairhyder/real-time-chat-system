<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/messages/{userId}', [ChatController::class, 'fetchMessages'])->name('chat.fetch');
    Route::post('/messages/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/check-new-messages', [ChatController::class, 'checkNewMessages']);
    Route::post('/mark-all-as-read', [ChatController::class, 'markAllAsRead']);
    Route::post('/mark-as-read/{userId}', [ChatController::class, 'markAsRead']);
});

require __DIR__.'/auth.php';
