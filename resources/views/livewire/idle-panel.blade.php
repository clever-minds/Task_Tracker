<div class="bg-white shadow-sm rounded-lg p-6" x-data="{ notice: false }" x-on:backlog-empty.window="notice = true; setTimeout(() => notice = false, 3000)">
    <h4 class="font-semibold text-gray-900 mb-1">Idle / needs attention</h4>
    <p class="text-xs text-gray-400 mb-4">Nothing in progress, not seen in 24+ hours. Visible only here.</p>

    <div x-show="notice" class="text-xs text-amber-600 mb-3">No matching backlog item to suggest — add one in Backlog.</div>

    <div class="space-y-3">
        @forelse ($idleEmployees as $employee)
            <div class="flex items-center justify-between border rounded-md p-3">
                <div>
                    <p class="font-medium text-gray-800">{{ $employee->name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $employee->last_seen_at ? 'Last seen '.$employee->last_seen_at->diffForHumans() : 'Never seen' }}
                    </p>
                </div>
                <button wire:click="suggestFromBacklog({{ $employee->id }})" class="text-sm px-3 py-1 bg-gray-100 rounded-md hover:bg-gray-200">
                    Suggest from backlog
                </button>
            </div>
        @empty
            <p class="text-gray-400 italic text-sm">No one idle right now.</p>
        @endforelse
    </div>
</div>
