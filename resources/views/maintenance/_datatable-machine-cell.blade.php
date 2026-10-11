<div>
    @can('machines.view')<a href="{{ route('machines.show', $machine) }}" class="link link-hover font-medium">{{ $machine->name }}</a>@else<span class="font-medium">{{ $machine->name }}</span>@endcan
    <div class="text-xs opacity-60"><code class="font-mono">{{ $machine->machine_code }}</code></div>
</div>
