@php
    // Shared by both the create and edit floor forms — options rendered via x-form.select.
    $floorStatusOptions = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'maintenance' => 'Under Maintenance',
    ];
@endphp

<!-- Create Floor Modal -->
@can('floors.create')
<dialog id="createFloorModal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box max-w-2xl bg-base-100 border border-base-300/50">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="font-bold text-2xl mb-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
            Add New Floor
        </h3>

        <form id="createFloorForm" class="space-y-4">
            @csrf
            <input type="hidden" name="building_id" value="{{ $building->id }}">

            <x-form.input name="name" label="Floor Name" required placeholder="e.g., Ground Floor, First Floor" />

            <x-form.input type="number" name="floor_number" label="Floor Number" required placeholder="0"
                          help="Use negative numbers for basement levels" />

            <x-form.select name="status" label="Status" required :options="$floorStatusOptions" />

            <x-form.textarea name="description" label="Description" :rows="3"
                              placeholder="Additional information about this floor..." class="h-24" />

            <div class="modal-action">
                <button type="button" onclick="document.getElementById('createFloorModal').close()"
                        class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Create Floor
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endcan

<!-- Edit Floor Modal -->
@can('floors.update')
<dialog id="editFloorModal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box max-w-2xl bg-base-100 border border-base-300/50">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="font-bold text-2xl mb-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
            Edit Floor
        </h3>

        <form id="editFloorForm" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_floor_id" name="floor_id">

            <x-form.input name="name" id="edit_name" label="Floor Name" required placeholder="e.g., Ground Floor, First Floor" />

            <x-form.input type="number" name="floor_number" id="edit_floor_number" label="Floor Number" required placeholder="0"
                          help="Use negative numbers for basement levels" />

            <x-form.select name="status" id="edit_status" label="Status" required :options="$floorStatusOptions" />

            <x-form.textarea name="description" id="edit_description" label="Description" :rows="3"
                              placeholder="Additional information about this floor..." class="h-24" />

            <div class="modal-action">
                <button type="button" onclick="document.getElementById('editFloorModal').close()"
                        class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Floor
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endcan

<!-- View Floor Details Modal -->
@can('floors.view')
<dialog id="viewFloorModal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box max-w-3xl bg-base-100 border border-base-300/50">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <div id="floorDetailsContent">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endcan

<!-- Delete Floor Modal -->
@can('floors.delete')
<dialog id="deleteFloorModal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box max-w-md bg-base-100 border border-base-300/50">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>

        <h3 class="font-bold text-2xl mb-2 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-error/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"></path>
                </svg>
            </div>
            Delete Floor
        </h3>

        <p class="opacity-70">This action cannot be undone. Are you sure you want to delete this floor?</p>

        <input type="hidden" id="delete_floor_id" />

        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">Cancel</button>
            </form>
            <button type="button" class="btn btn-error gap-2" onclick="confirmDeleteFloor()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Delete
            </button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endcan
