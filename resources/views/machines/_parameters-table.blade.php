{{--
    The machine's sensor parameters: what each one is, its latest reading and alert state, the range
    the API accepts and the alert limits in plain words. Shown on the machine page's Overview tab.
    Variables: $machine, $parameterRows (MachineParameterInterface::overview()).

    The value, "ago" and state pill carry data-param-* hooks; the page's refreshMachineSummary() script
    updates them from machines.summary whenever a reading or alert arrives, so they stay current live.
--}}
@php
    $statePills = [
        'critical' => ['badge-error', 'Critical'],
        'warning' => ['badge-warning', 'Warning'],
        'normal' => ['badge-success', 'Normal'],
        'no_limits' => ['badge-ghost', 'No limits'],
        'no_data' => ['badge-ghost', 'No data'],
    ];
@endphp

<div class="card bg-base-100 shadow-lg border border-base-300/50" id="machineParameters">
    <div class="card-body">
        <div class="flex items-center justify-between gap-2">
            <h2 class="card-title text-lg">
                Sensor parameters
                <span class="badge badge-ghost badge-sm">{{ count($parameterRows) }}</span>
            </h2>
            @can('machines.update')
                <a href="{{ route('machines.edit', $machine) }}#parameters" class="btn btn-ghost btn-xs">Edit parameters &amp; limits</a>
            @endcan
        </div>

        @if(count($parameterRows) === 0)
            <div class="rounded-lg bg-base-200/60 px-4 py-6 text-center text-sm opacity-80">
                <p class="font-medium">No sensor parameters yet</p>
                <p class="mt-1">Parameters define the readings this machine's device can send (temperature, humidity…).
                    @can('machines.update')
                        <a href="{{ route('machines.edit', $machine) }}#parameters" class="link">Add them on the Edit page.</a>
                    @endcan
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Latest reading</th>
                            <th>State</th>
                            <th>Accepted range</th>
                            <th>Alert limits</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parameterRows as $row)
                            @php [$pillClass, $pillText] = $statePills[$row['state']]; @endphp
                            <tr class="align-top">
                                <td>
                                    <div class="font-medium">{{ $row['label'] }}@if($row['unit']) <span class="opacity-60 font-normal">({{ $row['unit'] }})</span>@endif</div>
                                    <div class="text-xs opacity-60">
                                        <code>{{ $row['key'] }}</code>
                                        · {{ ['number' => 'Number', 'boolean' => 'Yes / No', 'text' => 'Text'][$row['type']] ?? $row['type'] }}
                                        @if($row['required']) · <span class="badge badge-outline badge-xs">required</span>@endif
                                    </div>
                                </td>
                                <td>
                                    <div class="font-semibold" data-param-value="{{ $row['key'] }}">{{ $row['display'] }}</div>
                                    <div class="text-xs opacity-60" data-param-ago="{{ $row['key'] }}">{{ $row['ago'] ?? 'No data yet' }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $pillClass }} gap-1 whitespace-nowrap" data-param-state="{{ $row['key'] }}">{{ $pillText }}</span>
                                </td>
                                <td class="whitespace-nowrap">{{ $row['range'] }}</td>
                                <td>
                                    @forelse($row['limits'] as $level => $text)
                                        <div class="text-sm">
                                            <span class="inline-block w-2 h-2 rounded-full mr-1 {{ $level === 'critical' ? 'bg-error' : 'bg-warning' }}" aria-hidden="true"></span>{{ $text }}
                                        </div>
                                    @empty
                                        <span class="text-sm opacity-60">{{ $row['type'] === 'number' ? 'No alert limits - this parameter never raises an alert' : '—' }}</span>
                                    @endforelse

                                    @if($row['problems'])
                                        <div class="mt-2 rounded-lg border border-warning/50 bg-warning/10 px-3 py-2 text-xs" role="alert">
                                            <div class="font-semibold">These limits contradict each other</div>
                                            <ul class="list-disc ml-4 mt-1 space-y-1">
                                                @foreach($row['problems'] as $problem)
                                                    <li>{{ $problem }}</li>
                                                @endforeach
                                            </ul>
                                            @can('machines.update')
                                                <a href="{{ route('machines.edit', $machine) }}#parameters" class="link mt-1 inline-block">Fix on the Edit page</a>
                                            @endcan
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
