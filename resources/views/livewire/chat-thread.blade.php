<div class="max-w-2xl mx-auto flex flex-col h-[100dvh] bg-gray-50/50 border-x border-gray-200" wire:poll.4s="$refresh">
    <!-- Header -->
    <div class="border-b bg-white px-6 py-4 flex items-center justify-between shadow-sm shrink-0">
        <div class="flex items-center gap-3">
            <!-- Initials Avatar -->
            @php
                $words = explode(' ', $employee->name);
                $initials = strtoupper(substr($words[0] ?? 'E', 0, 1) . substr($words[1] ?? '', 0, 1));
            @endphp
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center font-bold text-sm shadow-sm select-none">
                {{ $initials }}
            </div>
            <div>
                <p class="font-bold text-gray-900 leading-snug">Hey {{ $employee->name }} 👋</p>
                <p class="text-[10px] text-gray-400 mt-0.5 uppercase tracking-wider font-semibold">Workspace Check-in</p>
            </div>
        </div>
        
        <!-- Toggle Tabs -->
        <div class="flex bg-gray-100 p-1 rounded-lg border border-gray-250/60 text-xs">
            <button wire:click="$set('activeTab', 'chat')" class="px-4 py-1.5 rounded-md font-semibold transition duration-150 {{ $activeTab === 'chat' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                Chat Thread
            </button>
            <button wire:click="$set('activeTab', 'mytasks')" class="px-4 py-1.5 rounded-md font-semibold transition duration-150 {{ $activeTab === 'mytasks' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                My Tasks
            </button>
        </div>
    </div>

    @if ($activeTab === 'chat')
        <!-- Conversation Date Navigation -->
        <div class="bg-white border-b border-gray-150 px-6 py-2.5 flex items-center justify-between shrink-0 shadow-sm animate-fadeIn">
            <button wire:click="previousDay" class="p-1 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <div class="flex items-center gap-1.5 select-none">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="text-xs font-bold text-gray-700">
                    {{ $chatDate === now()->toDateString() ? 'Today' : (\Illuminate\Support\Carbon::parse($chatDate)->isYesterday() ? 'Yesterday' : \Illuminate\Support\Carbon::parse($chatDate)->format('l, M j, Y')) }}
                </span>
            </div>
            @php
                $isToday = $chatDate === now()->toDateString();
            @endphp
            <button wire:click="nextDay" @disabled($isToday) class="p-1 rounded-lg transition {{ $isToday ? 'text-gray-250 cursor-not-allowed' : 'hover:bg-gray-100 text-gray-500 hover:text-gray-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>

        <!-- Chat message viewport -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4 bg-gray-50/50" id="chat-messages-container">
            @forelse ($messages as $message)
                @if ($message->message_type === 'system_note')
                    <!-- System Notes -->
                    <div class="flex justify-center">
                        <span class="bg-gray-200/60 text-gray-500 text-[10px] font-bold px-2.5 py-1 rounded-full border border-gray-250/60 uppercase tracking-wider">
                            {{ $message->content }}
                        </span>
                    </div>
                @else
                    <!-- Message bubbles -->
                    @php
                        $isEmployee = $message->sender_type === 'employee';
                    @endphp
                    <div class="flex {{ $isEmployee ? 'justify-end' : 'justify-start' }}">
                        <div class="flex flex-col max-w-[85%]">
                            <div class="px-4 py-2.5 rounded-2xl text-sm shadow-sm leading-relaxed {{ $isEmployee ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white text-gray-800 border border-gray-150 rounded-tl-none' }}">
                                @if($message->message_type === 'task_push')
                                    <div class="flex items-center gap-1.5 mb-1.5 bg-gray-50 border border-gray-100 text-gray-600 px-2.5 py-1 rounded-lg text-xs font-semibold">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        New Assigned Task
                                    </div>
                                @endif

                                @if($message->task_id && $message->message_type === 'comment' && $message->task)
                                    <div class="flex items-center gap-1.5 mb-1.5 bg-gray-50 border border-gray-100 text-gray-600 px-2.5 py-1 rounded-lg text-xs font-semibold">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        Comment on: {{ $message->task->title }}
                                    </div>
                                @endif

                                <p class="whitespace-pre-line">{{ $message->content }}</p>

                                <!-- Task Action Promo -->
                                @if ($message->message_type === 'task_push' && $message->task && $message->task->priority === 'urgent' && $message->task->status === 'todo' && empty($dismissedPrompts[$message->task_id]))
                                    <div class="mt-3 flex gap-2 border-t border-gray-100 pt-3">
                                        <button wire:click="acknowledgeTask({{ $message->task_id }})" class="text-xs px-2.5 py-1 bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold rounded border border-gray-200 transition">
                                            Got it
                                        </button>
                                        <button wire:click="switchToTask({{ $message->task_id }})" wire:confirm="Pause your current task and switch to this one?" class="text-xs px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold rounded border border-amber-100 transition">
                                            I'm mid-task — switch me
                                        </button>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Timestamp -->
                            <span class="text-[10px] text-gray-400 mt-1 {{ $isEmployee ? 'text-right mr-1' : 'ml-1' }}">
                                {{ $isEmployee ? 'You' : 'Manager' }} · {{ $message->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                @endif
            @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-6">
                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    <p class="text-gray-400 italic text-sm">No messages yet. Send a message to start.</p>
                </div>
            @endforelse
        </div>

        <!-- Chat input dock -->
        <div class="border-t bg-white p-4 space-y-3 shrink-0 shadow-lg">
            @if ($pendingStatus)
                @if (! $selectedTaskId)
                    <div class="animate-fadeIn">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2.5">Select a task to report status</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse ($openTasks as $task)
                                <button wire:click="selectTaskForStatus({{ $task->id }})" class="text-xs px-3.5 py-1.5 bg-gray-50 border border-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 hover:border-gray-300 transition shadow-sm">
                                    {{ $task->title }}
                                </button>
                            @empty
                                <p class="text-xs text-gray-400 italic py-1">No open tasks available. Use the reply box instead.</p>
                            @endforelse
                            <button wire:click="cancelStatusFlow" class="text-xs px-3.5 py-1.5 text-gray-400 hover:text-gray-600 transition font-medium">Cancel</button>
                        </div>
                    </div>
                @else
                    <div class="animate-fadeIn">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2.5">Remark (Optional)</p>
                        <div class="flex gap-2">
                            <input type="text" wire:model="nextPlanText" placeholder="Add a comment or remark on this update..." class="flex-1 text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-1.5 px-3">
                            <button wire:click="submitStatusUpdate" class="text-sm px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow transition">Send</button>
                            <button wire:click="cancelStatusFlow" class="text-sm px-3 text-gray-400 hover:text-gray-600 transition font-medium">Cancel</button>
                        </div>
                    </div>
                @endif
            @elseif ($showAddTask)
                <div class="flex gap-2 animate-fadeIn">
                    <input type="text" wire:model="newTaskTitle" placeholder="New plan/task title..." class="flex-1 text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-1.5 px-3">
                    <button wire:click="addTask" class="text-sm px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow transition">Add</button>
                    <button wire:click="$set('showAddTask', false)" class="text-sm px-3 text-gray-400 hover:text-gray-600 transition font-medium">Cancel</button>
                </div>
            @else
                <!-- Quick status updating options -->
                <div class="flex flex-wrap items-center gap-2 mb-2 pb-2 border-b border-gray-50">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mr-1.5">Quick update:</span>
                    <button wire:click="chooseStatus('done')" class="text-[11px] px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-full border border-emerald-100 shadow-sm transition">
                        ✅ Done
                    </button>
                    <button wire:click="chooseStatus('in_progress')" class="text-[11px] px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-full border border-blue-100 shadow-sm transition">
                        🔄 In Progress
                    </button>
                    <button wire:click="chooseStatus('blocked')" class="text-[11px] px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-full border border-rose-100 shadow-sm transition">
                        🚧 Blocked
                    </button>
                    <button wire:click="$set('showAddTask', true)" class="text-[11px] px-3 py-1 bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold rounded-full border border-gray-200 shadow-sm transition">
                        + Add Task
                    </button>
                    @if ($isAway)
                        <button disabled class="text-[11px] px-3 py-1 bg-emerald-50 text-emerald-700 font-semibold rounded-full border border-emerald-100 shadow-sm cursor-not-allowed select-none animate-fadeIn">
                            Away Today ✅
                        </button>
                    @else
                        <button wire:click="markAway" wire:confirm="Mark today as away?" class="text-[11px] px-3 py-1 bg-gray-50 hover:bg-gray-100 text-gray-750 font-semibold rounded-full border border-gray-200 shadow-sm transition">
                            Mark Away
                        </button>
                    @endif
                </div>

                <!-- Text Chat Input -->
                <form wire:submit="sendReply" class="flex gap-2">
                    <input type="text" wire:model="replyText" placeholder="What moved today? What's next?" class="flex-1 text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-2 px-3">
                    <button type="submit" class="text-sm px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow transition">Send</button>
                </form>
            @endif
        </div>
    @else
        <!-- My Tasks Board Tab -->
        <div class="flex-1 overflow-y-auto bg-gray-50 flex flex-col">
            <!-- Quick-add Plan/Todo Input & Status Filter -->
            <div class="border-b bg-white px-6 py-4 flex flex-col sm:flex-row gap-3 shadow-sm shrink-0 items-center justify-between">
                <div class="flex-1 w-full flex gap-2">
                    <input type="text" wire:model="newTaskTitle" wire:keydown.enter="addTask" placeholder="Add a plan or todo item directly..." class="flex-1 text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 shadow-sm">
                    <button wire:click="addTask" class="text-sm px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow transition">Add</button>
                </div>
                
                <!-- Status Filter Dropdown -->
                <div class="flex items-center gap-1.5 w-full sm:w-auto justify-end">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Filter:</span>
                    <select wire:model.live="taskStatusFilter" class="text-xs rounded-xl border-gray-250 bg-gray-55 hover:bg-gray-100 py-1.5 pl-2.5 pr-8 font-semibold text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                        <option value="all">All Statuses</option>
                        <option value="todo">Todo</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Completed</option>
                        <option value="paused">Postponed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Task viewport list -->
            <div class="p-6 space-y-3.5 flex-1 overflow-y-auto">
                @forelse ($myTasks as $task)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center justify-between gap-4 hover:shadow transition duration-150">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $task->title }}</p>
                            <p class="text-[10px] text-gray-400 mt-1 flex items-center gap-1.5 uppercase font-bold">
                                <span class="px-2 py-0.5 rounded bg-gray-50 text-gray-500 border border-gray-200">{{ str_replace('_', ' ', $task->source) }}</span>
                                <span>·</span>
                                <span class="text-indigo-600">{{ $task->statusLabel() }}</span>
                            </p>
                        </div>
                        <select wire:change="updateTaskStatus({{ $task->id }}, $event.target.value)" class="text-xs rounded-lg border-gray-250 bg-gray-50 hover:bg-gray-100 py-1.5 pl-2.5 pr-8 font-semibold text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                            <option value="todo" @selected($task->status === 'todo')>Todo</option>
                            <option value="in_progress" @selected($task->status === 'in_progress')>In Progress</option>
                            <option value="done" @selected($task->status === 'done')>Completed</option>
                            <option value="paused" @selected($task->status === 'paused')>Postponed</option>
                            <option value="cancelled" @selected($task->status === 'cancelled')>Cancelled</option>
                        </select>
                    </div>
                @empty
                    <div class="text-center py-10 bg-white border border-gray-200 border-dashed rounded-xl p-6 shadow-sm mt-4">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <p class="text-gray-400 italic text-sm">No tasks or plans yet. Add one above to get started!</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
