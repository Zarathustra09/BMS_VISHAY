@extends('layouts.app')

@section('title', 'Create Building')

@section('breadcrumbs')
    <li><a href="{{ route('buildings.index') }}">Buildings</a></li>
    <li>Create New Building</li>
@endsection

@section('back', route('buildings.index'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold">Create New Building</h1>
            <p class="text-sm opacity-70 mt-1">Add a new facility to your infrastructure</p>
        </div>
    </div>

    <x-form.errors />

    @php
        $statusOptions = [
            'Operational' => 'Operational',
            'Under Construction' => 'Under Construction',
            'Maintenance' => 'Maintenance',
            'Inactive' => 'Inactive',
        ];
        $typeOptions = [
            'Production Facility' => 'Production Facility',
            'Warehouse' => 'Warehouse',
            'Office Building' => 'Office Building',
            'R&D Center' => 'R&D Center',
            'Distribution Center' => 'Distribution Center',
        ];
    @endphp

    <!-- Form Card -->
    <div class="card bg-base-100 shadow-xl border border-base-300/50">
        <div class="card-body">
            <form action="{{ route('buildings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="buildingForm">
                @csrf

                <!-- Image Upload Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-secondary/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold">Building Image</h2>
                        <span class="badge badge-ghost">Optional</span>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="label py-0">
                            <span class="text-sm font-medium">Upload Building Photo</span>
                            <span class="text-xs opacity-60 ml-auto">Max 2MB (JPG, PNG, GIF)</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <div class="avatar">
                                <div class="w-24 h-24 rounded-lg bg-base-200 flex items-center justify-center" id="imagePreview">
                                    <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="image" accept="image/*"
                                       class="file-input w-full @error('image') file-input-error @enderror"
                                       id="imageInput" />
                                @error('image')
                                    <p class="text-error text-xs mt-0.5">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Basic Information Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold">Basic Information</h2>
                        <span class="badge badge-error badge-sm">Required</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input name="name" label="Building Name" required span
                                      placeholder="e.g., Main Production Facility" maxlength="255"
                                      help="Enter a descriptive name" />

                        <x-form.input name="code" label="Building Code" required
                                      placeholder="e.g., BLD-001" maxlength="50"
                                      help="Unique identifier" />

                        <x-form.select name="status" label="Status" required
                                        placeholder="Select status" :options="$statusOptions" />

                        <x-form.select name="type" label="Building Type" required span
                                        placeholder="Select building type" :options="$typeOptions" />
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Location Information Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-info/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold">Location Information</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input name="address" label="Street Address" span
                                      placeholder="e.g., 123 Industrial Blvd" maxlength="500" />

                        <x-form.input name="city" label="City" placeholder="e.g., Springfield" maxlength="100" />

                        <x-form.input name="state" label="State" placeholder="e.g., IL" maxlength="100" />
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Emergency Contact Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-error/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold">Emergency Contact</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input name="emergency_contact_name" label="Contact Name"
                                      placeholder="e.g., John Doe" maxlength="255" />

                        <x-form.input type="tel" name="emergency_contact_phone" label="Phone Number"
                                      placeholder="e.g., (555) 123-4567" maxlength="20" />
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Additional Notes -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-warning/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold">Additional Notes</h2>
                    </div>

                    <x-form.textarea name="notes" label="Notes" :rows="5" maxlength="1000"
                                      placeholder="Any additional information about this building..."
                                      help="Optional — max 1000 characters"
                                      class="h-32" />
                </div>

                <!-- Form Actions -->
                <x-form.actions :cancel-href="route('buildings.index')" submit-label="Create Building" />
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Image preview functionality
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-24 h-24 rounded-lg object-cover" />`;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection
