<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Mail account
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Connect your own mail account (Gmail, Outlook, a custom domain, etc.) so welcome and task-assignment emails to employees are sent from your address.
        </p>

        @if ($hasMailConfigured)
            <p class="mt-2 text-sm font-medium text-green-600">Mail is configured.</p>
        @else
            <p class="mt-2 text-sm font-medium text-amber-600">Mail is not configured yet — employees won't receive welcome or task emails until you set this up.</p>
        @endif
    </header>

    <form wire:submit="save" class="mt-6 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="sm:col-span-2 lg:col-span-1">
                <x-input-label for="mail_host" value="SMTP Host" />
                <x-text-input id="mail_host" wire:model="mail_host" type="text" class="mt-1 block w-full" placeholder="smtp.gmail.com" />
                <x-input-error class="mt-2" :messages="$errors->get('mail_host')" />
            </div>

            <div>
                <x-input-label for="mail_port" value="Port" />
                <x-text-input id="mail_port" wire:model="mail_port" type="number" class="mt-1 block w-full" placeholder="587" />
                <x-input-error class="mt-2" :messages="$errors->get('mail_port')" />
            </div>

            <div>
                <x-input-label for="mail_encryption" value="Encryption" />
                <select id="mail_encryption" wire:model="mail_encryption" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="none">None</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('mail_encryption')" />
            </div>

            <div>
                <x-input-label for="mail_username" value="Username" />
                <x-text-input id="mail_username" wire:model="mail_username" type="text" class="mt-1 block w-full" placeholder="you@example.com" autocomplete="off" />
                <x-input-error class="mt-2" :messages="$errors->get('mail_username')" />
            </div>

            <div>
                <x-input-label for="mail_password" value="Password" />
                <x-text-input id="mail_password" wire:model="mail_password" type="password" class="mt-1 block w-full" placeholder="{{ $hasMailConfigured ? 'Leave blank to keep current password' : '' }}" autocomplete="new-password" />
                <x-input-error class="mt-2" :messages="$errors->get('mail_password')" />
            </div>

            <div>
                <x-input-label for="mail_from_address" value="From address" />
                <x-text-input id="mail_from_address" wire:model="mail_from_address" type="email" class="mt-1 block w-full" placeholder="you@example.com" />
                <x-input-error class="mt-2" :messages="$errors->get('mail_from_address')" />
            </div>

            <div>
                <x-input-label for="mail_from_name" value="From name" />
                <x-text-input id="mail_from_name" wire:model="mail_from_name" type="text" class="mt-1 block w-full" placeholder="Your Company" />
                <x-input-error class="mt-2" :messages="$errors->get('mail_from_name')" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button>Save</x-primary-button>

            @if ($saved)
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Saved.</p>
            @endif
        </div>
    </form>
</section>
