<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6" x-data="{ notice: false }" x-on:backlog-empty.window="notice = true; setTimeout(() => notice = false, 3000)">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h4 class="font-bold text-gray-900 text-sm uppercase tracking-wide">Needs Attention / Idle</h4>
            <p class="text-xs text-gray-400 mt-0.5">Employees with no active task in progress or not seen in 24+ hours.</p>
        </div>
        <span class="px-2 py-0.5 bg-rose-50 text-rose-700 text-[10px] font-semibold border border-rose-100 rounded-full">Alert Mode</span>
    </div>

    <div x-show="notice" x-transition class="text-xs bg-amber-50 border border-amber-100 text-amber-700 p-3 rounded-lg mb-4 flex items-center gap-2">
        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span>No matching backlog items to suggest. Add items to the Backlog first.</span>
    </div>

    <div class="space-y-3">
        @forelse ($idleEmployees as $employee)
            <div class="flex items-center justify-between border border-gray-100 hover:border-gray-150 rounded-xl p-4 bg-gray-50/20 hover:shadow-sm transition duration-150">
                <div class="flex items-center gap-3">
                    <!-- Warning Dot -->
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    <div>
                        <a href="{{ route('employees.show', $employee) }}" class="font-semibold text-gray-900 text-sm hover:text-indigo-600 hover:underline transition">
                            {{ $employee->name }}
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $employee->last_seen_at ? 'Last seen ' . $employee->last_seen_at->diffForHumans() : 'Never checked in' }}
                        </p>
                    </div>
                </div>
                <button wire:click="suggestFromBacklog({{ $employee->id }})" class="text-xs px-3.5 py-1.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition shadow-sm">
                    Suggest from Backlog
                </button>
            </div>
        @empty
            <div class="text-center py-6 border border-dashed border-gray-200 rounded-xl bg-gray-50/30">
                <svg class="w-8 h-8 text-emerald-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-emerald-700 font-medium text-xs">All employees are currently active and productive!</p>
            </div>
        @endforelse
    </div>
</div>
