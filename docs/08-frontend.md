# Frontend Conventions

The frontend is server-rendered Blade with a small amount of vanilla JS/Alpine for interactivity — there is no SPA framework (no Vue/React) in this codebase.

## Stack

| Piece | How it's loaded | Notes |
|---|---|---|
| Tailwind CSS v4 | CDN (`@tailwindcss/browser@4`) in [layouts/app.blade.php](../resources/views/layouts/app.blade.php) | Utility classes directly in Blade |
| DaisyUI v5 | CDN, on top of Tailwind | Provides component classes (`btn`, `card`, `alert`, `stat`, `input`, `join`, etc.) and theming (`data-theme="forest"` set on `<html>`) |
| Alpine.js | CDN (`alpinejs@3.x.x`) | Small reactive bits: the device-key Show/Hide/Copy control, dropdowns, modals |
| Vite + npm | `resources/js/app.js`, `resources/css/app.css` | Bundles local JS/CSS (bootstrap.js sets up axios); the DaisyUI/Tailwind/Alpine CDN scripts are separate from this build and don't need `npm run build` to change |
| Google Fonts | CDN (`fonts.bunny.net`, Inter) | |

Because Tailwind/DaisyUI/Alpine come from a CDN rather than being part of the Vite bundle, you can edit Blade markup that uses their classes without rebuilding — only changes to `resources/js/*` or `resources/css/*` need `npm run dev`/`npm run build`.

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
  index.blade.php    — list page
  create.blade.php   — create form
  edit.blade.php     — edit form
  show.blade.php     — detail page
  delete.blade.php   — confirm-delete page (see below)
```

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

## Theming

`data-theme="forest"` is hardcoded on the `<html>` tag in the main layout — there's no theme switcher yet. If one is added, note that the guest layout ([layouts/guest/app.blade.php](../resources/views/layouts/guest/app.blade.php)) would need to pick up the same theme mechanism.
