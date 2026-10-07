{{-- Expects: $log, $parameters (this machine's MachineParameters keyed by key). A reading whose
     parameter has since been deleted still renders, under a de-slugged version of its key. --}}
<div class="text-xs space-y-1">
    @foreach($log->readings ?? [] as $key => $value)
        @php
            $parameter = $parameters->get($key);
            $display = is_bool($value) ? ($value ? 'Yes' : 'No') : $value;
        @endphp
        <div>
            <span class="opacity-70">{{ $parameter?->label ?? ucfirst(str_replace('_', ' ', $key)) }}:</span>
            {{ $display }}@if($parameter?->unit) {{ $parameter->unit }}@endif
        </div>
    @endforeach
</div>
