<x-mail::message>
A new{{ $priority === 'urgent' ? ' urgent' : '' }} task has been assigned to you.

@if ($description)
{{ $description }}
@endif

<x-mail::button :url="$link">
View task
</x-mail::button>
</x-mail::message>
