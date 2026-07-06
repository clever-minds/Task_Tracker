<?php

namespace App\Livewire\Backlog;

use App\Models\BacklogItem;
use Livewire\Component;

class Index extends Component
{
    public string $title = '';
    public string $description = '';
    public string $suitable_role = 'any';
    public int $priority = 0;
    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'suitable_role' => 'required|in:fresher_mvp,laravel_dev,flutter_dev,freelancer_fullstack,any',
            'priority' => 'integer',
        ];
    }

    public function create(): void
    {
        $this->validate();

        BacklogItem::create([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'suitable_role' => $this->suitable_role,
            'priority' => $this->priority,
        ]);

        $this->reset(['title', 'description', 'priority', 'showForm']);
        $this->suitable_role = 'any';
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
