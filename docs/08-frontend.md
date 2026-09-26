# Frontend Conventions

The frontend is server-rendered Blade with a small amount of vanilla JS/Alpine for interactivity — there is no SPA framework (no Vue/React) in this codebase.

## Stack

| Piece | How it's loaded | Notes |
|---|---|---|
| Tailwind CSS v4 | CDN (`@tailwindcss/browser@4`) in [layouts/app.blade.php](../resources/views/layouts/app.blade.php) | Utility classes directly in Blade |
| DaisyUI v5 | CDN, on top of Tailwind | Provides component classes (`btn`, `card`, `alert`, `stat`, `input`, `join`, etc.) and theming (`data-theme="forest"` set on `<html>`) |
| Alpine.js | CDN (`alpinejs@3.x.x`) | Small reactive bits: the device-key Show/Hide/Copy control, dropdowns, modals |
| jQuery + DataTables | CDN, via [`partials/_datatables-assets.blade.php`](../resources/views/partials/_datatables-assets.blade.php) | Only included on pages with a table (buildings/machines index, machine logs, roles/show, permissions/index) — see [02-architecture.md](02-architecture.md) for the server-side DataTables pattern. `yajra/laravel-datatables` (composer) is the PHP half only; it ships no browser assets, so jQuery and DataTables' own JS/CSS come from CDN here, the only place jQuery is loaded in the app. |
| Vite + npm | `resources/js/app.js`, `resources/css/app.css` | Bundles local JS/CSS (bootstrap.js sets up axios); the DaisyUI/Tailwind/Alpine/DataTables CDN scripts are separate from this build and don't need `npm run build` to change |
| Google Fonts | CDN (`fonts.bunny.net`, Inter) | |

Because Tailwind/DaisyUI/Alpine/DataTables come from a CDN rather than being part of the Vite bundle, you can edit Blade markup that uses their classes without rebuilding — only changes to `resources/js/*` or `resources/css/*` need `npm run dev`/`npm run build`.

## Layout structure

- [layouts/app.blade.php](../resources/views/layouts/app.blade.php) — the authenticated shell: sidebar nav (highlights the active section via `request()->is('machines*')` etc.), `@include('layouts.header')`, `@yield('content')`, `@include('layouts.footer')`.
- [layouts/header.blade.php](../resources/views/layouts/header.blade.php) — top bar: page title/breadcrumbs slot, profile/settings/logout dropdown.
- [layouts/footer.blade.php](../resources/views/layouts/footer.blade.php) — footer.
- [layouts/guest/app.blade.php](../resources/views/layouts/guest/app.blade.php) — unauthenticated shell (login/register pages).
- [components/skeleton-loader.blade.php](../resources/views/components/skeleton-loader.blade.php) — loading placeholder included in the main layout.

## View folder conventions

Each module gets its own folder under `resources/views/`, mirroring the resource-controller actions:

```
resources/views/<module>/
  index.blade.php              — list page
  create.blade.php             — create form
  edit.blade.php                — edit form
  show.blade.php                — detail page
  delete.blade.php              — confirm-delete page (see below)
  _datatable-*.blade.php         — DataTable cell partials, if the index is a server-side table (see 02-architecture.md)
```

`resources/views/partials/` holds cross-module shared partials — currently just `_datatables-assets.blade.php` (jQuery + DataTables CDN, included on any page with a table).

**Exception**: `users/` has only `index.blade.php`. Create, edit, reset-password, and delete are all `<dialog>` modals on that single page rather than separate routes/views — appropriate here since a user record has few fields and no detail page worth a `show` view. Don't take this as the default; new modules should follow the full index/create/edit/show/delete convention unless there's a similarly good reason not to.

Every page starts with:

```blade
@extends('layouts.app')

@section('title', 'Page Title')

@section('breadcrumbs')
    <li><a href="{{ route('...') }}">Parent</a></li>
    <li>Current Page</li>
@endsection

@section('content')
    ...
@endsection
```

## Flash message pattern

Controllers set flash data with `->with('success', '...')` or `->with('error', '...')` on a redirect. Views render it at the top of the `content` section:

