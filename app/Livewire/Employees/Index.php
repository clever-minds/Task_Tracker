<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Illuminate\Support\Str;
use Livewire\Component;

class Index extends Component
{
    public string $name = '';
    public string $email = '';
    public string $checkin_frequency = 'daily';

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'checkin_frequency' => 'required|in:daily,every_2_days,weekly',
        ];
    }

    public function create(): void
    {
        $this->validate();

        Employee::create([
            'name' => $this->name,
            'email' => $this->email ?: null,
            'checkin_frequency' => $this->checkin_frequency,
            'chat_token' => Str::random(40),
        ]);

        $this->reset(['name', 'email']);
        $this->checkin_frequency = 'daily';
        $this->showForm = false;
    }

    public function regenerateLink(Employee $employee): void
    {
        $employee->update(['chat_token' => Str::random(40)]);
    }

    public function toggleActive(Employee $employee): void
    {
        $employee->update(['is_active' => ! $employee->is_active]);
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }

    public function render()
    {
        return view('livewire.employees.index', [
            'employees' => Employee::orderBy('name')->get(),
        ]);
    }
}
