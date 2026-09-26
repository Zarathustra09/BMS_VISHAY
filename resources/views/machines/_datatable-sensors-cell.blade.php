<div class="text-xs space-y-1">
    @if($log->temperature)
        <div>🌡️ {{ $log->temperature }}°C</div>
    @endif
    @if($log->power_consumption)
        <div>⚡ {{ $log->power_consumption }} kW</div>
    @endif
    @if($log->vibration_rms)
        <div>📊 {{ $log->vibration_rms }} RMS</div>
    @endif
    @if($log->pressure)
        <div>💨 {{ $log->pressure }} bar</div>
    @endif
</div>
