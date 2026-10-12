{{--
    The "Sensor Parameters" section of machines/_form.blade.php: a repeater for the readings this
    machine's device reports through the sensor API (POST /api/machine-logs, in the `readings`
    object), with each number parameter's accepted range and alert thresholds.

    Lives inside the form's `machineForm()` Alpine scope (state + methods are defined there), and
    posts as `parameters[i][...]`. An existing row (has an id) keeps its key and data type — the
    server ignores any change to them, since stored readings and the device's firmware refer to
    them. Expects (optional): $copySources — machines to copy parameters from (create only).
--}}
<x-form.section id="parameters" title="Sensor Parameters"
                subtitle="The readings this machine's device sends through the API. Each one can be range-checked, charted and alerted on.">
    <div class="md:col-span-2 space-y-4">
        @isset($copySources)
            @if($copySources->isNotEmpty())
                <div class="flex flex-col sm:flex-row sm:items-end gap-2 p-3 rounded-xl bg-base-200/60">
                    <div class="flex flex-col gap-1.5 flex-1">
                        <label class="label py-0" for="copyParametersFrom"><span class="text-sm font-medium">Copy parameters from an existing machine</span></label>
                        <select id="copyParametersFrom" class="select select-sm w-full" x-model="copyFrom">
                            <option value="">Select a machine…</option>
                            @foreach($copySources as $source)
                                <option value="{{ $source->id }}">{{ $source->name }} ({{ $source->machine_code }}) — {{ $source->parameters_count }} {{ Str::plural('parameter', $source->parameters_count) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline gap-2" :disabled="!copyFrom || copying" @click="copyParameters()">
                        <span class="loading loading-spinner loading-xs" x-show="copying"></span>
                        Copy
                    </button>
                </div>
            @endif
        @endisset

        @error('parameters')
            <p class="text-error text-sm">{{ $message }}</p>
        @enderror

        <template x-if="parameters.length === 0">
            <p class="text-sm opacity-50 py-2">No parameters yet. Without any, the API accepts a log's other fields but no sensor readings for this machine.</p>
        </template>

        <div class="space-y-3">
            <template x-for="(row, i) in parameters" :key="row.uid">
                <div class="border border-base-300 rounded-xl p-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                            <input type="hidden" x-bind:name="`parameters[${i}][id]`" x-bind:value="row.id ?? ''" />
                            <input type="hidden" x-bind:name="`parameters[${i}][data_type]`" x-bind:value="row.data_type" />

                            <div class="flex flex-col gap-1.5">
                                <label class="label py-0"><span class="text-sm font-medium">Label <span class="text-error">*</span></span></label>
                                <input type="text" x-bind:name="`parameters[${i}][label]`" x-model="row.label"
                                       @input="syncKeyFromLabel(row)"
                                       class="input w-full" x-bind:class="parameterError(i, 'label') ? 'input-error' : ''"
                                       placeholder="e.g., Coolant temperature" maxlength="255" />
                                <p class="text-error text-xs mt-0.5" x-show="parameterError(i, 'label')" x-text="parameterError(i, 'label')"></p>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="label py-0">
                                    <span class="text-sm font-medium">Key <span class="text-error">*</span></span>
                                    <span class="text-xs opacity-60" x-show="row.id">cannot be changed</span>
                                </label>
                                <input type="text" x-bind:name="`parameters[${i}][key]`" x-model="row.key"
                                       @input="row.keyTouched = true"
                                       x-bind:readonly="!!row.id"
                                       class="input w-full font-mono text-sm"
                                       x-bind:class="[parameterError(i, 'key') ? 'input-error' : '', row.id ? 'bg-base-200 opacity-70' : '']"
                                       placeholder="coolant_temperature" maxlength="50" />
                                <p class="text-error text-xs mt-0.5" x-show="parameterError(i, 'key')" x-text="parameterError(i, 'key')"></p>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="label py-0">
                                    <span class="text-sm font-medium">Data type <span class="text-error">*</span></span>
                                    <span class="text-xs opacity-60" x-show="row.id">cannot be changed</span>
                                </label>
                                <select x-model="row.data_type" x-bind:disabled="!!row.id" class="select w-full">
                                    @foreach(\App\Enums\ParameterDataType::options() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="label py-0"><span class="text-sm font-medium">Unit</span></label>
                                <input type="text" x-bind:name="`parameters[${i}][unit]`" x-model="row.unit"
                                       class="input w-full" placeholder="e.g., °C" maxlength="30" />
                                <p class="text-error text-xs mt-0.5" x-show="parameterError(i, 'unit')" x-text="parameterError(i, 'unit')"></p>
                            </div>

                            <div class="flex items-center gap-2 md:col-span-2">
                                <input type="hidden" x-bind:name="`parameters[${i}][required]`" value="0" />
                                <input type="checkbox" x-bind:name="`parameters[${i}][required]`" value="1"
                                       x-model="row.required" class="checkbox checkbox-sm" />
                                <span class="text-sm">Required in every <code>sensor_reading</code> the device sends</span>
                            </div>

                            <template x-if="row.data_type === 'number'">
                                <div class="md:col-span-2 space-y-4">
                                    <div>
                                        <p class="text-xs font-medium opacity-70 mb-1">Accepted range — a reading outside it is rejected by the API</p>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="flex flex-col gap-1">
                                                <span class="text-xs opacity-70">Lowest accepted</span>
                                                <input type="number" step="any" x-bind:name="`parameters[${i}][min]`" x-model="row.min"
                                                       class="input input-sm w-full" x-bind:class="parameterError(i, 'min') ? 'input-error' : ''"
                                                       placeholder="No minimum" aria-label="Lowest accepted" />
                                                <p class="text-error text-xs" x-show="parameterError(i, 'min')" x-text="parameterError(i, 'min')"></p>
                                            </label>
                                            <label class="flex flex-col gap-1">
                                                <span class="text-xs opacity-70">Highest accepted</span>
                                                <input type="number" step="any" x-bind:name="`parameters[${i}][max]`" x-model="row.max"
                                                       class="input input-sm w-full" x-bind:class="parameterError(i, 'max') ? 'input-error' : ''"
                                                       placeholder="No maximum" aria-label="Highest accepted" />
                                                <p class="text-error text-xs" x-show="parameterError(i, 'max')" x-text="parameterError(i, 'max')"></p>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="rounded-lg border border-base-300 p-3 space-y-3">
                                        <div>
                                            <p class="text-xs font-medium opacity-70">Alert limits</p>
                                            <p class="text-xs opacity-60">Fill in only the limits you need - a blank box is skipped, and with none filled in this parameter never raises an alert. "Critical" must be further out than "Warning".</p>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                            <label class="flex flex-col gap-1">
                                                <span class="text-xs opacity-70"><span class="inline-block w-2 h-2 rounded-full mr-1 bg-warning" aria-hidden="true"></span>Warning below</span>
                                                <input type="number" step="any" x-bind:name="`parameters[${i}][warning_min]`" x-model="row.warning_min"
                                                       class="input input-sm w-full" x-bind:class="parameterError(i, 'warning_min') ? 'input-error' : ''"
                                                       placeholder="e.g. 20" aria-label="Warning below" />
                                                <p class="text-error text-xs" x-show="parameterError(i, 'warning_min')" x-text="parameterError(i, 'warning_min')"></p>
                                            </label>
                                            <label class="flex flex-col gap-1">
                                                <span class="text-xs opacity-70"><span class="inline-block w-2 h-2 rounded-full mr-1 bg-warning" aria-hidden="true"></span>Warning above</span>
                                                <input type="number" step="any" x-bind:name="`parameters[${i}][warning_max]`" x-model="row.warning_max"
                                                       class="input input-sm w-full" x-bind:class="parameterError(i, 'warning_max') ? 'input-error' : ''"
                                                       placeholder="e.g. 80" aria-label="Warning above" />
                                                <p class="text-error text-xs" x-show="parameterError(i, 'warning_max')" x-text="parameterError(i, 'warning_max')"></p>
                                            </label>
                                            <label class="flex flex-col gap-1">
                                                <span class="text-xs opacity-70"><span class="inline-block w-2 h-2 rounded-full mr-1 bg-error" aria-hidden="true"></span>Critical below</span>
                                                <input type="number" step="any" x-bind:name="`parameters[${i}][critical_min]`" x-model="row.critical_min"
                                                       class="input input-sm w-full" x-bind:class="parameterError(i, 'critical_min') ? 'input-error' : ''"
                                                       placeholder="e.g. 10" aria-label="Critical below" />
                                                <p class="text-error text-xs" x-show="parameterError(i, 'critical_min')" x-text="parameterError(i, 'critical_min')"></p>
                                            </label>
                                            <label class="flex flex-col gap-1">
                                                <span class="text-xs opacity-70"><span class="inline-block w-2 h-2 rounded-full mr-1 bg-error" aria-hidden="true"></span>Critical above</span>
                                                <input type="number" step="any" x-bind:name="`parameters[${i}][critical_max]`" x-model="row.critical_max"
                                                       class="input input-sm w-full" x-bind:class="parameterError(i, 'critical_max') ? 'input-error' : ''"
                                                       placeholder="e.g. 100" aria-label="Critical above" />
                                                <p class="text-error text-xs" x-show="parameterError(i, 'critical_max')" x-text="parameterError(i, 'critical_max')"></p>
                                            </label>
                                        </div>

                                        <p class="text-xs" x-show="limitsSentence(row)" x-text="limitsSentence(row)"></p>

                                        <div class="rounded-lg border border-error/50 bg-error/10 px-3 py-2 text-xs text-error" role="alert" x-show="limitProblems(row).length">
                                            <p class="font-semibold">These limits contradict each other</p>
                                            <ul class="list-disc ml-4 mt-1 space-y-1">
                                                <template x-for="problem in limitProblems(row)" :key="problem">
                                                    <li x-text="problem"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex flex-col gap-1 shrink-0">
                            <button type="button" class="btn btn-ghost btn-xs" @click="moveParameterUp(i)" x-bind:disabled="i === 0" title="Move up">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                            <button type="button" class="btn btn-ghost btn-xs" @click="moveParameterDown(i)" x-bind:disabled="i === parameters.length - 1" title="Move down">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <button type="button" class="btn btn-error btn-outline btn-xs" @click="removeParameter(i)" title="Remove parameter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    <template x-if="row.id">
                        <p class="text-xs opacity-60">Removing a saved parameter stops this machine accepting it; readings already stored for it are kept in the logs.</p>
                    </template>
                </div>
            </template>
        </div>

        <button type="button" class="btn btn-outline btn-sm gap-2" @click="addParameter()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Add Parameter
        </button>
    </div>
</x-form.section>
