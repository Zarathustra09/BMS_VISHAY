{{--
    Shared by machines/create.blade.php and machines/edit.blade.php. Replaces the old 4-step
    wizard (duplicated ~800 lines in each of those two files, and already drifted between
    them) with a single ServiceNow-style scrolling form: a sticky action bar, a section jump
    rail on wide screens, and every field visible at once — type-specific spec fields are
    shown/hidden via Alpine rather than gated behind a "Next" button.

    Expects: $machine (Machine|null — null on create), $buildings, $machineTypes, $floors (only
    meaningful on edit — see MachineController@edit), and, on edit only, $thresholds.
--}}
@php
    $isEdit = (bool) ($machine ?? null);

    $machineTypeIdValue = old('machine_type_id', $machine?->machine_type_id ?? '');
    $statusValue = old('status', $machine?->status ?? 'active');
    $buildingValue = old('building_id', $machine?->building_id ?? '');
    $floorValue = old('floor_id', $machine?->floor_id ?? '');
    $lastMaintenanceValue = old('last_maintenance_date', $machine?->last_maintenance_date?->format('Y-m-d') ?? '');
    $maintenanceIntervalValue = old('maintenance_interval_days', $machine?->maintenance_interval_days ?? '');
    $nextMaintenanceValue = old('next_maintenance_date', $machine?->next_maintenance_date?->format('Y-m-d') ?? '');

    $actionUrl = $isEdit ? route('machines.update', $machine) : route('machines.store');
    $cancelHref = $isEdit ? route('machines.show', $machine) : route('machines.index');

    // Keyed by id-as-string, since every id coming out of the DOM (select value, JSON keys)
    // is a string — comparing as anything else is a recurring source of "it just won't switch"
    // bugs with Alpine.
    $specFieldsByTypeId = $machineTypes->mapWithKeys(fn ($type) => [(string) $type->id => $type->spec_fields ?? []]);

    $specErrors = collect($errors->keys())
        ->filter(fn ($key) => str_starts_with($key, 'specifications.'))
        ->mapWithKeys(fn ($key) => [substr($key, strlen('specifications.')) => $errors->first($key)]);

    // old('specifications') reflects the raw POST — a boolean field's checkbox posts the
    // string "1"/"0", not a real boolean — so a validation-failure redisplay is coerced back
    // through the field's own kind before being handed to Alpine. Without this, Alpine's
    // checkbox binding treats a truthy non-empty string like "0" as checked (JS's `!!"0"` is
    // true). Only the currently-selected type's fields matter here, since that's the only
    // type whose controls are actually rendered from this seed.
    $specValues = old('specifications', $machine?->specifications ?? []);
    foreach ($specFieldsByTypeId->get((string) $machineTypeIdValue, []) as $field) {
        $fieldKind = \App\Enums\SpecFieldType::tryFrom($field['type'] ?? '') ?? \App\Enums\SpecFieldType::Text;

        if ($fieldKind === \App\Enums\SpecFieldType::Boolean && array_key_exists($field['key'], $specValues)) {
            $specValues[$field['key']] = $fieldKind->cast($specValues[$field['key']]);
        } elseif ($fieldKind === \App\Enums\SpecFieldType::Multiselect) {
            $specValues[$field['key']] = $fieldKind->cast($specValues[$field['key']] ?? []);
        }
    }

    $seed = [
        'machineTypeId' => (string) $machineTypeIdValue,
        'selectedBuilding' => (string) $buildingValue,
        'currentFloorId' => $floorValue !== '' && $floorValue !== null ? (int) $floorValue : null,
        // Edit preloads the current building's floors so there's no fetch-on-first-paint;
        // create starts empty and calls loadFloors() once a building is picked.
        'floors' => $isEdit ? $floors->map(fn ($floor) => ['id' => $floor->id, 'name' => $floor->name])->values() : [],
        'lastMaintenance' => $lastMaintenanceValue,
        'maintenanceInterval' => $maintenanceIntervalValue,
        'nextMaintenance' => $nextMaintenanceValue,
        'specFieldsByTypeId' => $specFieldsByTypeId,
        'specValues' => $specValues,
        'specErrors' => $specErrors,
    ];
@endphp

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

