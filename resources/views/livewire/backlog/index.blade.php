<div class="space-y-6">
    <div class="flex justify-end">
        <button wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
            {{ $showForm ? 'Cancel' : '+ Add backlog item' }}
        </button>
    </div>

    @if ($showForm)
        <form wire:submit="create" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" wire:model="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('title') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea wire:model="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Suitable role</label>
                <select wire:model="suitable_role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="any">Any</option>
                    <option value="fresher_mvp">Fresher / No-code MVP builder</option>
                    <option value="laravel_dev">Laravel full-stack dev</option>
                    <option value="flutter_dev">Flutter dev</option>
                    <option value="freelancer_fullstack">Freelance full-stack/API dev</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Priority (higher = shown first)</label>
                <input type="number" wire:model="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-500">Add to backlog</button>
        </form>
    @endif

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($items as $item)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800">{{ $item->title }}</p>
                            @if ($item->description)
                                <p class="text-xs text-gray-500">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $item->suitable_role)) }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ ucfirst($item->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="close({{ $item->id }})" class="text-xs text-gray-500 hover:text-gray-800">Close</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-4 text-gray-400 italic">No backlog items yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
