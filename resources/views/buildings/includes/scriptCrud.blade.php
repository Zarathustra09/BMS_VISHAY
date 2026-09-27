<script>
    // Make functions globally available
    window.openCreateFloorModal = function() {
        const form = document.getElementById('createFloorForm');
        form.reset();
        clearFieldErrors(form);
        createFloorModal.showModal();
    };

    window.openEditFloorModal = async function(floorId) {
        try {
            const response = await fetch(`/floors/${floorId}`);
            const data = await response.json();

            if (response.ok) {
                const floor = data.floor;
                document.getElementById('edit_floor_id').value = floor.id;
                document.getElementById('edit_name').value = floor.name;
                document.getElementById('edit_floor_number').value = floor.floor_number;
                document.getElementById('edit_status').value = floor.status;
                document.getElementById('edit_description').value = floor.description || '';

                clearFieldErrors(document.getElementById('editFloorForm'));
                editFloorModal.showModal();
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to load floor details', 'error');
        }
    };

    window.openDeleteFloorModal = function(floorId) {
        document.getElementById('delete_floor_id').value = floorId;
        deleteFloorModal.showModal();
    };

    window.confirmDeleteFloor = function() {
        const floorId = document.getElementById('delete_floor_id').value;
        if (!floorId) return;
        deleteFloor(floorId);
        deleteFloorModal.close();
    };

    window.deleteFloor = async function(floorId) {

        try {
            const response = await fetch(`/floors/${floorId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });

            const result = await response.json();

            if (response.ok) {
                showToast('Floor deleted successfully!', 'success');
                const floorCard = document.querySelector(`[data-floor-id="${floorId}"]`);
                if (floorCard) floorCard.remove();
                const floorsContainer = document.getElementById('floors-container');
                if (floorsContainer && floorsContainer.children.length === 0) location.reload();
            } else {
                showToast('Failed to delete floor', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        }
    };

    window.viewFloorDetails = async function(floorId) {
        try {
            const response = await fetch(`/floors/${floorId}`);
            const data = await response.json();

            if (response.ok) {
                const floor = data.floor;
                const statusBadgeClass = floor.status === 'active' ? 'badge-success' :
                    floor.status === 'maintenance' ? 'badge-warning' : 'badge-error';

                const content = `
                    <h3 class="font-bold text-2xl mb-6 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-md">
                            <span class="text-xl font-bold text-base-100">${floor.floor_number}</span>
                        </div>
                        ${floor.name}
                    </h3>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-base-200/50 rounded-lg">
                                <p class="text-sm opacity-70 mb-1">Floor Number</p>
                                <p class="text-2xl font-bold">${floor.floor_number}</p>
                            </div>
                            <div class="p-4 bg-base-200/50 rounded-lg">
                                <p class="text-sm opacity-70 mb-1">Status</p>
                                <div class="badge ${statusBadgeClass} badge-lg mt-1">${floor.status.charAt(0).toUpperCase() + floor.status.slice(1)}</div>
                            </div>
                        </div>

                        ${floor.description ? `
                        <div class="p-4 bg-base-200/50 rounded-lg">
                            <p class="text-sm opacity-70 mb-2">Description</p>
                            <p>${floor.description}</p>
                        </div>
                        ` : ''}

                        <div class="p-4 bg-primary/10 rounded-lg border border-primary/20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm opacity-70">Total Machines</p>
                                        <p class="text-3xl font-bold text-primary">${floor.machines?.length || 0}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-action">
                        <form method="dialog">
                            <button class="btn btn-ghost">Close</button>
                        </form>
                        <button onclick="viewFloorModal.close(); openEditFloorModal(${floor.id});" class="btn btn-primary gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Floor
                        </button>
                    </div>
                `;

                document.getElementById('floorDetailsContent').innerHTML = content;
                viewFloorModal.showModal();
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to load floor details', 'error');
        }
    };

    // Toast notification helper
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} shadow-lg fixed bottom-4 right-4 w-auto z-50 max-w-sm`;
        toast.innerHTML = `
            <div>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // These modals are submitted via fetch/JSON rather than a page POST, so Laravel's
    // session-flashed $errors/old() never reach them — a 422's `errors` object is rendered
    // by hand instead, matching the <x-form.field> markup (a block <p class="text-error">
    // right after the control) so a validation failure looks the same here as everywhere else.
    function clearFieldErrors(form) {
        form.querySelectorAll('[data-js-error]').forEach(el => el.remove());
        form.querySelectorAll('.input-error, .select-error, .textarea-error').forEach(el => {
            el.classList.remove('input-error', 'select-error', 'textarea-error');
        });
    }

    function renderFieldErrors(form, errors) {
        clearFieldErrors(form);
        Object.entries(errors || {}).forEach(([field, messages]) => {
            const control = form.querySelector(`[name="${field}"]`);
            if (!control) return;

            const errorClass = control.tagName === 'SELECT' ? 'select-error'
                : control.tagName === 'TEXTAREA' ? 'textarea-error'
                : 'input-error';
            control.classList.add(errorClass);

            const message = document.createElement('p');
            message.className = 'text-error text-xs mt-0.5';
            message.setAttribute('data-js-error', field);
            message.textContent = Array.isArray(messages) ? messages[0] : messages;
            control.parentElement.appendChild(message);
        });
    }

    // Form submissions
    document.addEventListener('DOMContentLoaded', function() {
        const createForm = document.getElementById('createFloorForm');
        const editForm = document.getElementById('editFloorForm');

        if (createForm) {
            createForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());

                try {
                    const response = await fetch('{{ route("floors.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok) {
                        showToast('Floor created successfully!', 'success');
                        createFloorModal.close();
                        setTimeout(() => location.reload(), 500);
                    } else if (response.status === 422) {
                        renderFieldErrors(this, result.errors);
                    } else {
                        showToast(result.error || 'Failed to create floor', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                }
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const floorId = document.getElementById('edit_floor_id').value;
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());

                try {
                    const response = await fetch(`/floors/${floorId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok) {
                        showToast('Floor updated successfully!', 'success');
                        editFloorModal.close();
                        setTimeout(() => location.reload(), 500);
                    } else if (response.status === 422) {
                        renderFieldErrors(this, result.errors);
                    } else {
                        showToast(result.error || 'Failed to update floor', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                }
            });
        }
    });
</script>