<div class="space-y-6" x-data="machineForm(@js($seed))" x-cloak>

    {{-- ── Sticky action bar ── --}}
    <div class="sticky top-0 z-30">
        <div class="absolute inset-0 bg-base-200/90 backdrop-blur-md rounded-2xl border border-base-300/60 shadow-lg"></div>
        <div class="relative px-4 sm:px-6 py-4 flex flex-wrap items-center gap-4 justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold truncate">{{ $isEdit ? $machine?->name : 'New Machine' }}</h1>
                    <p class="text-xs opacity-60">{{ $isEdit ? 'Editing '.$machine?->machine_code : 'Creating a new machine record' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ $cancelHref }}" class="btn btn-ghost btn-sm gap-2">Cancel</a>
                <button type="submit" form="machineForm" class="btn btn-primary btn-sm gap-2" :disabled="submitting">
                    <span x-show="!submitting">{{ $isEdit ? 'Save Changes' : 'Create Machine' }}</span>
                    <span x-show="submitting" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>
    </div>

    <x-form.errors />

    <div class="xl:grid xl:grid-cols-[13rem_1fr] xl:gap-6 items-start">
        {{-- ── Section jump rail ── --}}
        <nav class="hidden xl:block sticky top-24 space-y-1 text-sm">
            <a href="#identification" class="block px-3 py-2 rounded-lg hover:bg-base-200">Identification</a>
            <a href="#location" class="block px-3 py-2 rounded-lg hover:bg-base-200">Location &amp; Power</a>
            <a href="#specifications" class="block px-3 py-2 rounded-lg hover:bg-base-200">Specifications</a>
            <a href="#maintenance" class="block px-3 py-2 rounded-lg hover:bg-base-200">Maintenance</a>
            <a href="#notes" class="block px-3 py-2 rounded-lg hover:bg-base-200">Notes &amp; Criticality</a>
        </nav>

        <form id="machineForm" action="{{ $actionUrl }}" method="POST" @submit="submitting = true" class="space-y-6 min-w-0">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <x-form.section id="identification" title="Identification" subtitle="Core details that identify this machine"
                             badge="Required" badge-class="badge-error">
                <x-form.input name="name" label="Machine Name" required span maxlength="255"
                              placeholder="e.g., Chiller Unit A" :value="$machine?->name ?? null" />

                <x-form.input name="machine_code" label="Machine Code" required maxlength="100"
                              placeholder="e.g., MCH-001" :value="$machine?->machine_code ?? null" />

                <x-form.select name="machine_type_id" label="Type" required placeholder="Select a type"
                                x-model="machineTypeId" @change="ensureDefaults()"
                                :options="$machineTypes->pluck('name', 'id')->all()" :selected="$machineTypeIdValue" />

                <x-form.select name="status" label="Status" required
                                :options="\App\Enums\MachineStatus::options()" :selected="$statusValue" />

                <x-form.input name="manufacturer" label="Manufacturer" maxlength="255" :value="$machine?->manufacturer ?? null" />
                <x-form.input name="model_number" label="Model Number" maxlength="255" :value="$machine?->model_number ?? null" />
                <x-form.input name="serial_number" label="Serial Number" maxlength="255" :value="$machine?->serial_number ?? null" />
            </x-form.section>

            <x-form.section id="location" title="Location & Power" subtitle="Where the machine sits and its electrical spec">
                @module('buildings')
                <x-form.select name="building_id" label="Building" placeholder="— No Building —"
                                x-model="selectedBuilding" @change="loadFloors()"
                                :options="$buildings->pluck('name', 'id')->all()" :selected="$buildingValue" />

                <x-form.select name="floor_id" label="Floor" x-bind:disabled="!selectedBuilding || floorsLoading">
                    <option value="">— No Floor —</option>
                    <template x-for="floor in floors" :key="floor.id">
                        <option :value="floor.id" x-text="floor.name" :selected="floor.id == currentFloorId"></option>
                    </template>
                    <template x-if="floorsLoading">
                        <option disabled>Loading floors...</option>
                    </template>
                </x-form.select>
                @endmodule

                <x-form.input type="date" name="installation_date" label="Installation Date"
                              :value="$machine?->installation_date?->format('Y-m-d') ?? null" />

                <x-form.input type="number" name="power_rating" label="Power Rating (kW)" step="0.01" min="0"
                              :value="$machine?->power_rating ?? null" />

                <x-form.input type="number" name="spec_voltage" label="Voltage (V)" step="0.01" min="0"
                              :value="$machine?->spec_voltage ?? null" />

                <x-form.input name="ip_address" label="IP Address" help="For network-connected machines"
                              :value="$machine?->ip_address ?? null" />
            </x-form.section>

            <x-form.section id="specifications" title="Specifications" subtitle="Fields shown depend on the selected Type">
                <div x-show="!machineTypeId" class="md:col-span-2 alert alert-info alert-soft">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Select a Machine Type in the Identification section to see relevant spec fields.</span>
                </div>

                <div x-show="machineTypeId && currentFields().length === 0" class="md:col-span-2 alert alert-soft">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <span>This type has no specification fields defined. Add some from Machine Types.</span>
                </div>

                <template x-for="field in currentFields()" :key="field.key">
                    <div class="flex flex-col gap-1.5" x-bind:class="field.type === 'multiselect' || field.type === 'textarea' ? 'md:col-span-2' : ''">
                        <label class="label py-0" x-bind:for="'spec_' + field.key">
                            <span class="text-sm font-medium">
                                <span x-text="field.label"></span>
                                <template x-if="field.unit"><span class="opacity-60" x-text="' (' + field.unit + ')'"></span></template>
                                <template x-if="field.required"><span class="text-error">*</span></template>
                            </span>
                        </label>

                        {{-- text / number / date / url / email — a single-line input --}}
                        <template x-if="['text', 'number', 'date', 'url', 'email'].includes(field.type)">
                            <input
                                x-bind:type="field.type"
                                x-bind:id="'spec_' + field.key"
                                x-bind:name="'specifications[' + field.key + ']'"
                                x-model="specValues[field.key]"
                                x-bind:step="field.type === 'number' ? (field.step || 'any') : null"
                                x-bind:min="field.type === 'number' ? field.min : null"
                                x-bind:max="field.type === 'number' ? field.max : null"
                                x-bind:placeholder="field.placeholder"
                                x-bind:required="field.required"
                                x-bind:class="{ 'input-error': specErrors[field.key] }"
                                class="input w-full"
                            />
                        </template>

                        <template x-if="field.type === 'textarea'">
                            <textarea
                                x-bind:id="'spec_' + field.key"
                                x-bind:name="'specifications[' + field.key + ']'"
                                x-model="specValues[field.key]"
                                x-bind:placeholder="field.placeholder"
                                x-bind:required="field.required"
                                x-bind:class="{ 'textarea-error': specErrors[field.key] }"
                                class="textarea w-full" rows="3"
                            ></textarea>
                        </template>

                        <template x-if="field.type === 'select'">
                            <select
                                x-bind:id="'spec_' + field.key"
                                x-bind:name="'specifications[' + field.key + ']'"
                                x-model="specValues[field.key]"
                                x-bind:required="field.required"
                                x-bind:class="{ 'select-error': specErrors[field.key] }"
                                class="select w-full"
                            >
                                <option value="">— Select —</option>
                                <template x-for="option in field.options" :key="option">
                                    <option :value="option" x-text="option"></option>
                                </template>
                            </select>
                        </template>

                        <template x-if="field.type === 'multiselect'">
                            <div class="flex flex-wrap gap-x-4 gap-y-2 p-3 rounded-lg border border-base-300">
                                <template x-for="option in field.options" :key="option">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" class="checkbox checkbox-sm"
                                               x-bind:name="'specifications[' + field.key + '][]'"
                                               :value="option"
                                               x-model="specValues[field.key]" />
                                        <span class="text-sm" x-text="option"></span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="field.type === 'boolean'">
                            <div class="flex items-center gap-3">
                                <input type="hidden" x-bind:name="'specifications[' + field.key + ']'" value="0" />
                                <input type="checkbox"
                                       x-bind:id="'spec_' + field.key"
                                       x-bind:name="'specifications[' + field.key + ']'"
                                       value="1"
                                       x-model="specValues[field.key]"
                                       class="toggle toggle-primary" />
                            </div>
                        </template>

                        <template x-if="field.help">
                            <p class="text-xs opacity-60 mt-0.5" x-text="field.help"></p>
                        </template>
                        <template x-if="specErrors[field.key]">
                            <p class="text-error text-xs mt-0.5" x-text="specErrors[field.key]"></p>
                        </template>
                    </div>
                </template>
            </x-form.section>

            <x-form.section id="maintenance" title="Maintenance Schedule">
                <x-form.input type="date" name="last_maintenance_date" label="Last Maintenance Date"
                              x-model="lastMaintenance" @change="calcNextMaintenance()"
                              :value="$lastMaintenanceValue" />

                <x-form.input type="number" name="maintenance_interval_days" label="Maintenance Interval (days)" min="1"
                              x-model="maintenanceInterval" @input="calcNextMaintenance()"
                              help="Auto-calculates the next maintenance date"
                              :value="$maintenanceIntervalValue" />

                <div class="md:col-span-2">
                    <x-form.input type="date" name="next_maintenance_date" label="Next Maintenance Date"
                                  x-model="nextMaintenance" :value="$nextMaintenanceValue" />
                </div>

                <div x-show="nextMaintenance" class="md:col-span-2 alert alert-success alert-soft">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>
                        Next maintenance: <strong x-text="formatDate(nextMaintenance)"></strong>
                        <template x-if="maintenanceInterval"><span> · every <span x-text="maintenanceInterval"></span> days</span></template>
                    </span>
                </div>
            </x-form.section>

            <x-form.section id="notes" title="Notes & Criticality">
                <x-form.textarea name="description" label="Description" :rows="3" span
                                  :value="$machine?->description ?? null" />

                <x-form.toggle name="is_critical" label="Critical Machine" color="error"
                                description="Flags this machine as critical infrastructure — surfaced on dashboards and alerting"
                                :checked="$machine?->is_critical ?? false" />
            </x-form.section>
        </form>
    </div>
