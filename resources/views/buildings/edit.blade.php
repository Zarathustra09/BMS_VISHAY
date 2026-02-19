@extends('layouts.app')

@section('title', 'Edit Building - ' . ($building->name ?? 'Building'))

@section('breadcrumbs')
    <li><a href="{{ route('buildings.index') }}">Buildings</a></li>
    <li><a href="{{ route('buildings.show', $building->id ?? 0) }}">{{ $building->name ?? 'Building' }}</a></li>
    <li>Edit</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('buildings.show', $building->id ?? 0) }}" class="btn btn-ghost btn-circle">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold">Edit Building</h1>
            <p class="text-sm opacity-70 mt-1">Update information for {{ $building->name ?? 'this building' }}</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Form Card -->
    <div class="card bg-base-100 shadow-xl border border-base-300/50">
        <div class="card-body">
            <form action="{{ route('buildings.update', $building->id ?? 0) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

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

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Update Building Photo</span>
                            <span class="label-text-alt">Max 2MB (JPG, PNG, GIF)</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <div class="avatar">
                                <div class="w-24 h-24 rounded-lg bg-base-200 flex items-center justify-center" id="imagePreview">
                                    @if($building->image ?? null)
                                        <img src="{{ asset('storage/' . $building->image) }}" alt="{{ $building->name ?? 'Building' }}" class="w-24 h-24 rounded-lg object-cover" />
                                    @else
                                        <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="image" accept="image/*"
                                       class="file-input file-input-bordered w-full @error('image') file-input-error @enderror"
                                       id="imageInput" />
                                @error('image')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @else
                                    <label class="label">
                                        <span class="label-text-alt opacity-50">Leave empty to keep current image</span>
                                    </label>
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
                        <!-- Building Name -->
                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text font-semibold">Building Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="name" placeholder="e.g., Main Production Facility"
                                   class="input input-bordered @error('name') input-error @enderror"
                                   value="{{ old('name', $building->name ?? '') }}" required maxlength="255" />
                            @error('name')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Building Code -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Building Code <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="code" placeholder="e.g., BLD-001"
                                   class="input input-bordered @error('code') input-error @enderror"
                                   value="{{ old('code', $building->code ?? '') }}" required maxlength="50" />
                            @error('code')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Building Status -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Status <span class="text-error">*</span></span>
                            </label>
                            <select name="status" class="select select-bordered @error('status') select-error @enderror" required>
                                <option value="" disabled>Select status</option>
                                <option value="Operational" {{ old('status', $building->status ?? '') === 'Operational' ? 'selected' : '' }}>Operational</option>
                                <option value="Under Construction" {{ old('status', $building->status ?? '') === 'Under Construction' ? 'selected' : '' }}>Under Construction</option>
                                <option value="Maintenance" {{ old('status', $building->status ?? '') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="Inactive" {{ old('status', $building->status ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Building Type -->
                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text font-semibold">Building Type <span class="text-error">*</span></span>
                            </label>
                            <select name="type" class="select select-bordered @error('type') select-error @enderror" required>
                                <option value="" disabled>Select building type</option>
                                <option value="Production Facility" {{ old('type', $building->type ?? '') === 'Production Facility' ? 'selected' : '' }}>Production Facility</option>
                                <option value="Warehouse" {{ old('type', $building->type ?? '') === 'Warehouse' ? 'selected' : '' }}>Warehouse</option>
                                <option value="Office Building" {{ old('type', $building->type ?? '') === 'Office Building' ? 'selected' : '' }}>Office Building</option>
                                <option value="R&D Center" {{ old('type', $building->type ?? '') === 'R&D Center' ? 'selected' : '' }}>R&D Center</option>
                                <option value="Distribution Center" {{ old('type', $building->type ?? '') === 'Distribution Center' ? 'selected' : '' }}>Distribution Center</option>
                            </select>
                            @error('type')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
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
                        <!-- Address -->
                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text font-semibold">Street Address</span>
                            </label>
                            <input type="text" name="address" placeholder="e.g., 123 Industrial Blvd"
                                   class="input input-bordered @error('address') input-error @enderror"
                                   value="{{ old('address', $building->address ?? '') }}" maxlength="500" />
                            @error('address')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">City</span>
                            </label>
                            <input type="text" name="city" placeholder="e.g., Springfield"
                                   class="input input-bordered @error('city') input-error @enderror"
                                   value="{{ old('city', $building->city ?? '') }}" maxlength="100" />
                            @error('city')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- State -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">State</span>
                            </label>
                            <input type="text" name="state" placeholder="e.g., IL"
                                   class="input input-bordered @error('state') input-error @enderror"
                                   value="{{ old('state', $building->state ?? '') }}" maxlength="100" />
                            @error('state')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
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
                        <!-- Contact Name -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Contact Name</span>
                            </label>
                            <input type="text" name="emergency_contact_name" placeholder="e.g., John Doe"
                                   class="input input-bordered @error('emergency_contact_name') input-error @enderror"
                                   value="{{ old('emergency_contact_name', $building->emergency_contact_name ?? '') }}" maxlength="255" />
                            @error('emergency_contact_name')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Contact Phone -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Phone Number</span>
                            </label>
                            <input type="tel" name="emergency_contact_phone" placeholder="e.g., (555) 123-4567"
                                   class="input input-bordered @error('emergency_contact_phone') input-error @enderror"
                                   value="{{ old('emergency_contact_phone', $building->emergency_contact_phone ?? '') }}" maxlength="20" />
                            @error('emergency_contact_phone')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
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

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Notes</span>
                            <span class="label-text-alt opacity-70">Optional - Max 1000 characters</span>
                        </label>
                        <textarea name="notes" class="textarea textarea-bordered h-32 @error('notes') textarea-error @enderror"
                                  placeholder="Any additional information about this building..." maxlength="1000">{{ old('notes', $building->notes ?? '') }}</textarea>
                        @error('notes')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between items-center pt-6 border-t border-base-300">
                    <!-- Delete Button (Left) -->
                    <a href="{{ route('buildings.confirm-delete', $building->id ?? 0) }}"
                       class="btn btn-error btn-outline gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Building
                    </a>

                    <!-- Save/Cancel Buttons (Right) -->
                    <div class="flex gap-3">
                        <a href="{{ route('buildings.show', $building->id ?? 0) }}" class="btn btn-ghost gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </div>
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

