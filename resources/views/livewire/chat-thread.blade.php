<div class="max-w-2xl mx-auto flex flex-col h-screen">
    <div class="border-b bg-white px-4 py-3 flex items-center justify-between">
        <div>
            <p class="font-semibold text-gray-800">Hey {{ $employee->name }} 👋</p>
        </div>
        <div class="flex gap-4 text-sm">
            <button wire:click="$set('activeTab', 'chat')" class="{{ $activeTab === 'chat' ? 'text-gray-900 font-medium' : 'text-gray-400' }}">Chat</button>
            <button wire:click="$set('activeTab', 'mytasks')" class="{{ $activeTab === 'mytasks' ? 'text-gray-900 font-medium' : 'text-gray-400' }}">My tasks</button>
        </div>
    </div>

    @if ($activeTab === 'chat')
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50">
            @forelse ($messages as $message)
                <div class="flex {{ $message->sender_type === 'employee' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2 {{ $message->sender_type === 'employee' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-800 shadow-sm' }}">
                        <p class="text-sm whitespace-pre-line">{{ $message->content }}</p>

                        @if ($message->message_type === 'task_push' && $message->task && $message->task->priority === 'urgent' && $message->task->status === 'todo' && empty($dismissedPrompts[$message->task_id]))
                            <div class="mt-2 flex gap-2">
                                <button wire:click="acknowledgeTask({{ $message->task_id }})" class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded-md">Got it</button>
                                <button wire:click="switchToTask({{ $message->task_id }})" wire:confirm="Pause your current task and switch to this one?" class="text-xs px-2 py-1 bg-amber-100 text-amber-800 rounded-md">I'm mid-task — switch me</button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-400 text-sm mt-8">No messages yet.</p>
            @endforelse
        </div>

        <div class="border-t bg-white px-4 py-3 space-y-3">
            @if ($pendingStatus)
                @if (! $selectedTaskId)
                    <div>
                        <p class="text-xs text-gray-500 mb-2">Which task?</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse ($openTasks as $task)
                                <button wire:click="selectTaskForStatus({{ $task->id }})" class="text-xs px-3 py-1 bg-gray-100 rounded-full hover:bg-gray-200">{{ $task->title }}</button>
                            @empty
                                <p class="text-xs text-gray-400 italic">No open tasks — use the reply box instead.</p>
                            @endforelse
                            <button wire:click="cancelStatusFlow" class="text-xs px-3 py-1 text-gray-400">Cancel</button>
                        </div>
                    </div>
                @else
                    <div>
                        <p class="text-xs text-gray-500 mb-2">What's next? (optional)</p>
                        <div class="flex gap-2">
                            <input type="text" wire:model="nextPlanText" placeholder="Not sure yet" class="flex-1 text-sm rounded-md border-gray-300">
                            <button wire:click="submitStatusUpdate" class="text-sm px-3 py-1 bg-indigo-600 text-white rounded-md">Send</button>
                            <button wire:click="cancelStatusFlow" class="text-sm px-2 text-gray-400">Cancel</button>
                        </div>
                    </div>
                @endif
            @elseif ($showAddTask)
                <div class="flex gap-2">
                    <input type="text" wire:model="newTaskTitle" placeholder="New task title" class="flex-1 text-sm rounded-md border-gray-300">
                    <button wire:click="addTask" class="text-sm px-3 py-1 bg-indigo-600 text-white rounded-md">Add</button>
                    <button wire:click="$set('showAddTask', false)" class="text-sm px-2 text-gray-400">Cancel</button>
                </div>
            @else
                <div class="flex flex-wrap gap-2">
                    <button wire:click="chooseStatus('done')" class="text-xs px-3 py-1 bg-green-100 text-green-700 rounded-full">✅ Done</button>
                    <button wire:click="chooseStatus('in_progress')" class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded-full">🔄 In Progress</button>
                    <button wire:click="chooseStatus('blocked')" class="text-xs px-3 py-1 bg-red-100 text-red-700 rounded-full">🚧 Blocked</button>
                    <button wire:click="$set('showAddTask', true)" class="text-xs px-3 py-1 bg-gray-100 text-gray-700 rounded-full">+ Add task</button>
                    <button wire:click="markAway" wire:confirm="Mark today as away?" class="text-xs px-3 py-1 bg-gray-100 text-gray-700 rounded-full">Mark today as away</button>
                </div>

                <form wire:submit="sendReply" class="flex gap-2">
                    <input type="text" wire:model="replyText" placeholder="What moved today? What's next?" class="flex-1 text-sm rounded-md border-gray-300">
                    <button type="submit" class="text-sm px-4 py-2 bg-indigo-600 text-white rounded-md">Send</button>
                </form>
            @endif
        </div>
    @else
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50">
            @forelse ($myTasks as $task)
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <p class="font-medium text-gray-800 text-sm">{{ $task->title }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</p>
                </div>
            @empty
                <p class="text-center text-gray-400 text-sm mt-8">No open tasks.</p>
            @endforelse
        </div>
    @endif
</div>
