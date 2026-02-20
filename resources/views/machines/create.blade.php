@extends('layouts.app')

@section('title', 'Create Machine')

@section('breadcrumbs')
    <li><a href="{{ route('machines.index') }}">Machines</a></li>
    <li>Create New Machine</li>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .step-card-enter { opacity: 0; transform: translateX(20px); }
    .step-card-enter-active { transition: opacity 0.25s ease, transform 0.25s ease; }
    .step-card-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
    .step-card-leave-to { opacity: 0; transform: translateX(-20px); }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="machineForm()" x-cloak>

    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('machines.index') }}" class="btn btn-ghost btn-circle">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold">Create New Machine</h1>
            <p class="text-sm opacity-70 mt-1">Register a new machine to your facility infrastructure</p>
        </div>
    </div>

    {{-- ── Validation Error Banner ── --}}
    @if($errors->any())
    <div class="alert alert-error shadow-lg">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-bold">Please fix the following errors before submitting:</p>
            <ul class="list-disc list-inside text-sm mt-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- ── Sticky Progress Steps ── -->
    <div class="sticky top-0 z-30">
        <div class="absolute inset-0 bg-base-200/90 backdrop-blur-md rounded-2xl border border-base-300/60 shadow-lg"></div>
        <div class="relative px-6 py-4">

            <ul class="steps steps-horizontal w-full">
                <li class="step transition-all duration-300"
                    :class="currentStep >= 1 ? 'step-primary' : ''"
                    @click="goToStep(1)"
                    :style="currentStep >= 1 ? 'cursor:pointer' : 'cursor:default'">
                    <span class="flex flex-col items-center gap-0.5">
                        <span class="hidden sm:inline text-[10px] font-bold tracking-widest uppercase"
                              :class="currentStep === 1 ? 'text-primary' : 'opacity-40'">Step 1</span>
                        <span class="font-semibold text-sm" :class="currentStep === 1 ? 'text-primary' : ''">Basic Info</span>
                    </span>
                </li>
                <li class="step transition-all duration-300"
                    :class="currentStep >= 2 ? 'step-primary' : ''"
                    @click="goToStep(2)"
                    :style="currentStep >= 2 ? 'cursor:pointer' : 'cursor:default'">
                    <span class="flex flex-col items-center gap-0.5">
                        <span class="hidden sm:inline text-[10px] font-bold tracking-widest uppercase"
                              :class="currentStep === 2 ? 'text-primary' : 'opacity-40'">Step 2</span>
                        <span class="font-semibold text-sm" :class="currentStep === 2 ? 'text-primary' : ''">Location</span>
                    </span>
                </li>
                <li class="step transition-all duration-300"
                    :class="currentStep >= 3 ? 'step-primary' : ''"
                    @click="goToStep(3)"
                    :style="currentStep >= 3 ? 'cursor:pointer' : 'cursor:default'">
                    <span class="flex flex-col items-center gap-0.5">
                        <span class="hidden sm:inline text-[10px] font-bold tracking-widest uppercase"
                              :class="currentStep === 3 ? 'text-primary' : 'opacity-40'">Step 3</span>
                        <span class="font-semibold text-sm" :class="currentStep === 3 ? 'text-primary' : ''">Specs</span>
                    </span>
                </li>
                <li class="step transition-all duration-300"
                    :class="currentStep >= 4 ? 'step-primary' : ''"
                    @click="goToStep(4)"
                    :style="currentStep >= 4 ? 'cursor:pointer' : 'cursor:default'">
                    <span class="flex flex-col items-center gap-0.5">
                        <span class="hidden sm:inline text-[10px] font-bold tracking-widest uppercase"
                              :class="currentStep === 4 ? 'text-primary' : 'opacity-40'">Step 4</span>
                        <span class="font-semibold text-sm" :class="currentStep === 4 ? 'text-primary' : ''">Maintenance</span>
                    </span>
                </li>
            </ul>

        </div>
    </div>

    <form action="{{ route('machines.store') }}" method="POST" id="machineForm" class="space-y-6"
          @submit.prevent="submitForm()">
        @csrf

        <!-- ── STEP 1: Basic Information ── -->
        <div class="card bg-base-100 shadow-xl border border-base-300/50"
             :class="currentStep === 1 ? '' : 'hidden'">
            <div class="card-body space-y-6">

                <!-- Section Header -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Basic Information</h2>
                        <p class="text-sm opacity-60">Core identity and classification of the machine</p>
                    </div>
                    <span class="badge badge-error badge-sm ml-auto">Required</span>
                </div>

                <div class="divider my-0"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Machine Name -->
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">Machine Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="name" placeholder="e.g., Chiller Unit A"
                               class="input input-bordered @error('name') input-error @enderror"
                               value="{{ old('name') }}" required maxlength="255"/>
                        @error('name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @else
                            <label class="label"><span class="label-text-alt opacity-50">Enter a descriptive name for the machine</span></label>
                        @enderror
                    </div>

                    <!-- Machine Code -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Machine Code <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="machine_code" placeholder="e.g., MCH-001"
                               class="input input-bordered @error('machine_code') input-error @enderror"
                               value="{{ old('machine_code') }}" required maxlength="100"/>
                        @error('machine_code')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @else
                            <label class="label"><span class="label-text-alt opacity-50">Unique identifier code</span></label>
                        @enderror
                    </div>

                    <!-- Machine Type -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Machine Type <span class="text-error">*</span></span>
                        </label>
                        <select name="type" x-model="machineType"
                                class="select select-bordered @error('type') select-error @enderror" required>
                            <option value="" disabled selected>Select machine type</option>
                            <option value="Chiller"          {{ old('type') === 'Chiller'           ? 'selected' : '' }}>🧊 Chiller</option>
                            <option value="Generator"        {{ old('type') === 'Generator'         ? 'selected' : '' }}>⚡ Generator</option>
                            <option value="Elevator"         {{ old('type') === 'Elevator'          ? 'selected' : '' }}>🛗 Elevator</option>
                            <option value="Pump"             {{ old('type') === 'Pump'              ? 'selected' : '' }}>💧 Pump</option>
                            <option value="Cooling Tower"    {{ old('type') === 'Cooling Tower'     ? 'selected' : '' }}>🏗️ Cooling Tower</option>
                            <option value="HVAC"             {{ old('type') === 'HVAC'              ? 'selected' : '' }}>🌡️ HVAC</option>
                            <option value="Compressor"       {{ old('type') === 'Compressor'        ? 'selected' : '' }}>🔧 Compressor</option>
                            <option value="Boiler"           {{ old('type') === 'Boiler'            ? 'selected' : '' }}>🔥 Boiler</option>
                            <option value="Other"            {{ old('type') === 'Other'             ? 'selected' : '' }}>⚙️ Other</option>
                        </select>
                        @error('type')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Status <span class="text-error">*</span></span>
                        </label>
                        <select name="status" class="select select-bordered @error('status') select-error @enderror" required>
                            <option value="" disabled selected>Select status</option>
                            <option value="active"      {{ old('status') === 'active'      ? 'selected' : '' }}>🟢 Active</option>
                            <option value="inactive"    {{ old('status') === 'inactive'    ? 'selected' : '' }}>⚪ Inactive</option>
                            <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>🟡 Maintenance</option>
                            <option value="fault"       {{ old('status') === 'fault'       ? 'selected' : '' }}>🔴 Fault</option>
                        </select>
                        @error('status')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Manufacturer -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Manufacturer</span>
                            <span class="label-text-alt badge badge-ghost badge-sm">Optional</span>
                        </label>
                        <input type="text" name="manufacturer" placeholder="e.g., Carrier, Trane, Siemens"
                               class="input input-bordered @error('manufacturer') input-error @enderror"
                               value="{{ old('manufacturer') }}" maxlength="255"/>
                        @error('manufacturer')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Model Number -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Model Number</span>
                            <span class="label-text-alt badge badge-ghost badge-sm">Optional</span>
                        </label>
                        <input type="text" name="model_number" placeholder="e.g., 30XA-400"
                               class="input input-bordered @error('model_number') input-error @enderror"
                               value="{{ old('model_number') }}" maxlength="255"/>
                        @error('model_number')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Serial Number -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Serial Number</span>
                            <span class="label-text-alt badge badge-ghost badge-sm">Optional</span>
                        </label>
                        <input type="text" name="serial_number" placeholder="e.g., SN-2024-XYZ-001"
                               class="input input-bordered @error('serial_number') input-error @enderror"
                               value="{{ old('serial_number') }}" maxlength="255"/>
                        @error('serial_number')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- IP Address -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">IP Address</span>
                            <span class="label-text-alt badge badge-ghost badge-sm">Optional</span>
                        </label>
                        <input type="text" name="ip_address" placeholder="e.g., 192.168.1.100"
                               class="input input-bordered @error('ip_address') input-error @enderror"
                               value="{{ old('ip_address') }}"/>
                        @error('ip_address')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @else
                            <label class="label"><span class="label-text-alt opacity-50">For network-connected machines</span></label>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">Description</span>
                            <span class="label-text-alt opacity-60">Optional</span>
                        </label>
                        <textarea name="description" rows="3"
                                  class="textarea textarea-bordered @error('description') textarea-error @enderror"
                                  placeholder="Brief description of the machine's purpose and usage...">{{ old('description') }}</textarea>
                        @error('description')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Is Critical Toggle -->
                    <div class="form-control md:col-span-2">
                        <div class="flex items-center gap-4 p-4 rounded-xl border border-base-300 bg-base-200/50">
                            <div class="flex-1">
                                <p class="font-semibold">Critical Machine</p>
                                <p class="text-sm opacity-60">Mark as critical if this machine is essential to operations</p>
                            </div>
                            <input type="hidden" name="is_critical" value="0"/>
                            <input type="checkbox" name="is_critical" value="1"
                                   class="toggle toggle-error toggle-lg"
                                   {{ old('is_critical') ? 'checked' : '' }}/>
                        </div>
                    </div>
                </div>

                <!-- Step Navigation -->
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

        <!-- ── STEP 2: Location ── -->
        <div class="card bg-base-100 shadow-xl border border-base-300/50"
             :class="currentStep === 2 ? '' : 'hidden'">
            <div class="card-body space-y-6">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-info/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Location</h2>
                        <p class="text-sm opacity-60">Assign building and floor placement</p>
                    </div>
                    <span class="badge badge-ghost badge-sm ml-auto">Optional</span>
                </div>

                <div class="divider my-0"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Building -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Building</span>
                        </label>
                        <select name="building_id" x-model="selectedBuilding"
                                @change="loadFloors()"
                                class="select select-bordered @error('building_id') select-error @enderror">
                            <option value="">— No Building —</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}" {{ old('building_id') == $building->id ? 'selected' : '' }}>
                                    {{ $building->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('building_id')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Floor -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Floor</span>
                        </label>
                        <select name="floor_id"
                                class="select select-bordered @error('floor_id') select-error @enderror"
                                :disabled="!selectedBuilding || floorsLoading">
                            <option value="">— No Floor —</option>
                            <template x-if="floorsLoading">
                                <option disabled>Loading floors...</option>
                            </template>
                            <template x-for="floor in floors" :key="floor.id">
                                <option :value="floor.id" x-text="floor.name"></option>
                            </template>
                        </select>
                        <label class="label">
                            <span class="label-text-alt opacity-50" x-show="!selectedBuilding">Select a building first</span>
                            <span class="label-text-alt opacity-50 flex items-center gap-1" x-show="floorsLoading">
                                <span class="loading loading-spinner loading-xs"></span> Loading floors...
                            </span>
                        </label>
                        @error('floor_id')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Installation Date -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Installation Date</span>
                        </label>
                        <input type="date" name="installation_date"
                               class="input input-bordered @error('installation_date') input-error @enderror"
                               value="{{ old('installation_date') }}"/>
                        @error('installation_date')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Power Rating -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Power Rating (kW)</span>
                        </label>
                        <input type="number" name="power_rating" step="0.01" min="0"
                               placeholder="e.g., 75.50"
                               class="input input-bordered @error('power_rating') input-error @enderror"
                               value="{{ old('power_rating') }}"/>
                        @error('power_rating')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Spec Voltage -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Voltage (V)</span>
                        </label>
                        <input type="number" name="spec_voltage" step="0.01" min="0"
                               placeholder="e.g., 380.00"
                               class="input input-bordered @error('spec_voltage') input-error @enderror"
                               value="{{ old('spec_voltage') }}"/>
                        @error('spec_voltage')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                </div>

                <!-- Step Navigation -->
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

        <!-- ── STEP 3: Type-Specific Specifications ── -->
        <div class="card bg-base-100 shadow-xl border border-base-300/50"
             :class="currentStep === 3 ? '' : 'hidden'">
            <div class="card-body space-y-6">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-secondary/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Specifications</h2>
                        <p class="text-sm opacity-60">Technical details — fields shown are relevant to the selected type</p>
                    </div>
                </div>

                <div class="divider my-0"></div>

                <!-- Empty state when no type selected -->
                <div x-show="!machineType" class="flex flex-col items-center justify-center py-12 gap-3 opacity-50">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                    </svg>
                    <p class="font-medium">Select a Machine Type in Step 1 to see relevant spec fields</p>
                </div>

                <!-- Chiller specs -->
                <div x-show="machineType === 'Chiller'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <div class="alert alert-info alert-soft mb-2">
                            <span class="text-sm">Chiller-specific specifications</span>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Cooling Capacity (Tons)</span></label>
                        <input type="number" name="capacity_tons" step="0.01" min="0" placeholder="e.g., 200.00"
                               class="input input-bordered @error('capacity_tons') input-error @enderror"
                               value="{{ old('capacity_tons') }}"/>
                        @error('capacity_tons')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                </div>

                <!-- Generator specs -->
                <div x-show="machineType === 'Generator'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <div class="alert alert-warning alert-soft mb-2">
                            <span class="text-sm">Generator-specific specifications</span>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Fuel Type</span></label>
                        <select name="fuel" class="select select-bordered @error('fuel') select-error @enderror">
                            <option value="">— Select Fuel —</option>
                            <option value="Diesel"   {{ old('fuel') === 'Diesel'   ? 'selected' : '' }}>Diesel</option>
                            <option value="Gasoline" {{ old('fuel') === 'Gasoline' ? 'selected' : '' }}>Gasoline</option>
                            <option value="Natural Gas" {{ old('fuel') === 'Natural Gas' ? 'selected' : '' }}>Natural Gas</option>
                            <option value="LPG"      {{ old('fuel') === 'LPG'      ? 'selected' : '' }}>LPG</option>
                        </select>
                        @error('fuel')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Autostart</span></label>
                        <div class="flex items-center gap-3 p-3 rounded-lg border border-base-300 bg-base-200/40">
                            <input type="hidden" name="autostart" value="0"/>
                            <input type="checkbox" name="autostart" value="1"
                                   class="toggle toggle-primary"
                                   {{ old('autostart') ? 'checked' : '' }}/>
                            <span class="text-sm opacity-70">Enable automatic start on power failure</span>
                        </div>
                        @error('autostart')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                </div>

                <!-- Elevator specs -->
                <div x-show="machineType === 'Elevator'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <div class="alert alert-success alert-soft mb-2">
                            <span class="text-sm">Elevator-specific specifications</span>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Capacity (Persons)</span></label>
                        <input type="number" name="capacity_persons" min="0" placeholder="e.g., 10"
                               class="input input-bordered @error('capacity_persons') input-error @enderror"
                               value="{{ old('capacity_persons') }}"/>
                        @error('capacity_persons')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Speed (m/s)</span></label>
                        <input type="number" name="speed_m_s" step="0.01" min="0" placeholder="e.g., 1.50"
                               class="input input-bordered @error('speed_m_s') input-error @enderror"
                               value="{{ old('speed_m_s') }}"/>
                        @error('speed_m_s')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                </div>

                <!-- Pump specs -->
                <div x-show="machineType === 'Pump'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <div class="alert alert-info alert-soft mb-2">
                            <span class="text-sm">Pump-specific specifications</span>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Flow Rate (LPM)</span></label>
                        <input type="number" name="flow_lpm" min="0" placeholder="e.g., 500"
                               class="input input-bordered @error('flow_lpm') input-error @enderror"
                               value="{{ old('flow_lpm') }}"/>
                        @error('flow_lpm')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Head (m)</span></label>
                        <input type="number" name="head_m" step="0.01" min="0" placeholder="e.g., 30.00"
                               class="input input-bordered @error('head_m') input-error @enderror"
                               value="{{ old('head_m') }}"/>
                        @error('head_m')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                </div>

                <!-- Cooling Tower specs -->
                <div x-show="machineType === 'Cooling Tower'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <div class="alert alert-info alert-soft mb-2">
                            <span class="text-sm">Cooling Tower-specific specifications</span>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Capacity (Tons)</span></label>
                        <input type="number" name="capacity_tons" step="0.01" min="0" placeholder="e.g., 500.00"
                               class="input input-bordered @error('capacity_tons') input-error @enderror"
                               value="{{ old('capacity_tons') }}"/>
                        @error('capacity_tons')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Number of Cells</span></label>
                        <input type="number" name="cells" min="0" placeholder="e.g., 4"
                               class="input input-bordered @error('cells') input-error @enderror"
                               value="{{ old('cells') }}"/>
                        @error('cells')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-semibold">Makeup Valve</span></label>
                        <div class="flex items-center gap-3 p-3 rounded-lg border border-base-300 bg-base-200/40">
                            <input type="hidden" name="makeup_valve" value="0"/>
                            <input type="checkbox" name="makeup_valve" value="1"
                                   class="toggle toggle-primary"
                                   {{ old('makeup_valve') ? 'checked' : '' }}/>
                            <span class="text-sm opacity-70">Has automatic makeup water valve</span>
                        </div>
                        @error('makeup_valve')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                </div>

                <!-- Generic / HVAC / Compressor / Boiler / Other specs -->
                <div x-show="['HVAC','Compressor','Boiler','Other'].includes(machineType)" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <div class="alert alert-ghost alert-soft mb-2">
                            <span class="text-sm">General specifications for this machine type</span>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Fuel / Energy Source</span></label>
                        <input type="text" name="fuel" placeholder="e.g., Electric, Gas, Diesel"
                               class="input input-bordered @error('fuel') input-error @enderror"
                               value="{{ old('fuel') }}" maxlength="100"/>
                        @error('fuel')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>
                </div>

                <!-- Step Navigation -->
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

        <!-- ── STEP 4: Maintenance Schedule ── -->
        <div class="card bg-base-100 shadow-xl border border-base-300/50"
             :class="currentStep === 4 ? '' : 'hidden'">
            <div class="card-body space-y-6">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-warning/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Maintenance Schedule</h2>
                        <p class="text-sm opacity-60">Set up preventive maintenance tracking</p>
                    </div><span class="badge badge-ghost badge-sm ml-auto">Optional</span>
                </div>

                <div class="divider my-0"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Last Maintenance Date -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Last Maintenance Date</span>
                        </label>
                        <input type="date" name="last_maintenance_date"
                               x-model="lastMaintenance"
                               @change="calcNextMaintenance()"
                               class="input input-bordered @error('last_maintenance_date') input-error @enderror"
                               value="{{ old('last_maintenance_date') }}"/>
                        @error('last_maintenance_date')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Maintenance Interval -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Maintenance Interval (Days)</span>
                        </label>
                        <input type="number" name="maintenance_interval_days" min="1"
                               x-model="maintenanceInterval"
                               @input="calcNextMaintenance()"
                               placeholder="e.g., 90"
                               class="input input-bordered @error('maintenance_interval_days') input-error @enderror"
                               value="{{ old('maintenance_interval_days') }}"/>
                        @error('maintenance_interval_days')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @else
                            <label class="label"><span class="label-text-alt opacity-50">Auto-calculates next maintenance date</span></label>
                        @enderror
                    </div>

                    <!-- Next Maintenance Date -->
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">Next Maintenance Date</span>
                        </label>
                        <div class="relative">
                            <input type="date" name="next_maintenance_date"
                                   x-model="nextMaintenance"
                                   class="input input-bordered w-full @error('next_maintenance_date') input-error @enderror"
                                   value="{{ old('next_maintenance_date') }}"/>
                            <div x-show="nextMaintenance"
                                 class="absolute right-3 top-1/2 -translate-y-1/2">
                                <span class="badge badge-success badge-sm gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Auto-set
                                </span>
                            </div>
                        </div>
                        @error('next_maintenance_date')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @else
                            <label class="label"><span class="label-text-alt opacity-50">Calculated automatically or set manually</span></label>
                        @enderror
                    </div>
                </div>

                <!-- Summary Card -->
                <div x-show="nextMaintenance" class="alert alert-success alert-soft">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-sm">Maintenance Schedule Set</p>
                        <p class="text-xs opacity-80">
                            Next maintenance scheduled for
                            <span class="font-bold" x-text="formatDate(nextMaintenance)"></span>
                            <template x-if="maintenanceInterval">
                                <span> · every <span x-text="maintenanceInterval"></span> days</span>
                            </template>
                        </p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between pt-4 border-t border-base-300">
                    <button type="button" @click="prevStep()" class="btn btn-ghost gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back
                    </button>
                    <div class="flex gap-3">
                        <a href="{{ route('machines.index') }}" class="btn btn-ghost gap-2">
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
                            <span x-text="submitting ? 'Creating...' : 'Create Machine'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div></form>
</div>

@push('scripts')
<script>
function machineForm() {
    return {
        currentStep: 1,
        machineType: '{{ old('type', '') }}',
        selectedBuilding: '{{ old('building_id', '') }}',
        floors: [],
        floorsLoading: false,
        lastMaintenance: '{{ old('last_maintenance_date', '') }}',
        maintenanceInterval: '{{ old('maintenance_interval_days', '') }}',
        nextMaintenance: '{{ old('next_maintenance_date') }}',
        submitting: false,

        nextStep() { if (this.currentStep < 4) this.currentStep++; },
        prevStep() { if (this.currentStep > 1) this.currentStep--; },
        goToStep(step) { if (step <= this.currentStep) this.currentStep = step; },

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
            document.getElementById('machineForm').submit();
        },

        init() {
            if (this.selectedBuilding) this.loadFloors();

            @if($errors->any())
                // Jump to the step that contains the first error
                @if($errors->hasAny(['name', 'machine_code', 'type', 'status', 'manufacturer', 'model_number', 'serial_number', 'ip_address', 'description', 'is_critical']))
                    this.currentStep = 1;
                @elseif($errors->hasAny(['building_id', 'floor_id', 'installation_date', 'power_rating', 'spec_voltage']))
                    this.currentStep = 2;
                @elseif($errors->hasAny(['capacity_tons', 'fuel', 'autostart', 'capacity_persons', 'speed_m_s', 'flow_lpm', 'head_m', 'cells', 'makeup_valve']))
                    this.currentStep = 3;
                @elseif($errors->hasAny(['last_maintenance_date', 'maintenance_interval_days', 'next_maintenance_date']))
                    this.currentStep = 4;
                @endif
                // Mark all previous steps as visited so the user can navigate back
                @php
                    $stepWithError = $errors->hasAny(['name','machine_code','type','status','manufacturer','model_number','serial_number','ip_address','description','is_critical']) ? 1
                        : ($errors->hasAny(['building_id','floor_id','installation_date','power_rating','spec_voltage']) ? 2
                        : ($errors->hasAny(['capacity_tons','fuel','autostart','capacity_persons','speed_m_s','flow_lpm','head_m','cells','makeup_valve']) ? 3 : 4));
                @endphp
                // Allow clicking back to any step up to the error step
                this.currentStep = {{ $stepWithError }};
            @endif
        }
    }
}
</script>
@endpush
@endsection
