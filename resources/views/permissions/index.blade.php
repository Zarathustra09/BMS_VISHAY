@extends('layouts.app')

@section('title', 'Permissions')

@section('breadcrumbs')
    <li><a href="{{ route('permissions.index') }}">Permissions</a></li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            Permissions
        </h1>
        <p class="text-sm opacity-70 mt-2">
            Every permission the system knows about, and which roles hold it.
            @can('roles.create')
                Permissions are read-only here — a permission only takes effect once code checks it, so new ones are added by a developer, not from this page. To change who has a permission, edit a <a href="{{ route('roles.index') }}" class="link link-primary">role</a> instead.
            @endcan
        </p>
    </div>

    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table id="permissionsTable" class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Permission</th>
                            <th>Module</th>
                            <th>Roles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $permission)
                            @php
                                [$module] = explode('.', $permission->name, 2);
                            @endphp
                            <tr>
                                <td><code class="text-sm">{{ $permission->name }}</code></td>
                                <td class="capitalize">{{ str_replace('-', ' ', $module) }}</td>
                                <td>
                                    @forelse($permission->roles as $role)
                                        <div class="badge badge-outline badge-sm capitalize mr-1 mb-1">{{ $role->name }}</div>
                                    @empty
                                        <span class="opacity-40 text-sm">No role has this permission</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('partials._datatables-assets')

@push('scripts')
<script>
    $(function () {
        // A few dozen rows at most — client-side sort/search/paging over the
        // already-rendered table, no ajax endpoint needed.
        $('#permissionsTable').DataTable({
            autoWidth: false,
            pageLength: 25,
        });
    });
</script>
@endpush
@endsection
