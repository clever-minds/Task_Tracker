<div class="space-y-6">
    <div class="flex justify-end">
        <button wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
            {{ $showForm ? 'Cancel' : '+ Add employee' }}
        </button>
    </div>

    @if ($showForm)
        <form wire:submit="create" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select wire:model="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="fresher_mvp">Fresher / No-code MVP builder</option>
                    <option value="laravel_dev">Laravel full-stack dev</option>
                    <option value="flutter_dev">Flutter dev</option>
                    <option value="freelancer_fullstack">Freelance full-stack/API dev</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email (for daily reminder only, optional)</label>
                <input type="email" wire:model="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Check-in frequency</label>
                <select wire:model="checkin_frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="daily">Daily</option>
                    <option value="every_2_days">Every 2 days</option>
                    <option value="weekly">Weekly</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-500">
                Create employee
            </button>
        </form>
    @endif

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Their link</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($employees as $employee)
                    <tr>
                        <td class="px-6 py-4">
                            <a href="{{ route('employees.show', $employee) }}" class="text-indigo-600 hover:underline font-medium">{{ $employee->name }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $employee->role)) }}</td>
                        <td class="px-6 py-4">
                            <input type="text" readonly value="{{ url('/c/'.$employee->chat_token) }}" onclick="this.select()" class="text-xs w-64 rounded-md border-gray-300 bg-gray-50">
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs px-2 py-1 rounded-full {{ $employee->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                            <button wire:click="regenerateLink({{ $employee->id }})" wire:confirm="Regenerate this employee's link? The old one will stop working." class="text-xs text-gray-500 hover:text-gray-800">Regenerate link</button>
                            <button wire:click="toggleActive({{ $employee->id }})" class="text-xs text-gray-500 hover:text-gray-800">{{ $employee->is_active ? 'Deactivate' : 'Activate' }}</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