```blade
@if(session('success'))
<div class="alert alert-success shadow-lg animate-fadeIn">
    <svg class="w-6 h-6" ...></svg>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="alert alert-error shadow-lg animate-fadeIn"> ... </div>
@endif
```

A one-off flash key can also carry a specific payload rather than a plain message — e.g. `machines/show.blade.php` reads `session('device_key_plain')` to show the newly generated device key right after `POST /machines/{machine}/device-key`.

## Confirm-delete page pattern

Destructive deletes use a dedicated confirmation **page** (not a JS `confirm()` popup) at `{module}/{id}/delete`, linked from the index/show page's delete button. The building delete page ([buildings/delete.blade.php](../resources/views/buildings/delete.blade.php)) is the most elaborate example: it requires the user to type the building's exact name into a text input (server-validated in `BuildingController@destroy` via a custom closure rule) plus check three separate acknowledgement checkboxes (data loss will be permanent, floors will be deleted, machines will be deleted), each counting the actual related records so the warning is specific ("2 floors", "5 machines"). Simpler modules (e.g. machines) use a lighter version of the same page with a warning banner and a single confirm button.

Use a JS `onsubmit="return confirm('...')"` popup instead only for lower-stakes actions — e.g. regenerating a device key uses this lighter pattern since it's reversible (a new key can always be generated again) rather than destructive.

A third variant exists for modal-driven pages like Users ([users/index.blade.php](../resources/views/users/index.blade.php)): a per-row **confirmation modal** (`<dialog>`) rather than a full page, since the whole page is already modal-based for create/edit/reset-password. It states plainly what will be deleted (the user's name and email) with no undo, and is a middle ground between the two patterns above — heavier than a `confirm()` popup (an actual page in the DOM, not a native browser dialog, so it can be styled and can't be accidentally dismissed by pressing Enter) but lighter than a dedicated route+page, appropriate for a delete that doesn't cascade to other records the way a building's does.

## Authorization in views

Gate UI elements with Blade's `@can` directive, backed by Spatie permissions (see [06-security.md](06-security.md)):

```blade
@can('machines.view-device-key')
    <div class="card"> ... device key card ... </div>
@endcan
```

Always pair this with the same permission on the route/controller side — `@can` hides the button, but the server-side `permission:` middleware is what actually enforces it. Never rely on `@can` alone.

## Interactivity patterns

- **Show/Hide/Copy toggle** (device key card): `x-data="{ visible: false, copied: false }"`, toggling an `<input type="password">`/`type="text"` binding and `navigator.clipboard.writeText()`.
- **Async chat with polling** (AI insights on the machine page): plain `fetch()` calls, CSRF token read from the `<meta name="csrf-token">` tag (see [06-security.md](06-security.md)), a `setInterval`-driven poll against `ai.check-status` until the response is ready.
- **DaisyUI `<dialog>` modals**: used for things like "Add New User" (`onclick="add_user_modal.showModal()"`) — no Alpine needed since `<dialog>` has native show/close.
- **Shared partial with a readonly mode**: [roles/_permission-matrix.blade.php](../resources/views/roles/_permission-matrix.blade.php) renders the same module × view/create/update/delete checkbox grid on three pages (`roles/create`, `roles/edit`, `roles/show`), toggled by a `$readonly` flag passed via `@include(..., ['readonly' => true])`. Readonly mode disables every checkbox and skips rendering the row/column/select-all toggle buttons and their `<script>` block entirely — cheaper than rendering-then-hiding, and it means a readonly view can never accidentally submit a change. The toggle buttons use plain vanilla JS (`querySelectorAll` + a shared `data-module`/`data-action` attribute on each checkbox), not Alpine, because the interaction is simple bulk-check/uncheck with no state to track between renders. The script is pushed once via `@once @push('scripts')` so including the partial multiple times on one page (it isn't, currently, but could be) wouldn't redeclare the functions.

## Theming

`data-theme="forest"` is hardcoded on the `<html>` tag in the main layout — there's no theme switcher yet. If one is added, note that the guest layout ([layouts/guest/app.blade.php](../resources/views/layouts/guest/app.blade.php)) would need to pick up the same theme mechanism.
