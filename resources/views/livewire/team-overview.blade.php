<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-8" wire:poll.4s>
    @forelse ($employees as $employee)
        <div class="bg-white border border-gray-150 hover:border-indigo-200 rounded-2xl p-4 sm:p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative overflow-hidden group">
            <!-- Decorative Accent line -->
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition duration-300"></div>

            <div>
                <!-- Top Section -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <!-- Employee Initials Avatar with shadow -->
                        @php
                            $words = explode(' ', $employee->name);
                            $initials = strtoupper(substr($words[0] ?? 'E', 0, 1) . substr($words[1] ?? '', 0, 1));
                            
                            // Determine online state dot color
                            $isOnline = $employee->last_seen_at && $employee->last_seen_at->gt(now()->subMinutes(15));
                            $isRecent = $employee->last_seen_at && $employee->last_seen_at->gt(now()->subHours(2));
                        @endphp
                        <div class="relative">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-md select-none">
                                {{ $initials }}
                            </div>
                            <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full border-2 border-white {{ $isOnline ? 'bg-emerald-500 animate-pulse' : ($isRecent ? 'bg-amber-400' : 'bg-gray-300') }}" title="{{ $isOnline ? 'Online now' : ($isRecent ? 'Active recently' : 'Offline') }}"></span>
                        </div>
                        <div>
                            <a href="{{ route('employees.show', $employee) }}" class="font-bold text-gray-900 hover:text-indigo-600 transition text-base">
                                {{ $employee->name }}
                            </a>
                            <p class="text-[10px] text-gray-400 font-medium tracking-wider mt-0.5 uppercase">Team Member</p>
                        </div>
                    </div>
                    
                    <!-- Last Seen Badge -->
                    <span class="text-[10px] bg-gray-50 border border-gray-150 px-2.5 py-1 rounded-full text-gray-500 font-semibold whitespace-nowrap shadow-sm">
                        {{ $employee->last_seen_at?->diffForHumans() ?? 'Not seen yet' }}
                    </span>
                </div>

                <!-- Current Task Info Widget -->
                <div class="mt-5 bg-gradient-to-br from-gray-50 to-slate-50/50 border border-gray-100 rounded-xl p-4 shadow-inner">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-gray-400 mb-2">Active Task & Status</p>
                    @if ($employee->current_task)
                        @php
                            $statusColors = [
                                'in_progress' => ['bg-blue-500', 'text-blue-700', 'bg-blue-50', 'border-blue-100', 'In Progress'],
                                'todo' => ['bg-gray-400', 'text-gray-700', 'bg-gray-50', 'border-gray-150', 'To Do'],
                                'blocked' => ['bg-rose-500', 'text-rose-700', 'bg-rose-50', 'border-rose-100', 'Blocked'],
                                'done' => ['bg-emerald-500', 'text-emerald-700', 'bg-emerald-50', 'border-emerald-100', 'Completed'],
                            ];
                            $cfg = $statusColors[$employee->current_task->status] ?? ['bg-gray-400', 'text-gray-700', 'bg-gray-50', 'border-gray-100', 'Unknown'];
                        @endphp
                        <div class="flex items-start gap-3">
                            <span class="w-2.5 h-2.5 rounded-full mt-1.5 {{ $cfg[0] }} {{ $employee->current_task->status === 'in_progress' ? 'animate-pulse' : '' }}"></span>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-gray-800 leading-snug">{{ $employee->current_task->title }}</p>
                                <span class="inline-block mt-1 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border {{ $cfg[2] }} {{ $cfg[1] }} {{ $cfg[3] }}">
                                    {{ $cfg[4] }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-gray-400 italic text-xs py-1">
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                            <span>No active tasks assigned</span>
                        </div>
                    @endif
                </div>

                <!-- Last Reply Section -->
                <div class="mt-4 px-1">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-gray-400 mb-1.5">Last Check-in / Message</p>
                    @if ($employee->last_reply_summary)
                        <div class="flex items-start gap-2 text-gray-600 text-xs leading-relaxed">
                            <svg class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            <span class="line-clamp-2 font-medium italic">"{{ $employee->last_reply_summary }}"</span>
                        </div>
                    @else
                        <p class="text-gray-400 italic text-xs">No activity logged today.</p>
                    @endif
                </div>
            </div>

            <!-- Footer Action Link -->
            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end">
                <a href="{{ route('employees.show', $employee) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 group/btn">
                    Open Dashboard
                    <svg class="w-3.5 h-3.5 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-2 bg-white border border-gray-150 rounded-2xl p-12 text-center shadow-sm">
            <svg class="w-14 h-14 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <h4 class="font-bold text-gray-900 text-lg">No active employees</h4>
            <p class="text-gray-500 text-sm mt-1.5 mb-5">Add your first employee to start monitoring progress.</p>
            <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg shadow-md transition">Add Employee</a>
        </div>
    @endforelse
</div>
