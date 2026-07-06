<div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $employee->name }}</h3>
                <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $employee->role)) }} · {{ ucfirst(str_replace('_', ' ', $employee->checkin_frequency)) }} check-in</p>
            </div>
            <button wire:click="$set('showTaskForm', {{ $showTaskForm ? 'false' : 'true' }})" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                {{ $showTaskForm ? 'Cancel' : '+ Assign task' }}
            </button>
        </div>

        @if ($showTaskForm)
            <form wire:submit="assignTask" class="mt-4 space-y-3 border-t pt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" wire:model="newTaskTitle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('newTaskTitle') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea wire:model="newTaskDescription" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Priority</label>
                    <select wire:model="newTaskPriority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-500">Send to their thread</button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white shadow-sm rounded-lg p-4 text-center">
            <p class="text-xs uppercase text-gray-400">Avg. close time</p>
            <p class="text-xl font-semibold text-gray-800">{{ $metrics['avg_close_time_hours'] ?? '—' }}{{ $metrics['avg_close_time_hours'] ? ' hrs' : '' }}</p>
        </div>
        <div class="bg-white shadow-sm rounded-lg p-4 text-center">
            <p class="text-xs uppercase text-gray-400">Rework rate</p>
            <p class="text-xl font-semibold text-gray-800">{{ $metrics['rework_rate'] ?? '—' }}</p>
        </div>
        <div class="bg-white shadow-sm rounded-lg p-4 text-center">
            <p class="text-xs uppercase text-gray-400">Idle days logged</p>
            <p class="text-xl font-semibold text-gray-800">{{ $metrics['idle_days'] }}</p>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg p-6">
        <h4 class="font-semibold text-gray-900 mb-4">Task history</h4>
        <div class="space-y-4">
            @forelse ($employee->tasks as $task)
                <div class="border rounded-md p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-gray-800">{{ $task->title }}</p>
                            <p class="text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', $task->source)) }} · {{ $task->priority }} priority</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                    </div>

                    @if ($task->description)
                        <p class="text-sm text-gray-600 mt-2">{{ $task->description }}</p>
                    @endif

                    <div class="mt-3 flex gap-2">
                        <input type="text" wire:model="commentText.{{ $task->id }}" placeholder="Comment on this task…" class="flex-1 text-sm rounded-md border-gray-300">
                        <button wire:click="addComment({{ $task->id }})" class="text-sm px-3 py-1 bg-gray-100 rounded-md hover:bg-gray-200">Send</button>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 italic">No tasks yet.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg p-6">
        <h4 class="font-semibold text-gray-900 mb-4">Daily log history</h4>
        <div class="space-y-3">
            @forelse ($employee->dailyLogs as $log)
                <div class="border-b pb-3">
                    <p class="text-xs text-gray-400">{{ $log->log_date->format('D, M j') }}</p>
                    <p class="text-sm text-gray-700">{{ $log->reply_text ?? ($log->status_reported ? ucfirst(str_replace('_', ' ', $log->status_reported)) : 'No reply') }}</p>
                    @if ($log->next_plan)
                        <p class="text-xs text-gray-500">Next: {{ $log->next_plan }}</p>
                    @endif
                </div>
            @empty
                <p class="text-gray-400 italic">No check-ins logged yet.</p>
            @endforelse
        </div>
    </div>
</div>
