{{--
    Buttons carry the user's data as data-* attributes rather than inline onclick with
    interpolated strings — a name/email containing a quote would break out of an inline JS
    string even though Blade HTML-escapes it. The page's shared modals are populated via
    jQuery .data() reads (see users/index.blade.php), which is safe either way.
--}}
<div class="flex items-center gap-2">
    @can('users.update')
        <button type="button" class="btn btn-outline btn-xs js-edit-user"
                data-id="{{ $user->id }}"
                data-name="{{ $user->name }}"
                data-email="{{ $user->email }}"
                data-role="{{ $user->roles->first()->name ?? '' }}">Edit</button>
        <button type="button" class="btn btn-ghost btn-xs js-reset-password"
                data-id="{{ $user->id }}"
                data-name="{{ $user->name }}">Reset Password</button>
    @endcan
    @can('users.delete')
        @if($user->id !== auth()->id())
        <button type="button" class="btn btn-error btn-outline btn-xs js-delete-user"
                data-id="{{ $user->id }}"
                data-name="{{ $user->name }}"
                data-email="{{ $user->email }}">Delete</button>
        @endif
    @endcan
</div>
