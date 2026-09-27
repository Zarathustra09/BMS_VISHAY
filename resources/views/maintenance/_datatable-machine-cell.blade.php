<div>
    <a href="{{ route('machines.show', $machine) }}" class="link link-hover font-medium">{{ $machine->name }}</a>
    <div class="text-xs opacity-60"><code class="font-mono">{{ $machine->machine_code }}</code></div>
</div>
