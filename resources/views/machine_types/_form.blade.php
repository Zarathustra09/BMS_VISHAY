{{--
    Shared by machine_types/create.blade.php and machine_types/edit.blade.php.

    Expects: $machineType (MachineType|null — null on create), $inUse (bool — only meaningful
    on edit).
--}}
@php
    $isEdit = (bool) ($machineType ?? null);
    $actionUrl = $isEdit ? route('machine-types.update', $machineType) : route('machine-types.store');
    $cancelHref = route('machine-types.index');

    $seedFields = old('spec_fields', $machineType?->specFields() ?? []);

    // Per-row validation errors ("spec_fields.2.label" => message) collapsed into a flat
    // "index.attribute" => message map the Alpine repeater can look up by array index —
    // purely a presentation reshape of the framework's own $errors bag, not a business rule.
    $fieldErrors = [];
    foreach ($errors->keys() as $errorKey) {
        if (preg_match('/^spec_fields\.(\d+)\.(\w+)$/', $errorKey, $m)) {
            $fieldErrors["{$m[1]}.{$m[2]}"] = $errors->first($errorKey);
        }
    }
@endphp

<div class="space-y-6 max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
            <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 10.83V6a3 3 0 013-3z"></path>
            </svg>
        </div>
        {{ $isEdit ? 'Edit Machine Type' : 'Add Machine Type' }}
    </h1>

    <x-form.errors />

    <form action="{{ $actionUrl }}" method="POST" class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <x-form.section title="Type Details" subtitle="Name and description shown across the app">
            <x-form.input name="name" label="Name" required maxlength="100"
                          placeholder="e.g., Chiller" :value="$machineType?->name ?? null" />

            <x-form.textarea name="description" label="Description" :rows="3" span maxlength="1000"
                              :value="$machineType?->description ?? null" />

            <div class="flex flex-col gap-1.5 md:col-span-2">
                <label class="label py-0">
                    <span class="text-sm font-medium">Icon <span class="text-error">*</span></span>
                </label>

                @php
                    $selectedIcon = old('icon', $machineType?->icon ?? 'cog');
                @endphp

                <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
                    @foreach(\App\Enums\MachineTypeIcon::cases() as $iconCase)
                        <label class="cursor-pointer" title="{{ $iconCase->label() }}">
                            <input type="radio" name="icon" value="{{ $iconCase->value }}"
                                   class="peer sr-only" @checked($selectedIcon === $iconCase->value) />
                            <div class="flex flex-col items-center justify-center gap-1 rounded-lg border border-base-300 p-3 text-base-content/70 hover:bg-base-200 peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary transition-colors">
                                <x-machine-type-icon :icon="$iconCase->value" />
                                <span class="text-[10px] text-center leading-tight">{{ $iconCase->label() }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                @error('icon')
                    <p class="text-error text-xs mt-0.5">{{ $message }}</p>
                @enderror
            </div>
        </x-form.section>

        <div class="card bg-base-100 shadow-lg border border-base-300/50" x-data="specFieldBuilder(@js($seedFields), @js($fieldErrors))">
            <div class="card-body">
                <div class="flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <h2 class="card-title text-base">Specification Fields</h2>
                        <p class="text-sm opacity-60">The fields a machine of this type will ask for. Renaming a field's label later is safe — its stored data stays linked by an internal key that never changes.</p>
                    </div>
                </div>

                <div class="divider my-0"></div>

                @if($inUse ?? false)
                <div class="alert alert-warning alert-soft">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>Machines already use this type. Removing a field here hides it on those machines; the value already stored for it is kept, not deleted.</span>
                </div>
                @endif

                <template x-if="fields.length === 0">
                    <p class="text-sm opacity-50 py-2">No specification fields yet.</p>
                </template>

                <div class="space-y-3">
                    <template x-for="(field, i) in fields" :key="field.uid">
                        <div class="border border-base-300 rounded-xl p-4 space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="flex flex-col gap-1.5">
                                        <label class="label py-0"><span class="text-sm font-medium">Label <span class="text-error">*</span></span></label>
                                        <input type="text" x-bind:name="`spec_fields[${i}][label]`" x-model="field.label"
                                               class="input w-full" x-bind:class="fieldError(i, 'label') ? 'input-error' : ''"
                                               placeholder="e.g., Capacity" />
                                        <p class="text-error text-xs mt-0.5" x-show="fieldError(i, 'label')" x-text="fieldError(i, 'label')"></p>
                                    </div>

                                    <div class="flex flex-col gap-1.5">
                                        <label class="label py-0"><span class="text-sm font-medium">Kind <span class="text-error">*</span></span></label>
                                        <select x-bind:name="`spec_fields[${i}][type]`" x-model="field.type" class="select w-full">
                                            <option value="text">Text</option>
                                            <option value="number">Number</option>
                                        </select>
                                    </div>

                                    <template x-if="field.type === 'number'">
                                        <div class="flex flex-col gap-1.5">
                                            <label class="label py-0"><span class="text-sm font-medium">Unit</span></label>
                                            <input type="text" x-bind:name="`spec_fields[${i}][unit]`" x-model="field.unit"
                                                   class="input w-full" placeholder="e.g., tons" maxlength="20" />
                                        </div>
                                    </template>

                                    <template x-if="field.type === 'number'">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="flex flex-col gap-1.5">
                                                <label class="label py-0"><span class="text-sm font-medium">Step</span></label>
                                                <input type="text" x-bind:name="`spec_fields[${i}][step]`" x-model="field.step"
                                                       class="input w-full" placeholder="e.g., 0.01" />
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <label class="label py-0"><span class="text-sm font-medium">Min</span></label>
                                                <input type="text" x-bind:name="`spec_fields[${i}][min]`" x-model="field.min"
                                                       class="input w-full" placeholder="e.g., 0" />
                                            </div>
                                        </div>
                                    </template>

                                    <div class="flex items-center gap-2">
                                        <input type="hidden" x-bind:name="`spec_fields[${i}][required]`" value="0" />
                                        <input type="checkbox" x-bind:name="`spec_fields[${i}][required]`" value="1"
                                               x-model="field.required" class="checkbox checkbox-sm" />
                                        <span class="text-sm">Required on the machine form</span>
                                    </div>

                                    <template x-if="field.key">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs opacity-50">Key</span>
                                            <span class="badge badge-ghost font-mono" x-text="field.key"></span>
                                            <input type="hidden" x-bind:name="`spec_fields[${i}][key]`" x-bind:value="field.key" />
                                        </div>
                                    </template>
                                </div>

                                <div class="flex flex-col gap-1 shrink-0">
                                    <button type="button" class="btn btn-ghost btn-xs" @click="moveUp(i)" x-bind:disabled="i === 0" title="Move up">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-xs" @click="moveDown(i)" x-bind:disabled="i === fields.length - 1" title="Move down">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <button type="button" class="btn btn-error btn-outline btn-xs" @click="removeField(i)" title="Remove field">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="button" class="btn btn-outline btn-sm gap-2 mt-4" @click="addField()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add Field
                </button>
            </div>
        </div>

        <x-form.actions :cancel-href="$cancelHref"
                        :submit-label="$isEdit ? 'Save Changes' : 'Create Type'"
                        :destructive-href="$isEdit ? route('machine-types.confirm-delete', $machineType) : null" />
    </form>
</div>

@push('scripts')
<script>
function specFieldBuilder(seedFields, seedErrors) {
    return {
        fields: (seedFields || []).map((f, i) => ({
            uid: i + 1,
            key: f.key || null,
            label: f.label || '',
            type: f.type === 'number' ? 'number' : 'text',
            unit: f.unit || '',
            step: f.step || '',
            min: f.min ?? '',
            required: !!f.required,
        })),
        nextUid: (seedFields || []).length + 1,
        errors: seedErrors || {},

        addField() {
            this.fields.push({
                uid: this.nextUid++, key: null, label: '', type: 'text',
                unit: '', step: '', min: '', required: false,
            });
        },

        removeField(index) {
            this.fields.splice(index, 1);
        },

        moveUp(index) {
            if (index === 0) return;
            const fields = this.fields;
            [fields[index - 1], fields[index]] = [fields[index], fields[index - 1]];
        },

        moveDown(index) {
            if (index === this.fields.length - 1) return;
            const fields = this.fields;
            [fields[index + 1], fields[index]] = [fields[index], fields[index + 1]];
        },

        fieldError(index, attribute) {
            return this.errors[index + '.' + attribute] || null;
        },
    };
}
</script>
@endpush
