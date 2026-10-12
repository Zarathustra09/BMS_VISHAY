{{--
    The "API Integration" card on machines/show: everything a developer needs to make a device POST
    readings for THIS machine, built from its own parameters (MachineParameterService::apiExample()).
    The curl command carries a YOUR_DEVICE_KEY placeholder — the real key is never embedded here,
    so this card is safe to show to anyone who can view the machine.

    Expects: $machine, $apiExample.
--}}
<div class="card bg-base-100 shadow-lg border border-base-300/50"
     x-data="{
         tab: 'request',
         copied: null,
         copy(text, id) {
             navigator.clipboard.writeText(text);
             this.copied = id;
             setTimeout(() => { if (this.copied === id) this.copied = null; }, 1500);
         },
     }">
    <div class="card-body">
        <h2 class="card-title text-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
            API Integration
        </h2>
        <p class="text-sm opacity-70">
            How this machine's device sends readings. Only the parameters below are accepted — anything else is rejected with a <code>422</code>.
            Parameters are managed on the @can('machines.update')<a href="{{ route('machines.edit', $machine) }}#parameters" class="link">edit page</a>@else edit page @endcan.
        </p>

        <div role="tablist" class="tabs tabs-border mt-2">
            <button type="button" role="tab" class="tab" :class="tab === 'request' && 'tab-active'" @click="tab = 'request'">Request</button>
            <button type="button" role="tab" class="tab" :class="tab === 'body' && 'tab-active'" @click="tab = 'body'">Example body</button>
            <button type="button" role="tab" class="tab" :class="tab === 'curl' && 'tab-active'" @click="tab = 'curl'">curl</button>
            <button type="button" role="tab" class="tab" :class="tab === 'responses' && 'tab-active'" @click="tab = 'responses'">Responses</button>
        </div>

        {{-- Request: endpoint, header, parameter table --}}
        <div x-show="tab === 'request'" class="space-y-4 pt-2">
            <div class="grid grid-cols-1 md:grid-cols-[8rem_1fr] gap-x-4 gap-y-2 text-sm items-center">
                <span class="opacity-70">Endpoint</span>
                <span><span class="badge badge-neutral badge-sm mr-2">POST</span><code class="break-all">{{ $apiExample['endpoint'] }}</code></span>

                <span class="opacity-70">Auth header</span>
                <span><code>X-Device-Key: &lt;this machine's device key&gt;</code></span>

                <span class="opacity-70">Content type</span>
                <span><code>Content-Type: application/json</code></span>
            </div>

            <div>
                <p class="text-sm font-medium mb-1">Parameters accepted in <code>readings</code></p>
                @if($apiExample['parameters']->isEmpty())
                    <p class="text-sm opacity-60">This machine has no parameters yet, so it can't report any sensor readings. Add them on the edit page.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Key</th>
                                    <th>Label</th>
                                    <th>Type</th>
                                    <th>Unit</th>
                                    <th>Required</th>
                                    <th>Accepted range</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($apiExample['parameters'] as $parameter)
                                    <tr>
                                        <td><code>{{ $parameter->key }}</code></td>
                                        <td>{{ $parameter->label }}</td>
                                        <td>{{ $parameter->data_type->label() }}</td>
                                        <td>{{ $parameter->unit ?? '—' }}</td>
                                        <td>{{ $parameter->required ? 'Yes' : 'No' }}</td>
                                        <td>
                                            @if($parameter->min !== null || $parameter->max !== null)
                                                {{ $parameter->min !== null ? (float) $parameter->min : '−∞' }} to {{ $parameter->max !== null ? (float) $parameter->max : '∞' }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs opacity-60 mt-2">
                        A parameter marked <em>required</em> must be present in every <code>sensor_reading</code> log. Besides <code>log_type</code> and <code>readings</code>, a body may carry
                        <code>event</code>, <code>severity</code>, <code>notes</code>, <code>performed_by</code>, <code>previous_status</code>, <code>current_status</code> and <code>logged_at</code>.
                    </p>
                @endif
            </div>
        </div>

        {{-- Example body --}}
        <div x-show="tab === 'body'" x-cloak class="pt-2">
            <div class="relative">
                <button type="button" class="btn btn-xs btn-ghost absolute top-2 right-2"
                        @click="copy(@js($apiExample['body_json']), 'body')">
                    <span x-text="copied === 'body' ? 'Copied!' : 'Copy'"></span>
                </button>
                <pre class="bg-base-200 rounded-lg p-3 pr-16 text-xs overflow-x-auto"><code>{{ $apiExample['body_json'] }}</code></pre>
            </div>
        </div>

        {{-- curl --}}
        <div x-show="tab === 'curl'" x-cloak class="pt-2 space-y-2">
            <div class="relative">
                <button type="button" class="btn btn-xs btn-ghost absolute top-2 right-2"
                        @click="copy(@js($apiExample['curl']), 'curl')">
                    <span x-text="copied === 'curl' ? 'Copied!' : 'Copy'"></span>
                </button>
                <pre class="bg-base-200 rounded-lg p-3 pr-16 text-xs overflow-x-auto"><code>{{ $apiExample['curl'] }}</code></pre>
            </div>
            <p class="text-xs opacity-60">Replace <code>YOUR_DEVICE_KEY</code> with this machine's key (see Device API Key above).</p>
        </div>

        {{-- Example responses --}}
        <div x-show="tab === 'responses'" x-cloak class="pt-2 space-y-4">
            @foreach($apiExample['responses'] as $response)
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="badge badge-sm {{ $response['status'] < 300 ? 'badge-success' : ($response['status'] === 401 ? 'badge-warning' : 'badge-error') }}">{{ $response['status'] }}</span>
                        <span class="text-sm">{{ $response['title'] }}</span>
                    </div>
                    <pre class="bg-base-200 rounded-lg p-3 text-xs overflow-x-auto"><code>{{ $response['json'] }}</code></pre>
                </div>
            @endforeach
        </div>
    </div>
</div>
