<x-mail::message>
Hi {{ $employee->name }},

You've been added to the team task tracker. This is your personal link — no account or password needed, just keep it handy.

<x-mail::button :url="$link">
Open your task thread
</x-mail::button>

Use it to see what's assigned to you, mark progress, and reply directly.
</x-mail::message>
