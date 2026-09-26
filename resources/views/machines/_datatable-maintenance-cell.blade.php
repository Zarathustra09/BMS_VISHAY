@if($machine->next_maintenance_date)
    @php
        $days = now()->diffInDays($machine->next_maintenance_date, false);
        $overdue = $days < 0;
        $dueSoon = $days >= 0 && $days <= 7;
    @endphp
    <span class="text-xs {{ $overdue ? 'text-error font-medium' : ($dueSoon ? 'text-warning font-medium' : '') }}">
        @if($overdue)
            Overdue {{ abs($days) }}d
        @elseif($dueSoon)
            Due in {{ $days }}d
        @else
            {{ $machine->next_maintenance_date->format('M d, Y') }}
        @endif
    </span>
@else
    <span class="opacity-40 text-xs">—</span>
@endif
