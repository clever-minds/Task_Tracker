<div class="grid grid-cols-1 md:grid-cols-2 gap-6" wire:poll.5s>
    @forelse ($employees as $employee)
        <div class="bg-white border border-gray-200 hover:border-gray-300 rounded-xl p-6 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between">
            <div>
                <!-- Top Section -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <!-- Employee Initials Avatar -->
                        @php
                            $words = explode(' ', $employee->name);
                            $initials = strtoupper(substr($words[0] ?? 'E', 0, 1) . substr($words[1] ?? '', 0, 1));
                        @endphp
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center font-bold text-sm shadow-sm select-none">
                            {{ $initials }}
                        </div>
                        <div>
                            <a href="{{ route('employees.show', $employee) }}" class="font-bold text-gray-900 hover:text-indigo-600 hover:underline transition">
                                {{ $employee->name }}
                            </a>
                        </div>
                    </div>
                    
                    <!-- Last Seen Badge -->
                    <span class="text-[10px] bg-gray-50 border border-gray-150 px-2 py-0.5 rounded-full text-gray-500 font-medium whitespace-nowrap">
                        {{ $employee->last_seen_at?->diffForHumans() ?? 'Not seen yet' }}
                    </span>
                </div>

                <!-- Current Task Info -->
                <div class="mt-5 bg-gray-50/50 border border-gray-100 rounded-lg p-3.5">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-gray-400 mb-1">Active Status</p>
                    @if ($employee->current_task && $employee->current_task->status === 'in_progress')
                        <div class="flex items-start gap-2">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 animate-pulse"></span>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $employee->current_task->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">In Progress (Urgent Priority)</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-gray-400 italic text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                            <span>No task in progress right now</span>
                        </div>
                    @endif
                </div>

                <!-- Last Reply Info -->
                <div class="mt-4 px-1">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-gray-400 mb-1">Last Reply</p>
                    <p class="text-gray-600 text-xs line-clamp-2 leading-relaxed">
                        {{ $employee->last_reply_summary ?? 'No updates logged today.' }}
                    </p>
                </div>
            </div>

            <!-- Footer Action Button -->
            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end">
                <a href="{{ route('employees.show', $employee) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 flex items-center gap-1 group">
                    View Productivity Dashboard
                    <svg class="w-3 h-3 transform group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-2 bg-white border border-gray-200 rounded-xl p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <h4 class="font-bold text-gray-900">No active employees</h4>
            <p class="text-gray-500 text-sm mt-1 mb-4">Add your first employee to start monitoring progress.</p>
            <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow transition">Add Employee</a>
        </div>
    @endforelse
</div>
