<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Response;

class ChatController extends Controller
{
    public function show(string $chat_token): Response
    {
        $employee = Employee::where('chat_token', $chat_token)
            ->where('is_active', true)
            ->firstOrFail();

        $employee->update(['last_seen_at' => now()]);

        return response()
            ->view('chat.show', ['employee' => $employee])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
