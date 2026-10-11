{{--
    Shared by groups/create and groups/edit.
    Variables: $group (null when creating), $users, $roles, $groupedPermissions.
--}}
@php
    $isEdit = isset($group) && $group;
    $checkedUsers = collect(old('user_ids', $isEdit ? $group->users->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
    $checkedRoles = old('roles', $isEdit ? $group->roles->pluck('name')->all() : []);
    $checkedPermissions = old('permissions', $isEdit ? $group->permissions->pluck('name')->all() : []);
@endphp

@if(session('error'))
<div class="alert alert-error shadow-lg">
    <span>{{ session('error') }}</span>
</div>
@endif

<form action="{{ $isEdit ? route('groups.update', $group) : route('groups.store') }}" method="POST" class="space-y-6">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body space-y-2">
            <x-form.input name="name" label="Group Name" required autofocus class="max-w-md"
                          :value="$isEdit ? $group->name : null"
                          placeholder="e.g. Line 1 Maintenance Team" />
            <x-form.textarea name="description" label="Description" :rows="2"
                             :value="$isEdit ? $group->description : null"
                             help="Optional - what this group is for" />
        </div>
    </div>

    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title">Members</h2>
            <p class="text-sm opacity-70">Everyone ticked here inherits this group's roles and permissions, and is emailed when a machine assigned to this group raises an alert. A user can be in several groups.</p>

            <input type="text" id="groupMemberSearch" class="input input-sm w-full max-w-sm" placeholder="Search users..." autocomplete="off">
            <div class="max-h-72 overflow-y-auto space-y-1 border border-base-300 rounded-lg p-2">
                @forelse($users as $user)
                <label class="flex items-center gap-3 px-2 py-1 rounded hover:bg-base-200 cursor-pointer group-member-row"
                       data-search="{{ strtolower($user->name.' '.$user->email) }}">
                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="checkbox checkbox-sm"
                           @checked(in_array($user->id, $checkedUsers, true))>
                    <span>
                        <span class="block text-sm">{{ $user->name }}</span>
                        <span class="block text-xs opacity-60">{{ $user->email }}</span>
                    </span>
                </label>
                @empty
                <p class="text-sm opacity-60 px-2">No users exist yet.</p>
                @endforelse
            </div>
            @error('user_ids.*') <p class="text-error text-sm">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title">Roles</h2>
            <p class="text-sm opacity-70">Members get every permission of the roles ticked here.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($roles as $role)
                <label class="flex items-center gap-3 px-3 py-2 rounded-lg border border-base-300 hover:bg-base-200 cursor-pointer">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="checkbox checkbox-sm"
                           @checked(in_array($role->name, $checkedRoles, true))>
                    <span class="capitalize">{{ str_replace('-', ' ', $role->name) }}</span>
                </label>
                @endforeach
            </div>
            @error('roles.*') <p class="text-error text-sm">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title">Extra permissions</h2>
            <p class="text-sm opacity-70">Optional - permissions given to the group directly, on top of its roles.</p>
            @include('roles._permission-matrix', ['groupedPermissions' => $groupedPermissions, 'checkedNames' => $checkedPermissions])
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ $isEdit ? route('groups.show', $group) : route('groups.index') }}" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Group' }}</button>
    </div>
</form>

<script>
    document.getElementById('groupMemberSearch')?.addEventListener('input', function (e) {
        var q = e.target.value.toLowerCase();
        document.querySelectorAll('.group-member-row').forEach(function (row) {
            row.style.display = row.dataset.search.includes(q) ? '' : 'none';
        });
    });
</script>
