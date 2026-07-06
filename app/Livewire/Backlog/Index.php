<?php

namespace App\Livewire\Backlog;

use App\Models\BacklogItem;
use Livewire\Component;

class Index extends Component
{
    public string $title = '';
    public string $description = '';
    public int $priority = 0;
    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'integer',
        ];
    }

    public function create(): void
    {
        $this->validate();

        BacklogItem::create([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'priority' => $this->priority,
        ]);

        $this->reset(['title', 'description', 'priority', 'showForm']);
    }

    public function close(BacklogItem $item): void
    {
        $item->update(['status' => 'closed']);
    }

    public function render()
    {
        return view('livewire.backlog.index', [
            'items' => BacklogItem::where('status', '!=', 'closed')->orderByDesc('priority')->get(),
        ]);
    }
}
