<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Employee <span class="text-gray-400 font-normal">/</span> {{ $employee->name }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:employees.show :employee="$employee" />
        </div>
    </div>
</x-app-layout>
