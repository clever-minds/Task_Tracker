<x-mail::message>
# Week of {{ $start->format('M j') }} – {{ $end->format('M j') }}

<x-mail::table>
| Employee | Done | Carryover | Idle days | Rework |
| :------- | :--: | :-------: | :-------: | :----: |
@foreach ($summary as $row)
| {{ $row['name'] }} | {{ $row['done'] }} | {{ $row['carryover'] }} | {{ $row['idle_days'] }} | {{ $row['rework_count'] }} |
@endforeach
</x-mail::table>
</x-mail::message>
