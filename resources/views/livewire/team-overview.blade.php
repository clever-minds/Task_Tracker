<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse ($employees as $employee)
        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex items-start justify-between">
                <div>
                    <a href="{{ route('employees.show', $employee) }}" class="text-lg font-semibold text-gray-900 hover:underline">
                        {{ $employee->name }}
                    </a>
                </div>
                <span class="text-xs text-gray-400">
                    {{ $employee->last_seen_at?->diffForHumans() ?? 'Not seen yet' }}
                </span>
            </div>

            <div class="mt-4">
                <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Current task</p>
                @if ($employee->current_task)
                    <p class="text-gray-800">{{ $employee->current_task->title }}</p>
                @else
                    <p class="text-gray-400 italic">Nothing in progress</p>
                @endif
            </div>

            <div class="mt-4">
                <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Last reply</p>
                <p class="text-gray-600 text-sm">{{ $employee->last_reply_summary ?? '—' }}</p>
            </div>
        </div>
    @empty
        <p class="text-gray-500">No employees yet — add one to get started.</p>
    @endforelse
</div>
