@extends('layouts.app')

@section('title', 'Edit Machine — ' . $machine->name)

@section('breadcrumbs')
    <li><a href="{{ route('machines.index') }}">Machines</a></li>
    <li><a href="{{ route('machines.show', $machine) }}">{{ $machine->name }}</a></li>
    <li>Edit</li>
@endsection

@section('back', route('machines.show', $machine))

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="machineEditForm()" x-cloak>

    <!-- Header -->
    <div class="flex items-center gap-4">
        <div class="flex-1">
            <h1 class="text-3xl font-bold">Edit Machine</h1>
            <p class="text-sm opacity-70 mt-1">Updating <span class="font-semibold text-primary">{{ $machine->name }}</span> · <code class="text-xs bg-base-300 px-1.5 py-0.5 rounded">{{ $machine->machine_code }}</code></p>
        </div>
        <a href="{{ route('machines.show', $machine) }}" class="btn btn-ghost btn-sm gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            View Machine
        </a>
    </div>

    <!-- Validation Error Banner -->
    @if($errors->any())
    <div class="alert alert-error shadow-lg">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-bold">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-sm mt-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Sticky Progress Steps -->
    <div class="sticky top-0 z-30">
        <div class="absolute inset-0 bg-base-200/90 backdrop-blur-md rounded-2xl border border-base-300/60 shadow-lg"></div>
        <div class="relative px-6 py-4">
            <ul class="steps steps-horizontal w-full">
                <li class="step transition-all duration-300"
                    :class="currentStep >= 1 ? 'step-primary' : ''"
                    @click="goToStep(1)" style="cursor:pointer">
                    <span class="flex flex-col items-center gap-0.5">
                        <span class="hidden sm:inline text-[10px] font-bold tracking-widest uppercase"
                              :class="currentStep === 1 ? 'text-primary' : 'opacity-40'">Step 1</span>
                        <span class="font-semibold text-sm" :class="currentStep === 1 ? 'text-primary' : ''">Basic Info</span>
                    </span>
                </li>
                <li class="step transition-all duration-300"
                    :class="currentStep >= 2 ? 'step-primary' : ''"
                    @click="goToStep(2)" style="cursor:pointer">
                    <span class="flex flex-col items-center gap-0.5">
                        <span class="hidden sm:inline text-[10px] font-bold tracking-widest uppercase"
                              :class="currentStep === 2 ? 'text-primary' : 'opacity-40'">Step 2</span>
                        <span class="font-semibold text-sm" :class="currentStep === 2 ? 'text-primary' : ''">Location</span>
                    </span>
                </li>
                <li class="step transition-all duration-300"
                    :class="currentStep >= 3 ? 'step-primary' : ''"
                    @click="goToStep(3)" style="cursor:pointer">
                    <span class="flex flex-col items-center gap-0.5">
                        <span class="hidden sm:inline text-[10px] font-bold tracking-widest uppercase"
                              :class="currentStep === 3 ? 'text-primary' : 'opacity-40'">Step 3</span>
                        <span class="font-semibold text-sm" :class="currentStep === 3 ? 'text-primary' : ''">Specs</span>
                    </span>
                </li>
                <li class="step transition-all duration-300"
                    :class="currentStep >= 4 ? 'step-primary' : ''"
                    @click="goToStep(4)" style="cursor:pointer">
                    <span class="flex flex-col items-center gap-0.5">
                        <span class="hidden sm:inline text-[10px] font-bold tracking-widest uppercase"
                              :class="currentStep === 4 ? 'text-primary' : 'opacity-40'">Step 4</span>
                        <span class="font-semibold text-sm" :class="currentStep === 4 ? 'text-primary' : ''">Maintenance</span>
                    </span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('machines.update', $machine) }}" method="POST" id="machineEditForm" class="space-y-6"
          @submit.prevent="submitForm()">
        @csrf
        @method('PUT')

        <!-- STEP 1: Basic Information -->
        <div x-show="currentStep === 1"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-4"
             class="card bg-base-100 shadow-xl border border-base-300/50">
            <div class="card-body space-y-6">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Basic Information</h2>
                        <p class="text-sm opacity-60">Core identity and classification</p>
                    </div>
                    <span class="badge badge-error badge-outline badge-sm ml-auto">Required</span>
                </div>

                <div class="divider my-0"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-semibold">Machine Name <span class="text-error">*</span></span></label>
                        <input type="text" name="name"
                               class="input input-bordered @error('name') input-error @enderror"
                               value="{{ old('name', $machine->name) }}" required maxlength="255"/>
                        @error('name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Machine Code <span class="text-error">*</span></span></label>
                        <input type="text" name="machine_code"
                               class="input input-bordered @error('machine_code') input-error @enderror"
                               value="{{ old('machine_code', $machine->machine_code) }}" required maxlength="100"/>
                        @error('machine_code')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Machine Type <span class="text-error">*</span></span></label>
                        <select name="type" x-model="machineType"
                                class="select select-bordered @error('type') select-error @enderror" required>
                            <option value="" disabled>Select machine type</option>
                            @foreach(['Chiller' => '🧊', 'Generator' => '⚡', 'Elevator' => '🛗', 'Pump' => '💧', 'Cooling Tower' => '🏗️', 'HVAC' => '🌡️', 'Compressor' => '🔧', 'Boiler' => '🔥', 'Other' => '⚙️'] as $type => $icon)
                                <option value="{{ $type }}" {{ old('type', $machine->type) === $type ? 'selected' : '' }}>
                                    {{ $icon }} {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Status <span class="text-error">*</span></span></label>
                        <select name="status" class="select select-bordered @error('status') select-error @enderror" required>
                            @foreach(['active' => '🟢 Active', 'inactive' => '⚪ Inactive', 'maintenance' => '🟡 Maintenance', 'fault' => '🔴 Fault'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $machine->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Manufacturer</span>
                            <span class="label-text-alt badge badge-ghost badge-sm">Optional</span>
                        </label>
                        <input type="text" name="manufacturer"
                               class="input input-bordered @error('manufacturer') input-error @enderror"
                               value="{{ old('manufacturer', $machine->manufacturer) }}" maxlength="255"/>
                        @error('manufacturer')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Model Number</span>
                            <span class="label-text-alt badge badge-ghost badge-sm">Optional</span>
                        </label>
                        <input type="text" name="model_number"
                               class="input input-bordered @error('model_number') input-error @enderror"
                               value="{{ old('model_number', $machine->model_number) }}" maxlength="255"/>
                        @error('model_number')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Serial Number</span>
                            <span class="label-text-alt badge badge-ghost badge-sm">Optional</span>
                        </label>
                        <input type="text" name="serial_number"
                               class="input input-bordered @error('serial_number') input-error @enderror"
                               value="{{ old('serial_number', $machine->serial_number) }}" maxlength="255"/>
                        @error('serial_number')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">IP Address</span>
                            <span class="label-text-alt badge badge-ghost badge-sm">Optional</span>
                        </label>
                        <input type="text" name="ip_address" placeholder="e.g., 192.168.1.100"
                               class="input input-bordered @error('ip_address') input-error @enderror"
                               value="{{ old('ip_address', $machine->ip_address) }}"/>
                        @error('ip_address')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">Description</span>
                            <span class="label-text-alt opacity-60">Optional</span>
                        </label>
                        <textarea name="description" rows="3"
                                  class="textarea textarea-bordered @error('description') textarea-error @enderror"
                                  placeholder="Brief description of the machine's purpose and usage...">{{ old('description', $machine->description) }}</textarea>
                        @error('description')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control md:col-span-2">
                        <div class="flex items-center gap-4 p-4 rounded-xl border border-base-300 bg-base-200/50">
                            <div class="flex-1">
                                <p class="font-semibold">Critical Machine</p>
                                <p class="text-sm opacity-60">Mark as critical if this machine is essential to operations</p>
                            </div>
                            <input type="hidden" name="is_critical" value="0"/>
                            <input type="checkbox" name="is_critical" value="1"
                                   class="toggle toggle-error toggle-lg"
                                   {{ old('is_critical', $machine->is_critical) ? 'checked' : '' }}/>
                        </div>
                    </div>

                </div>

                <div class="flex justify-end pt-4 border-t border-base-300">
                    <button type="button" @click="nextStep()" class="btn btn-primary gap-2">
                        Next: Location
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- STEP 2: Location & Power -->
        <div x-show="currentStep === 2"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-4"
             class="card bg-base-100 shadow-xl border border-base-300/50">
            <div class="card-body space-y-6">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-info/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Location & Power</h2>
                        <p class="text-sm opacity-60">Building placement and electrical specs</p>
                    </div>
                    <span class="badge badge-ghost badge-outline badge-sm ml-auto">Optional</span>
                </div>

                <div class="divider my-0"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Building</span></label>
                        <select name="building_id" x-model="selectedBuilding" @change="loadFloors()"
                                class="select select-bordered @error('building_id') select-error @enderror">
                            <option value="">— No Building —</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}"
                                        {{ old('building_id', $machine->building_id) == $building->id ? 'selected' : '' }}>
                                    {{ $building->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('building_id')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Floor</span></label>
                        <select name="floor_id"
                                class="select select-bordered @error('floor_id') select-error @enderror"
                                :disabled="!selectedBuilding || floorsLoading">
                            <option value="">— No Floor —</option>
                            <template x-if="floorsLoading">
                                <option disabled>Loading floors...</option>
                            </template>
                            <template x-for="floor in floors" :key="floor.id">
                                <option :value="floor.id"
                                        :selected="floor.id == currentFloorId"
                                        x-text="floor.name"></option>
                            </template>
                        </select>
                        <label class="label">
                            <span class="label-text-alt opacity-50" x-show="!selectedBuilding && !floorsLoading">Select a building first</span>
                            <span class="label-text-alt opacity-50 flex items-center gap-1" x-show="floorsLoading">
                                <span class="loading loading-spinner loading-xs"></span> Loading floors...
                            </span>
                        </label>
                        @error('floor_id')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Installation Date</span></label>
                        <input type="date" name="installation_date"
                               class="input input-bordered @error('installation_date') input-error @enderror"
                               value="{{ old('installation_date', $machine->installation_date?->format('Y-m-d')) }}"/>
                        @error('installation_date')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Power Rating (kW)</span></label>
                        <input type="number" name="power_rating" step="0.01" min="0"
                               class="input input-bordered @error('power_rating') input-error @enderror"
                               value="{{ old('power_rating', $machine->power_rating) }}"/>
                        @error('power_rating')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Voltage (V)</span></label>
                        <input type="number" name="spec_voltage" step="0.01" min="0"
                               class="input input-bordered @error('spec_voltage') input-error @enderror"
                               value="{{ old('spec_voltage', $machine->spec_voltage) }}"/>
                        @error('spec_voltage')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                </div>

                <div class="flex justify-between pt-4 border-t border-base-300">
                    <button type="button" @click="prevStep()" class="btn btn-ghost gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back
                    </button>
                    <button type="button" @click="nextStep()" class="btn btn-primary gap-2">
                        Next: Specifications
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- STEP 3: Specifications -->
        <div x-show="currentStep === 3"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-4"
             class="card bg-base-100 shadow-xl border border-base-300/50">
            <div class="card-body space-y-6">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-secondary/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Specifications</h2>
                        <p class="text-sm opacity-60">Technical details for <span class="font-semibold" x-text="machineType || 'selected type'"></span></p>
                    </div>
                </div>

                <div class="divider my-0"></div>

                <!-- No type selected -->
                <div x-show="!machineType" class="flex flex-col items-center justify-center py-16 gap-4 opacity-50">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-center">
                        <p class="font-semibold">No machine type selected</p>
                        <p class="text-sm mt-1">Go back to Step 1 and select a machine type</p>
                    </div>
                    <button type="button" @click="currentStep = 1" class="btn btn-sm btn-ghost">← Go to Step 1</button>
                </div>

                <!-- Chiller -->
                <div x-show="machineType === 'Chiller'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2"><div class="alert alert-info"><span class="text-sm font-medium">🧊 Chiller specifications</span></div></div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Cooling Capacity (Tons)</span></label>
                        <input type="number" name="capacity_tons" step="0.01" min="0"
                               class="input input-bordered @error('capacity_tons') input-error @enderror"
                               value="{{ old('capacity_tons', $machine->capacity_tons) }}"/>
                        @error('capacity_tons')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                </div>

                <!-- Generator -->
                <div x-show="machineType === 'Generator'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2"><div class="alert alert-warning"><span class="text-sm font-medium">⚡ Generator specifications</span></div></div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Fuel Type</span></label>
                        <select name="fuel" class="select select-bordered @error('fuel') select-error @enderror">
                            <option value="">— Select Fuel —</option>
                            @foreach(['Diesel', 'Gasoline', 'Natural Gas', 'LPG'] as $fuelType)
                                <option value="{{ $fuelType }}" {{ old('fuel', $machine->fuel) === $fuelType ? 'selected' : '' }}>{{ $fuelType }}</option>
                            @endforeach
                        </select>
                        @error('fuel')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Autostart</span></label>
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-base-300 bg-base-200/40 h-12">
                            <input type="hidden" name="autostart" value="0"/>
                            <input type="checkbox" name="autostart" value="1"
                                   class="toggle toggle-primary"
                                   {{ old('autostart', $machine->autostart) ? 'checked' : '' }}/>
                            <span class="text-sm opacity-70">Enable automatic start on power failure</span>
                        </div>
                    </div>
                </div>

                <!-- Elevator -->
                <div x-show="machineType === 'Elevator'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2"><div class="alert alert-success"><span class="text-sm font-medium">🛗 Elevator specifications</span></div></div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Capacity (Persons)</span></label>
                        <input type="number" name="capacity_persons" min="0"
                               class="input input-bordered @error('capacity_persons') input-error @enderror"
                               value="{{ old('capacity_persons', $machine->capacity_persons) }}"/>
                        @error('capacity_persons')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Speed (m/s)</span></label>
                        <input type="number" name="speed_m_s" step="0.01" min="0"
                               class="input input-bordered @error('speed_m_s') input-error @enderror"
                               value="{{ old('speed_m_s', $machine->speed_m_s) }}"/>
                        @error('speed_m_s')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                </div>

                <!-- Pump -->
                <div x-show="machineType === 'Pump'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2"><div class="alert alert-info"><span class="text-sm font-medium">💧 Pump specifications</span></div></div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Flow Rate (LPM)</span></label>
                        <input type="number" name="flow_lpm" min="0"
                               class="input input-bordered @error('flow_lpm') input-error @enderror"
                               value="{{ old('flow_lpm', $machine->flow_lpm) }}"/>
                        @error('flow_lpm')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Head (m)</span></label>
                        <input type="number" name="head_m" step="0.01" min="0"
                               class="input input-bordered @error('head_m') input-error @enderror"
                               value="{{ old('head_m', $machine->head_m) }}"/>
                        @error('head_m')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                </div>

                <!-- Cooling Tower -->
                <div x-show="machineType === 'Cooling Tower'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2"><div class="alert alert-info"><span class="text-sm font-medium">🏗️ Cooling Tower specifications</span></div></div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Capacity (Tons)</span></label>
                        <input type="number" name="capacity_tons" step="0.01" min="0"
                               class="input input-bordered @error('capacity_tons') input-error @enderror"
                               value="{{ old('capacity_tons', $machine->capacity_tons) }}"/>
                        @error('capacity_tons')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Number of Cells</span></label>
                        <input type="number" name="cells" min="0"
                               class="input input-bordered @error('cells') input-error @enderror"
                               value="{{ old('cells', $machine->cells) }}"/>
                        @error('cells')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-semibold">Makeup Valve</span></label>
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-base-300 bg-base-200/40">
                            <input type="hidden" name="makeup_valve" value="0"/>
                            <input type="checkbox" name="makeup_valve" value="1"
                                   class="toggle toggle-primary"
                                   {{ old('makeup_valve', $machine->makeup_valve) ? 'checked' : '' }}/>
                            <span class="text-sm opacity-70">Has automatic makeup water valve</span>
                        </div>
                    </div>
                </div>

                <!-- HVAC / Compressor / Boiler / Other -->
                <div x-show="['HVAC','Compressor','Boiler','Other'].includes(machineType)" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2"><div class="alert"><span class="text-sm font-medium">⚙️ General specifications</span></div></div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Fuel / Energy Source</span></label>
                        <input type="text" name="fuel" placeholder="e.g., Electric, Gas, Diesel"
                               class="input input-bordered @error('fuel') input-error @enderror"
                               value="{{ old('fuel', $machine->fuel) }}" maxlength="255"/>
                        @error('fuel')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                </div>

                <div class="flex justify-between pt-4 border-t border-base-300">
                    <button type="button" @click="prevStep()" class="btn btn-ghost gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back
                    </button>
                    <button type="button" @click="nextStep()" class="btn btn-primary gap-2">
                        Next: Maintenance
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- STEP 4: Maintenance Schedule -->
        <div x-show="currentStep === 4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-4"
             class="card bg-base-100 shadow-xl border border-base-300/50">
            <div class="card-body space-y-6">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-warning/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Maintenance Schedule</h2>
                        <p class="text-sm opacity-60">Preventive maintenance tracking</p>
                    </div>
                    <span class="badge badge-ghost badge-outline badge-sm ml-auto">Optional</span>
                </div>

                <div class="divider my-0"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Last Maintenance Date</span></label>
                        <input type="date" name="last_maintenance_date"
                               x-model="lastMaintenance" @change="calcNextMaintenance()"
                               class="input input-bordered @error('last_maintenance_date') input-error @enderror"
                               value="{{ old('last_maintenance_date', $machine->last_maintenance_date?->format('Y-m-d')) }}"/>
                        @error('last_maintenance_date')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Maintenance Interval (Days)</span></label>
                        <input type="number" name="maintenance_interval_days" min="1"
                               x-model="maintenanceInterval" @input="calcNextMaintenance()"
                               class="input input-bordered @error('maintenance_interval_days') input-error @enderror"
                               value="{{ old('maintenance_interval_days', $machine->maintenance_interval_days) }}"/>
                        @error('maintenance_interval_days')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @else
                            <label class="label"><span class="label-text-alt opacity-50">Auto-calculates next maintenance date</span></label>
                        @enderror
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-semibold">Next Maintenance Date</span></label>
                        <div class="relative">
                            <input type="date" name="next_maintenance_date"
                                   x-model="nextMaintenance"
                                   class="input input-bordered w-full @error('next_maintenance_date') input-error @enderror"
                                   value="{{ old('next_maintenance_date', $machine->next_maintenance_date?->format('Y-m-d')) }}"/>
                            <div x-show="nextMaintenance" class="absolute right-3 top-1/2 -translate-y-1/2">
                                <span class="badge badge-success badge-sm gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Set
                                </span>
                            </div>
                        </div>
                        @error('next_maintenance_date')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                </div>

                <div x-show="nextMaintenance" class="alert alert-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-sm">Maintenance Schedule Set</p>
                        <p class="text-xs opacity-80">
                            Next maintenance: <span class="font-bold" x-text="formatDate(nextMaintenance)"></span>
                            <template x-if="maintenanceInterval">
                                <span> · every <span x-text="maintenanceInterval"></span> days</span>
                            </template>
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between pt-4 border-t border-base-300">
                    <button type="button" @click="prevStep()" class="btn btn-ghost gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back
                    </button>
                    <div class="flex gap-3">
                        <a href="{{ route('machines.show', $machine) }}" class="btn btn-ghost gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary gap-2" :disabled="submitting">
                            <span x-show="!submitting">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span x-show="submitting" class="loading loading-spinner loading-sm"></span>
                            <span x-text="submitting ? 'Saving...' : 'Save Changes'"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </form>

    <!-- Alert Thresholds — separate form/route (AlertService), independent of the wizard above -->
    <div class="card bg-base-100 shadow-xl border border-base-300/50">
        <div class="card-body space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-error/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
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
                                    <td><input type="number" step="any" name="thresholds[{{ $metric }}][warning_min]" value="{{ old('thresholds.'.$metric.'.warning_min', $threshold?->warning_min) }}" class="input input-bordered input-sm w-28"/></td>
                                    <td><input type="number" step="any" name="thresholds[{{ $metric }}][warning_max]" value="{{ old('thresholds.'.$metric.'.warning_max', $threshold?->warning_max) }}" class="input input-bordered input-sm w-28"/></td>
                                    <td><input type="number" step="any" name="thresholds[{{ $metric }}][critical_min]" value="{{ old('thresholds.'.$metric.'.critical_min', $threshold?->critical_min) }}" class="input input-bordered input-sm w-28"/></td>
                                    <td><input type="number" step="any" name="thresholds[{{ $metric }}][critical_max]" value="{{ old('thresholds.'.$metric.'.critical_max', $threshold?->critical_max) }}" class="input input-bordered input-sm w-28"/></td>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Thresholds
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function machineEditForm() {
    return {
        currentStep: 1,
        machineType: '{{ old('type', $machine->type) }}',
        selectedBuilding: '{{ old('building_id', $machine->building_id) }}',
        currentFloorId: {{ old('floor_id', $machine->floor_id) ?? 'null' }},
        floors: @json($floors),
        floorsLoading: false,
        lastMaintenance: '{{ old('last_maintenance_date', $machine->last_maintenance_date?->format('Y-m-d')) }}',
        maintenanceInterval: '{{ old('maintenance_interval_days', $machine->maintenance_interval_days) }}',
        nextMaintenance: '{{ old('next_maintenance_date', $machine->next_maintenance_date?->format('Y-m-d')) }}',
        submitting: false,

        nextStep() { if (this.currentStep < 4) this.currentStep++; },
        prevStep() { if (this.currentStep > 1) this.currentStep--; },
        goToStep(step) { this.currentStep = step; }, // edit mode: all steps freely navigable

        async loadFloors() {
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

        submitForm() {
            this.submitting = true;
            document.getElementById('machineEditForm').submit();
        },

        init() {
            // On validation error, jump to the offending step
            @if($errors->any())
                @if($errors->hasAny(['name','machine_code','type','status','manufacturer','model_number','serial_number','ip_address','description','is_critical']))
                    this.currentStep = 1;
                @elseif($errors->hasAny(['building_id','floor_id','installation_date','power_rating','spec_voltage']))
                    this.currentStep = 2;
                @elseif($errors->hasAny(['capacity_tons','fuel','autostart','capacity_persons','speed_m_s','flow_lpm','head_m','cells','makeup_valve']))
                    this.currentStep = 3;
                @elseif($errors->hasAny(['last_maintenance_date','maintenance_interval_days','next_maintenance_date']))
                    this.currentStep = 4;
                @endif
            @endif
        }
    }
}
</script>
@endpush
@endsection
