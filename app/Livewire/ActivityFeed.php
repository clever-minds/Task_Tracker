<?php

namespace App\Livewire;

use App\Models\ChatMessage;
use Livewire\Component;

class ActivityFeed extends Component
{
    public function render()
    {
        $activity = ChatMessage::query()
            ->where('message_type', '!=', 'daily_prompt')
            ->with(['employee', 'task', 'sender'])
            ->latest()
            ->limit(30)
            ->get();

        return view('livewire.activity-feed', ['activity' => $activity]);
    }
}
