<div class="grid grid-cols-1 lg:grid-cols-12 gap-8" wire:poll.4s>
    <!-- Left Column: Metrics & Tasks -->
    <div class="lg:col-span-7 space-y-6">
        
        <!-- Header Profile Card -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Date Picker Filter -->
                <div class="flex items-center gap-1">
                    <input type="date" wire:model.live="filterDate" class="text-xs rounded-lg border-gray-250 py-1 px-2.5 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700 shadow-sm transition">
                    @if($filterDate)
                        <button wire:click="$set('filterDate', null)" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-1.5 transition">Clear</button>
                    @endif
                </div>

                <!-- Status Badge -->
                <div class="flex items-center gap-2">
                    @php
                        $statusColors = [
                            'working' => 'bg-blue-50 text-blue-700 border-blue-100',
                            'away' => 'bg-amber-50 text-amber-700 border-amber-100',
                            'idle' => 'bg-rose-50 text-rose-700 border-rose-100',
                            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        ];
                        $statusColor = $statusColors[$metrics['active_status']] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border {{ $statusColor }}">
                        <span class="w-2 h-2 rounded-full mr-1.5 {{ str_replace('text-', 'bg-', explode(' ', $statusColor)[1]) }} animate-pulse"></span>
                        {{ $metrics['active_status_details'] }}
                    </span>
                    
                    <button wire:click="$toggle('showTaskForm')" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg shadow hover:bg-indigo-500 transition duration-150">
                        {{ $showTaskForm ? 'Cancel' : 'Assign Task' }}
                    </button>
                </div>
            </div>

            <div x-data="{ show: @entangle('showTaskForm') }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95" x-transition:enter-end="opacity-100 transform translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0 scale-100" x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95" class="origin-top" x-cloak>
                <form wire:submit="assignTask" class="mt-6 space-y-4 border-t border-gray-100 pt-6">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Title</label>
                        <input type="text" wire:model="newTaskTitle" class="mt-1 block w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                        @error('newTaskTitle') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Description (Optional)</label>
                        <textarea wire:model="newTaskDescription" rows="2" class="mt-1 block w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm"></textarea>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Priority</label>
                            <select wire:model="newTaskPriority" class="mt-1 block w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="self-end pt-1">
                            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition">Send to thread</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($filterDate)
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-xs text-indigo-850 flex items-center justify-between shadow-sm animate-fadeIn">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Showing chat messages, check-ins, and completed tasks for <strong>{{ Carbon\Carbon::parse($filterDate)->format('M j, Y') }}</strong>. Active tasks remain visible.</span>
                </div>
                <button wire:click="$set('filterDate', null)" class="text-xs font-semibold text-indigo-700 hover:text-indigo-900 transition">Show All History</button>
            </div>
        @endif

        <!-- Metrics Dashboard Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- Completion Rate -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 relative overflow-hidden flex flex-col justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Completion Rate</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="text-2xl font-bold text-gray-900">{{ $metrics['completion_rate'] }}%</p>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $metrics['completion_rate'] > 75 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $metrics['completion_rate'] > 75 ? 'Good' : 'Medium' }}
                    </span>
                </div>
            </div>
            
            <!-- Avg Close Time -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 relative overflow-hidden flex flex-col justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Avg. Close Time</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $metrics['avg_close_time_hours'] ?? '—' }}{{ $metrics['avg_close_time_hours'] ? 'h' : '' }}
                    </p>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ !$metrics['avg_close_time_hours'] || $metrics['avg_close_time_hours'] < 24 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ !$metrics['avg_close_time_hours'] || $metrics['avg_close_time_hours'] < 24 ? 'Fast' : 'Slow' }}
                    </span>
                </div>
            </div>

            <!-- Rework Rate -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 relative overflow-hidden flex flex-col justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Rework Rate</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="text-2xl font-bold text-gray-900">{{ $metrics['rework_rate'] ?? '0' }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ ($metrics['rework_rate'] ?? 0) <= 0.2 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ ($metrics['rework_rate'] ?? 0) <= 0.2 ? 'Stable' : 'High' }}
                    </span>
                </div>
            </div>

            <!-- Idle Days -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 relative overflow-hidden flex flex-col justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Idle Days (Total)</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="text-2xl font-bold text-gray-900">{{ $metrics['idle_days'] }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $metrics['idle_days'] < 2 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $metrics['idle_days'] < 2 ? 'Low' : 'Review' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Task Tabs and Lists -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6" x-data="{ tab: 'active' }">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                <h4 class="font-bold text-gray-900 text-sm uppercase tracking-wide">Tasks Board</h4>
                <div class="flex gap-2 text-xs bg-gray-50 p-1 rounded-lg border border-gray-100">
                    <button @click="tab = 'active'" :class="tab === 'active' ? 'bg-white text-indigo-600 shadow font-semibold' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 rounded-md transition duration-150">
                        Active
                    </button>
                    <button @click="tab = 'completed'" :class="tab === 'completed' ? 'bg-white text-indigo-600 shadow font-semibold' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 rounded-md transition duration-150">
                        Completed
                    </button>
                    <button @click="tab = 'other'" :class="tab === 'other' ? 'bg-white text-indigo-600 shadow font-semibold' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 rounded-md transition duration-150">
                        Other
                    </button>
                </div>
            </div>

            <!-- Active Tab Content -->
            <div x-show="tab === 'active'" class="space-y-4">
                @php $activeTasks = $employee->tasks->whereIn('status', ['todo', 'in_progress', 'blocked']); @endphp
                @forelse ($activeTasks as $task)
                    @php
                        $borderLeftColor = match($task->status) {
                            'in_progress' => 'border-l-4 border-l-blue-500',
                            'blocked' => 'border-l-4 border-l-rose-500',
                            'todo' => 'border-l-4 border-l-gray-300',
                            default => 'border-l-4 border-l-gray-150',
                        };
                    @endphp
                    <div class="border border-gray-150 {{ $borderLeftColor }} hover:shadow-md hover:border-indigo-100 rounded-xl p-4 transition duration-150">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <h5 class="font-bold text-gray-900 text-sm">{{ $task->title }}</h5>
                                <p class="text-[10px] text-gray-400 mt-1.5 flex items-center gap-2">
                                    <span class="font-bold px-2 py-0.5 rounded bg-gray-100 text-gray-600 uppercase tracking-wider">{{ str_replace('_', ' ', $task->source) }}</span>
                                    <span>·</span>
                                    <span class="{{ $task->priority === 'urgent' ? 'text-rose-600 font-bold' : 'text-gray-400 font-medium' }}">{{ ucfirst($task->priority) }} Priority</span>
                                </p>
                            </div>
                            <!-- Status Badge -->
                            @php
                                $badgeColor = match($task->status) {
                                    'in_progress' => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'blocked' => 'bg-rose-50 text-rose-700 border-rose-100',
                                    default => 'bg-gray-50 text-gray-600 border-gray-150',
                                };
                            @endphp
                            <span class="text-[10px] px-2.5 py-1 rounded-full border {{ $badgeColor }} font-bold uppercase tracking-wider">{{ $task->statusLabel() }}</span>
                        </div>

                        @if ($task->description)
                            <p class="text-xs text-gray-600 mt-2.5 bg-gray-50 p-2.5 rounded-lg leading-relaxed border border-gray-100">{{ $task->description }}</p>
                        @endif

                        <div class="mt-4 flex gap-2">
                            <input type="text" wire:model="commentText.{{ $task->id }}" placeholder="Comment on this task…" class="flex-1 text-xs rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-1.5 px-3">
                            <button wire:click="addComment({{ $task->id }})" class="text-xs px-3 py-1.5 bg-gray-50 border border-gray-200 text-gray-750 font-bold rounded-lg hover:bg-gray-100 transition shadow-sm">Comment</button>
                            <button wire:click="deleteTask({{ $task->id }})" wire:confirm="Are you sure you want to delete this task? This will remove all associated chat history for this task." class="text-xs px-3 py-1.5 bg-rose-50 border border-rose-150 text-rose-700 font-bold rounded-lg hover:bg-rose-100 transition shadow-sm">Delete</button>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 italic text-sm text-center py-6">No active tasks right now.</p>
                @endforelse
            </div>

            <!-- Completed Tab Content -->
            <div x-show="tab === 'completed'" class="space-y-4" style="display: none;">
                @forelse ($completedTasks as $task)
                    <div class="border border-gray-100 hover:border-gray-200 hover:shadow-sm rounded-xl p-4 transition duration-150">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <h5 class="font-semibold text-gray-900 text-sm line-through decoration-gray-400 text-gray-500">{{ $task->title }}</h5>
                                <p class="text-xs text-gray-400 mt-1">
                                    Completed at {{ $task->completed_at?->format('M j, Y g:i A') ?? '—' }} 
                                    @if($task->reopened_count > 0)
                                        · <span class="text-rose-600">Reopened {{ $task->reopened_count }}x</span>
                                    @endif
                                </p>
                            </div>
                            <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 font-medium">Completed</span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 italic text-sm text-center py-6">No completed tasks yet.</p>
                @endforelse
            </div>

            <!-- Other Tab Content -->
            <div x-show="tab === 'other'" class="space-y-4" style="display: none;">
                @php $otherTasks = $employee->tasks->whereIn('status', ['paused', 'cancelled']); @endphp
                @forelse ($otherTasks as $task)
                    <div class="border border-gray-100 hover:border-gray-200 hover:shadow-sm rounded-xl p-4 transition duration-150">
                        <div class="flex justify-between items-center gap-4">
                            <div>
                                <h5 class="font-semibold text-gray-900 text-sm">{{ $task->title }}</h5>
                                <p class="text-xs text-gray-400 mt-1">Source: {{ ucfirst(str_replace('_', ' ', $task->source)) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @php
                                    $badgeColor = $task->status === 'cancelled' ? 'bg-red-50 text-red-700 border-red-100' : 'bg-amber-50 text-amber-700 border-amber-100';
                                @endphp
                                <span class="text-xs px-2.5 py-1 rounded-full border {{ $badgeColor }} font-medium">{{ $task->statusLabel() }}</span>
                                <button wire:click="deleteTask({{ $task->id }})" wire:confirm="Are you sure you want to delete this task? This will remove all associated chat history for this task." class="text-xs px-2.5 py-1 bg-rose-50 border border-rose-150 text-rose-700 font-semibold rounded-lg hover:bg-rose-100 transition shadow-sm">Delete</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 italic text-sm text-center py-6">No postponed or cancelled tasks.</p>
                @endforelse
            </div>
        </div>

        <!-- Daily log history -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h4 class="font-bold text-gray-900 text-sm uppercase tracking-wide mb-4">Check-in Logs</h4>
            <div class="space-y-4">
                @forelse ($dailyLogs as $log)
                    <div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-500">{{ $log->log_date->format('l, M j, Y') }}</p>
                            @if($log->replied_at)
                                <span class="text-[10px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full border border-emerald-100 font-medium">Replied {{ $log->replied_at->diffForHumans() }}</span>
                            @else
                                <span class="text-[10px] bg-rose-50 text-rose-700 px-2 py-0.5 rounded-full border border-rose-100 font-medium">No Reply (Idle)</span>
                            @endif
                        </div>
                        <div class="mt-1.5 space-y-1">
                            @if ($log->reply_text)
                                <p class="text-sm text-gray-700 leading-relaxed">{{ $log->reply_text }}</p>
                            @elseif ($log->status_reported && $log->task)
                                <div class="text-sm text-gray-750 leading-relaxed">
                                    Reported task <span class="font-semibold text-gray-900">"{{ $log->task->title }}"</span> as <span class="font-semibold text-indigo-600">{{ ucfirst(str_replace('_', ' ', $log->status_reported)) }}</span>
                                    @if ($log->task->description)
                                        <p class="text-xs text-gray-400 mt-0.5 italic">Task details: {{ $log->task->description }}</p>
                                    @endif
                                </div>
                            @elseif ($log->status_reported)
                                <p class="text-sm text-gray-700 leading-relaxed">Reported status: <span class="font-semibold text-indigo-600">{{ ucfirst(str_replace('_', ' ', $log->status_reported)) }}</span></p>
                            @else
                                <p class="text-sm text-gray-450 italic leading-relaxed">No check-in received.</p>
                            @endif
                            
                            @if ($log->next_plan)
                                <p class="text-xs font-semibold text-indigo-650 mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                                    Remark: <span class="text-gray-700 font-normal">{{ $log->next_plan }}</span>
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 italic text-sm text-center py-4">No check-ins logged yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive Live Chat -->
    <div class="lg:col-span-5 flex flex-col bg-white border border-gray-200 rounded-xl shadow-sm h-[calc(100vh-14rem)] min-h-[550px]">
        <!-- Chat Header -->
        <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between bg-gray-50 rounded-t-xl">
            <div>
                <h4 class="font-bold text-gray-900 text-sm uppercase tracking-wide">Live Chat Thread</h4>
                <p class="text-xs text-gray-500 mt-0.5">Real-time view with employee</p>
            </div>
            <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-ping"></span>
        </div>

        <!-- Chat Messages Container -->
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4 bg-gray-50/50" id="admin-chat-container">
            @forelse ($messages as $message)
                @if ($message->message_type === 'system_note')
                    <!-- System Notes -->
                    <div class="flex justify-center">
                        <span class="bg-gray-100 text-gray-500 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-gray-200">
                            {{ $message->content }}
                        </span>
                    </div>
                @else
                    <!-- Chat bubbles -->
                    @php
                        $isManager = $message->sender_type === 'manager';
                    @endphp
                    <div class="flex {{ $isManager ? 'justify-end' : 'justify-start' }}">
                        <div class="flex flex-col max-w-[85%]">
                            <div class="px-4 py-2.5 rounded-2xl text-sm shadow-sm leading-relaxed {{ $isManager ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white text-gray-800 border border-gray-100 rounded-tl-none' }}">
                                
                                @if($message->message_type === 'task_push')
                                    <div class="flex items-center gap-1.5 mb-1.5 bg-indigo-750 px-2 py-1 rounded text-xs font-semibold text-white/90">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        TASK DISPATCHED
                                    </div>
                                @endif

                                @if($message->task_id && $message->message_type === 'comment' && $message->task)
                                    <div class="flex items-center gap-1.5 mb-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $isManager ? 'bg-indigo-700/50 text-indigo-100' : 'bg-gray-50 border border-gray-100 text-gray-600' }}">
                                        <svg class="w-3.5 h-3.5 {{ $isManager ? 'text-indigo-200' : 'text-indigo-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        Comment on: {{ $message->task->title }}
                                    </div>
                                @endif

                                <p class="whitespace-pre-line">{{ $message->content }}</p>
                            </div>
                            
                            <!-- Sender & Time -->
                            <span class="text-[10px] text-gray-400 mt-1 {{ $isManager ? 'text-right mr-1' : 'ml-1' }}">
                                {{ $isManager ? 'You' : $employee->name }} · {{ $message->created_at->diffForHumans() }}
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

        <!-- Chat Input Area -->
        <div class="border-t border-gray-100 p-4 bg-white rounded-b-xl">
            <form wire:submit.prevent="sendAdminMessage" class="flex gap-2 items-end">
                <div class="flex-1">
                    <textarea wire:model="adminMessage" rows="3" placeholder="Type a message... (Use /task title | desc | urgent to assign)" class="block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm py-2 px-3 resize-none scrollbar-none" style="max-height: 120px;" onkeydown="if(event.keyCode === 13 && !event.shiftKey) { event.preventDefault(); @this.call('sendAdminMessage'); }"></textarea>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-500 shadow-sm transition">Send</button>
            </form>
            <div class="flex items-center justify-between text-[10px] text-gray-400 mt-2 px-1">
                <span>⚡ Tip: type <code class="bg-gray-100 px-1 py-0.5 rounded font-mono">/task Fix bugs | Desc | urgent</code> to assign inline</span>
                <span>Press Enter to send</span>
            </div>
        </div>
    </div>
</div>
