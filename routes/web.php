<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use App\Models\Employee;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/employees', function () {
        return view('employees.index');
    })->name('employees.index');

    Route::get('/employees/{employee}', function (Employee $employee) {
        return view('employees.show', ['employee' => $employee]);
    })->name('employees.show');

    Route::get('/backlog', function () {
        return view('backlog.index');
    })->name('backlog.index');
});

Route::get('/c/{chat_token}', [ChatController::class, 'show'])->name('chat.show');

require __DIR__.'/auth.php';
