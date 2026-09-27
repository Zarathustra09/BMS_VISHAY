@php
    $daysUntil = today()->diffInDays($machine->next_maintenance_date, false);
    if ($daysUntil < 0) {
        [$color, $label] = ['error', 'Overdue'];
    } elseif ($daysUntil <= 7) {
        [$color, $label] = ['warning', 'Due Soon'];
    } else {
        [$color, $label] = ['ghost', 'Upcoming'];
    }
@endphp
<div class="badge badge-{{ $color }} gap-1">{{ $label }}</div>
