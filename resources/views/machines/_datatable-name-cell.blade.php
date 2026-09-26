<div>
    <div class="font-medium flex items-center gap-2">
        {{ $machine->name }}
        @if($machine->is_critical)
            <div class="badge badge-error badge-xs">Critical</div>
        @endif
    </div>
    <div class="text-xs opacity-60 font-mono">{{ $machine->machine_code }}</div>
</div>
