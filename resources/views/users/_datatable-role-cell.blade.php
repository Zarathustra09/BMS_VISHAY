<div class="flex flex-wrap items-center gap-1">
    @forelse($user->roles as $role)
        <div class="badge badge-primary badge-outline capitalize">{{ $role->name }}</div>
    @empty
        <div class="badge badge-ghost">No role</div>
    @endforelse
    @if($user->id === auth()->id())
        <div class="badge badge-ghost badge-sm">You</div>
    @endif
</div>
