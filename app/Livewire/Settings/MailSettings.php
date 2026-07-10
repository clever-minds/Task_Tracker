<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class MailSettings extends Component
{
    public string $mail_host = '';
    public ?int $mail_port = 587;
    public string $mail_username = '';
    public string $mail_password = '';
    public string $mail_encryption = 'tls';
    public string $mail_from_address = '';
    public string $mail_from_name = '';

    public bool $saved = false;

    public function mount(): void
    {
        $user = auth()->user();

        $this->mail_host = (string) $user->mail_host;
        $this->mail_port = $user->mail_port ?: 587;
        $this->mail_username = (string) $user->mail_username;
        $this->mail_encryption = $user->mail_encryption ?: 'tls';
        $this->mail_from_address = (string) $user->mail_from_address;
        $this->mail_from_name = (string) $user->mail_from_name;
        // mail_password is intentionally left blank - it's write-only from the UI's
        // perspective so the encrypted value never round-trips back to the browser.
    }

    public function save(): void
    {
        $validated = $this->validate([
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'required|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'required|in:tls,ssl,none',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        $validated['mail_encryption'] = $validated['mail_encryption'] === 'none' ? null : $validated['mail_encryption'];

        if ($validated['mail_password'] === '' || $validated['mail_password'] === null) {
            unset($validated['mail_password']);
        }

        $user->update($validated);

        $this->mail_password = '';
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.settings.mail-settings', [
            'hasMailConfigured' => auth()->user()->hasMailConfigured(),
        ]);
    }
}
