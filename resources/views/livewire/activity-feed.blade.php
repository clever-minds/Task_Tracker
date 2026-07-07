<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6" wire:poll.5s>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h4 class="font-bold text-gray-900 text-sm uppercase tracking-wide">Live Activity Feed</h4>
            <p class="text-xs text-gray-400 mt-0.5">Real-time update timeline of recent employee logs and manager actions.</p>
        </div>
        <span class="w-2.5 h-2.5 bg-indigo-500 rounded-full animate-ping"></span>
    </div>

    <div class="space-y-4 max-h-[28rem] overflow-y-auto pr-2 scrollbar-thin">
        @forelse ($activity as $entry)
            <div class="flex items-start gap-3.5 border-b border-gray-50 pb-3 last:border-b-0 last:pb-0 group">
                <!-- Activity Icon based on Sender/Type -->
                @php
                    $isManager = $entry->sender_type === 'manager';
                    $isSystem = $entry->message_type === 'system_note';
                    
                    $iconBg = 'bg-gray-100 text-gray-500';
                    $iconSvg = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>';
                    
                    if ($isSystem) {
                        $iconBg = 'bg-slate-100 text-slate-600';
                        $iconSvg = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    } elseif ($isManager) {
                        $iconBg = 'bg-indigo-50 text-indigo-600';
                        $iconSvg = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>';
                    } elseif ($entry->message_type === 'status_update') {
                        $iconBg = 'bg-emerald-50 text-emerald-600';
                        $iconSvg = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    }
                @endphp
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 border border-gray-100 shadow-sm {{ $iconBg }}">
                    {!! $iconSvg !!}
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 leading-snug">
                        <a href="{{ route('employees.show', $entry->employee) }}" class="font-semibold text-indigo-600 hover:underline">
                            {{ $entry->employee->name }}
                        </a>
                        @if ($isManager)
                            <span class="text-xs font-bold text-gray-400 uppercase ml-1">Manager:</span>
                        @endif
                        <span class="text-gray-700 font-medium">{{ $entry->content }}</span>
                    </p>
                    
                    @if ($entry->task)
                        <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 mt-1 hover:text-gray-600 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Task: "{{ $entry->task->title }}"
                        </span>
                    @endif
                </div>
                
                <span class="text-[10px] text-gray-400 font-medium whitespace-nowrap pt-0.5">{{ $entry->created_at->diffForHumans() }}</span>
            </div>
        @empty
            <p class="text-gray-400 italic text-sm text-center py-8">No recent activity detected.</p>
        @endforelse
    </div>
</div>