</div>

@if($isEdit)
    {{-- Alert Thresholds — a separate form/route (AlertService), independent of the machine
         form above; saving one never touches the other. --}}
    <div class="card bg-base-100 shadow-xl border border-base-300/50 mt-6">
        <div class="card-body space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-error/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold">Alert Thresholds</h2>
                    <p class="text-sm opacity-60">Leave a field blank to skip that bound. A metric with no bounds set at all isn't monitored.</p>
                </div>
            </div>

            <div class="divider my-0"></div>

            <form action="{{ route('machines.thresholds.update', $machine) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Warning Min</th>
                                <th>Warning Max</th>
                                <th>Critical Min</th>
                                <th>Critical Max</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Interfaces\AlertInterface::METRIC_LABELS as $metric => $label)
                                @php $threshold = $thresholds->get($metric); @endphp
                                <tr>
                                    <td class="font-medium whitespace-nowrap">{{ $label }}</td>
                                    @foreach(['warning_min', 'warning_max', 'critical_min', 'critical_max'] as $bound)
                                        <td>
                                            <input type="number" step="any"
                                                   name="thresholds[{{ $metric }}][{{ $bound }}]"
                                                   value="{{ old("thresholds.$metric.$bound", $threshold?->$bound) }}"
                                                   aria-label="{{ $label }} {{ str_replace('_', ' ', $bound) }}"
                                                   class="input input-sm w-28" />
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @error('thresholds')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror

                <div class="flex justify-end pt-4 border-t border-base-300">
                    <button type="submit" class="btn btn-primary gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save Thresholds
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@push('scripts')
<script>
function machineForm(seed) {
    return {
        machineTypeId: seed.machineTypeId || '',
        selectedBuilding: seed.selectedBuilding || '',
        currentFloorId: seed.currentFloorId,
        floors: seed.floors || [],
        floorsLoading: false,
        lastMaintenance: seed.lastMaintenance || '',
        maintenanceInterval: seed.maintenanceInterval || '',
        nextMaintenance: seed.nextMaintenance || '',
        specFieldsByTypeId: seed.specFieldsByTypeId || {},
        specValues: seed.specValues || {},
        specErrors: seed.specErrors || {},
        submitting: false,

        // The <select>'s value is always a string, and JSON object keys are always strings —
        // comparing machineTypeId as anything else is exactly the kind of "it just won't
        // switch" bug this app has already shipped once (see docs/08-frontend.md).
        currentFields() {
            return this.specFieldsByTypeId[this.machineTypeId] || [];
        },

        // A multiselect/boolean field's control relies on Alpine's checkbox binding picking
        // the right mode from the *current* value's JS type (an array binds as a checkbox
        // group, anything else as a single on/off) — a field with no value yet (freshly
        // switched to, or never given one) needs that starting value set explicitly, or the
        // first click would set it to the wrong type instead of building up an array/toggle.
        ensureDefaults() {
            this.currentFields().forEach((field) => {
                if (field.type === 'multiselect' && ! Array.isArray(this.specValues[field.key])) {
                    this.specValues[field.key] = [];
                } else if (field.type === 'boolean' && typeof this.specValues[field.key] !== 'boolean') {
                    this.specValues[field.key] = false;
                }
            });
        },

        async loadFloors() {
            this.currentFloorId = null;
            if (!this.selectedBuilding) { this.floors = []; return; }
            this.floorsLoading = true;
            try {
                const res = await fetch(`/floors?building_id=${this.selectedBuilding}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) this.floors = await res.json();
            } catch (e) {
                this.floors = [];
            } finally {
                this.floorsLoading = false;
            }
        },

        calcNextMaintenance() {
            if (this.lastMaintenance && this.maintenanceInterval) {
                const date = new Date(this.lastMaintenance);
                date.setDate(date.getDate() + parseInt(this.maintenanceInterval));
                this.nextMaintenance = date.toISOString().split('T')[0];
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        },

        init() {
            // Edit seeds `floors` from the server, so only create (which starts empty) needs
            // to fetch on load.
            if (this.selectedBuilding && this.floors.length === 0) this.loadFloors();
            this.ensureDefaults();
        },
    };
}
</script>
@endpush
