# Frontend Conventions

The frontend is server-rendered Blade with a small amount of vanilla JS/Alpine for interactivity — there is no SPA framework (no Vue/React) in this codebase.

## Stack

| Piece | How it's loaded | Notes |
|---|---|---|
| Tailwind CSS v4 | Vite bundle (`@tailwindcss/vite` in `vite.config.js`, entry `resources/css/app.css`) | Utility classes directly in Blade. Compiled for real — see the note below. |
| DaisyUI v5 | Vite bundle, via `@plugin "daisyui"` in `resources/css/app.css` | Provides component classes (`btn`, `card`, `alert`, `stat`, `input`, `join`, etc.) and theming (`data-theme` set on `<html>` — see [Theming](#theming) below) |
| Alpine.js | CDN (`alpinejs@3.x.x`) | Small reactive bits: the device-key Show/Hide/Copy control, dropdowns, modals |
| jQuery + DataTables | CDN, via [`partials/_datatables-assets.blade.php`](../resources/views/partials/_datatables-assets.blade.php) | Only included on pages with a table (buildings/machines/alerts index, machine logs, roles/show, permissions/index) — see [02-architecture.md](02-architecture.md) for the server-side DataTables pattern. `yajra/laravel-datatables` (composer) is the PHP half only; it ships no browser assets, so jQuery and DataTables' own JS/CSS come from CDN here, the only place jQuery is loaded in the app. |
| Vite + npm | `resources/js/app.js`, `resources/css/app.css` | Bundles local JS/CSS (bootstrap.js sets up axios) and now Tailwind/DaisyUI too; the Alpine/DataTables CDN scripts remain separate from this build |
| Google Fonts | CDN (`fonts.bunny.net`, Inter) | |

**Tailwind/DaisyUI moved off the browser CDN onto a real Vite build.** They used to load from `@tailwindcss/browser@4` + `daisyui@5` CDN tags, which JIT-compile whatever classes are present in the rendered DOM at page load — convenient, but silently broken in two ways an actual build exposes: (1) any class name built by string interpolation in Blade (`class="badge-{{ $color }}"`, `class="lg:grid-cols-{{ $n }}"`) never appears as literal text for a real scanner to find, so it must instead be written as complete literal class names, or added to the `@source inline(...)` safelist in `resources/css/app.css` if it's genuinely assembled at runtime from a small fixed set of tokens (see the comment there); (2) `resources/css/app.css` used to carry a `@plugin "daisyui/theme"` block overriding `forest` with a pink/magenta palette that was never intended to be live (verbatim theme-generator scaffold, pasted into an unrelated commit) — it was dead code for months, silently ignored because nothing compiled it, so the app actually rendered daisyUI's stock green `forest`. Compiling for real woke that block up and the theme visibly changed color; it's since been deleted, and every theme in `resources/css/app.css`'s `@plugin "daisyui"` list is now daisyUI's stock definition, none customised. Now that it's a real build, `npm run build` (or `npm run dev`) is required after editing any Blade file that uses a class not already compiled in, exactly like editing `resources/js/*` or `resources/css/*` always required.

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

**Exception**: `users/` has only `index.blade.php`. Create, edit, reset-password, and delete are all `<dialog>` modals on that single page rather than separate routes/views — appropriate here since a user record has few fields and no detail page worth a `show` view. Don't take this as the default; new modules should follow the full index/create/edit/show/delete convention unless there's a similarly good reason not to. Since the table itself is now a server-side DataTable (ajax-rendered rows), edit/reset-password/delete are each **one shared modal**, not one per user — see the pattern below.

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

A third variant exists for modal-driven pages like Users ([users/index.blade.php](../resources/views/users/index.blade.php)): a **confirmation modal** (`<dialog>`) rather than a full page, since the whole page is already modal-based for create/edit/reset-password. It states plainly what will be deleted (the user's name and email) with no undo, and is a middle ground between the two patterns above — heavier than a `confirm()` popup (an actual page in the DOM, not a native browser dialog, so it can be styled and can't be accidentally dismissed by pressing Enter) but lighter than a dedicated route+page, appropriate for a delete that doesn't cascade to other records the way a building's does.

**Shared modal, populated from an ajax-rendered row.** Once `users/index`'s table became a server-side DataTable, the old pattern of rendering one `edit_user_modal_{{ $user->id }}` / `delete_user_modal_{{ $user->id }}` per row no longer works — ajax-rendered rows aren't in the initial Blade output, so there's nothing to render N modals against. Instead there's **one** `#edit_user_modal`, one `#reset_password_modal`, one `#delete_user_modal` on the page, and each row's action buttons carry the row's data as `data-*` attributes (see [`users/_datatable-actions.blade.php`](../resources/views/users/_datatable-actions.blade.php)) rather than inline `onclick="...('{{ $user->name }}')"` calls. A click handler (delegated via `$(document).on('click', '.js-edit-user', ...)`, since the buttons don't exist until DataTables renders them) reads `$(this).data(...)`, fills the shared modal's fields, points its form's `action` at a route built from a `:id`-placeholder template (`'{{ route("users.update", ["user" => ":id"]) }}'.replace(':id', id)`), and opens it. This also sidesteps a real bug in the old approach: a name or email containing a single quote would have broken out of the inline-`onclick` JS string even though Blade's `{{ }}` HTML-escaped it — `data-*` attributes read via `.data()` have no such risk. The same shared-modal-plus-`:id`-template pattern is used for the "Add Users" modal on `roles/index`.

## Authorization in views

Gate UI elements with Blade's `@can` directive, backed by Spatie permissions (see [06-security.md](06-security.md)):

```blade
@can('machines.view-device-key')
    <div class="card"> ... device key card ... </div>
@endcan
```

Always pair this with the same permission on the route/controller side — `@can` hides the button, but the server-side `permission:` middleware is what actually enforces it. Never rely on `@can` alone.

## Header bell (Phase 5)

The alerts dropdown in [layouts/header.blade.php](../resources/views/layouts/header.blade.php) and the sidebar's "Alerts" badge in [layouts/app.blade.php](../resources/views/layouts/app.blade.php) both read `$unreadAlertCount`/`$recentAlertNotifications`, shared by a `View::composer()` in `AppServiceProvider::boot()` rather than queried inline in the view — see the "Shared view data" section in [02-architecture.md](02-architecture.md). Each notification in the dropdown is a small `<form>` that POSTs to `notifications.read` (marks it read, then redirects to the machine it's about) rather than a plain link, since marking-as-read needs a state change.

## Inline SVG charts (Phase 6)

The "Live Sensor Readings" chart on `machines/show.blade.php` is **hand-built inline SVG**, not a charting library. daisyUI's own chart offering (daisyUI Charts) is a paid product built on ApexCharts, and ApexCharts is no longer MIT — it's revenue-based, requiring a paid commercial licence for any organization at or above $2M annual revenue, which this deployment is well above. Chart.js (genuinely MIT) was also considered and declined in favor of no third-party dependency at all. The result matches the app's existing chart language (`home.blade.php`'s card/select/legend layout) using nothing but SVG, Alpine, and DaisyUI CSS tokens.

Three traps make this kind of chart easy to get subtly wrong — all three bit an earlier draft of this feature, so they're recorded here rather than left to be rediscovered:

1. **`var()` does not work in SVG presentation attributes.** `<rect fill="var(--color-error)">` renders as black/nothing — CSS custom properties are only substituted inside CSS *declarations*, not element attributes. Every chart colour lives in the page's `<style>` block as a class (`.chart-line { stroke: var(--color-primary) }`), never as `fill="..."`/`stroke="..."` directly on an SVG element. This is the same family of bug as the DaisyUI v4→v5 `hsl(var(--b1))` trap documented under Theming below — get it right once here, in one place, and the whole chart recolours instantly on a theme switch with zero JavaScript.
2. **`preserveAspectRatio="none"`** (needed so the SVG stretches to fill its container without ever measuring `clientWidth`, which the layout's 300ms content-reveal delay — see the DataTables gotchas above — would report as 0) **stretches `<text>` into smeared glyphs and turns `<circle>` into an ellipse.** So the SVG itself contains only `<path>`, `<rect>`, and `<line>` (all of which use `vector-effect="non-scaling-stroke"` to keep a constant 1px line weight regardless of scale). Axis labels and data-point dots are HTML (`<span>`) positioned on top of the SVG with inline `left`/`top` percentages, not SVG elements.
3. **Tailwind only compiles utility classes it can find as literal text in a source file** (see the Stack table above — this used to be a browser-CDN-specific quirk and now applies for the same reason a real build always has: it scans source, never runtime output). Axis labels solve this by being a fixed set of five statically-classed `<span x-text="...">` elements per axis in the initial HTML — Alpine only ever changes their text, never their classes or existence. The Online/Offline badge follows the same rule from the other direction: all of its possible classes (`badge-success`, `badge-error`, `badge-ghost`, `animate-pulse`) appear as literal strings in the page source, and Alpine's `:class` binding only ever selects between them — never builds one by string concatenation, which a class scanner can't see coming and won't generate on its own (a handful of other views build a badge/alert color this way from a small fixed set of tokens instead of literal strings — see the `@source inline(...)` safelist in `resources/css/app.css`).

**Data flow**: `MachineLogService::getReadingSeries(Machine, ?metric, range)` returns bucketed points (avg/min/max/n per bucket — keeping min/max means a real spike that raised a critical alert doesn't disappear into an average) plus the metric's threshold bounds and the machine's online state, in one array. `MachineController@show` calls it once and the Blade view embeds the result directly into the page via `x-data="sensorChart(@js($chart))"` — `@js()` safely serializes it into a `JSON.parse('...')` call, so the chart renders fully drawn on first paint with **no fetch and no `x-cloak` flash**. The same method backs `GET /machines/{machine}/readings`, which the component then polls every 30 seconds (paused via `document.visibilityState` when the tab isn't visible, cancelled mid-flight with `AbortController` on a metric/range change, and given up after 5 consecutive failures rather than hammering a broken endpoint forever).

**Gaps are real gaps.** An empty bucket (a silent period) is never zero-filled — the chart's path-building code starts a new SVG path segment whenever the time between two consecutive points exceeds 1.5× the bucket size, so an outage renders as a visible break in the line rather than a misleading flat interpolation across it.

## Interactivity patterns

- **Show/Hide/Copy toggle** (device key card): `x-data="{ visible: false, copied: false }"`, toggling an `<input type="password">`/`type="text"` binding and `navigator.clipboard.writeText()`.
- **Async chat with polling** (AI insights on the machine page): plain `fetch()` calls, CSRF token read from the `<meta name="csrf-token">` tag (see [06-security.md](06-security.md)), a `setInterval`-driven poll against `ai.check-status` until the response is ready.
- **Sensor chart polling** (Live Sensor Readings, machines/show): same `setInterval` shape as the AI chat, but 30s instead of 2s, and using `fetch()`'s built-in `AbortController` instead of a boolean flag to cancel a stale in-flight request when the metric/range selection changes mid-poll. See "Inline SVG charts" above.
- **DaisyUI `<dialog>` modals**: used for things like "Add New User" (`onclick="add_user_modal.showModal()"`) — no Alpine needed since `<dialog>` has native show/close.
- **Shared partial with a readonly mode**: [roles/_permission-matrix.blade.php](../resources/views/roles/_permission-matrix.blade.php) renders the same module × view/create/update/delete checkbox grid on three pages (`roles/create`, `roles/edit`, `roles/show`), toggled by a `$readonly` flag passed via `@include(..., ['readonly' => true])`. Readonly mode disables every checkbox and skips rendering the row/column/select-all toggle buttons and their `<script>` block entirely — cheaper than rendering-then-hiding, and it means a readonly view can never accidentally submit a change. The toggle buttons use plain vanilla JS (`querySelectorAll` + a shared `data-module`/`data-action` attribute on each checkbox), not Alpine, because the interaction is simple bulk-check/uncheck with no state to track between renders. The script is pushed once via `@once @push('scripts')` so including the partial multiple times on one page (it isn't, currently, but could be) wouldn't redeclare the functions.
- **Seeding an Alpine `x-data` component from server data**: always use `@js($value)`, never `@json($value)`, in an HTML attribute. `@js()` compiles to `JSON.parse('…')` with the JSON string single-quoted, which is safe inside a double-quoted attribute; `@json()` emits the raw JSON with its own double quotes, which terminates the attribute early the moment the payload contains an object or array. That exact bug once broke every Alpine binding on `machines/_form.blade.php` (`x-data="machineForm(@json($seed))"` truncated to `machineForm({`) — the floor dropdown never populated and specification fields never switched with the selected type, because Alpine had silently failed to initialize at all. `machines/show.blade.php`'s `sensorChart(@js($chart))` is the correct pattern to copy.
- **Dynamic repeater rows** (Machine Types' Specification Fields builder, [machine_types/_form.blade.php](../resources/views/machine_types/_form.blade.php)): the first add/remove-row UI in the app, `x-data="specFieldBuilder(@js($seedFields), @js($fieldErrors))"` holding a `fields` array rendered with `<template x-for="(field, i) in fields" :key="field.uid">`. Since each row's input `name` depends on its array index (`spec_fields[0][label]`, `spec_fields[1][label]`, …), the rows use plain `<input x-bind:name="`spec_fields[${i}][label]`">` elements rather than the `<x-form.*>` components below, which only take a static `name` prop — each row's markup instead copies `field.blade.php`'s classes by hand (`flex flex-col gap-1.5`, `label py-0`, `input w-full`) to stay visually consistent. Per-row server validation errors ("spec_fields.2.label" → message) are reshaped in the view into a flat `{"2.label": "message"}` object the component can look up by index, since Alpine has no direct access to Laravel's `$errors` bag. The boolean `required` checkbox uses the same hidden-`0` + checkbox-`1` trick as `<x-form.toggle>` (an unchecked checkbox submits nothing at all), just written by hand for the same dynamic-name reason.

## Back button convention

Nested pages (create/edit/show/delete — anything reached by drilling into a record rather than directly from the sidebar) declare a **smart parent route** via `@section('back', route(...))`:

```blade
@section('back', route('buildings.show', $building->id ?? 0))
```

[layouts/app.blade.php](../resources/views/layouts/app.blade.php) renders a single shared back button just above `@yield('content')`, only when the section is present:

```blade
@hasSection('back')
    <a href="@yield('back')" class="btn btn-ghost btn-sm gap-2 mb-4">
        <svg ...chevron-left.../> Back
    </a>
@endif
```

Rules:
- Point at the logical parent, not `history.back()` — a deep-linked or freshly-loaded page still gets a correct target. `edit`/`delete`/`show` pages that need the model use the bound route-model variable (e.g. `route('roles.show', $role)`); `create` pages point at the module's `index`.
- **Top-level index pages** (and `home`, `profile`, `settings`, `permissions`) set **no** `back` section — they're reached from the sidebar, so no button renders.
- There is exactly one back button per page, owned by the layout. Don't hand-roll another `<a class="btn btn-ghost btn-circle">` back arrow inside a view's own markup — add `@section('back', ...)` instead.

## Theming

Two DaisyUI themes are wired to a single `theme` `localStorage` key: **`corporate`** (light, the default) and **`forest`** (dark, green primary). Both are daisyUI's stock definitions, already compiled into the Vite bundle via the `@plugin "daisyui"` themes list in `resources/css/app.css`, so switching needs no new assets.

**No-FOUC bootstrap.** A synchronous inline `<script>` runs in `<head>`, before the DaisyUI stylesheet link, reading `localStorage.getItem('theme')` (falling back to `corporate`) and setting `data-theme` on `<html>` immediately — this runs in both [layouts/app.blade.php](../resources/views/layouts/app.blade.php) and [layouts/guest/app.blade.php](../resources/views/layouts/guest/app.blade.php), so a theme chosen while logged in is still respected on the login screen. The static `data-theme="corporate"` attribute on `<html>` is the no-JS fallback.

**Single source of truth.** `layouts/app.blade.php` defines a global `window.setTheme(theme)`:

```js
window.setTheme = function (theme) {
    document.documentElement.setAttribute('data-theme', theme);
    try { localStorage.setItem('theme', theme); } catch (e) {}
    document.querySelectorAll('.js-theme-toggle').forEach(function (cb) {
        cb.checked = theme === 'forest';
    });
};
```

Anything that changes the theme calls this — never touch `data-theme`/`localStorage` directly. Two call sites:
- **Header toggle** ([layouts/header.blade.php](../resources/views/layouts/header.blade.php)): a `swap swap-rotate` sun/moon checkbox (`.js-theme-toggle`) between the alerts dropdown and the user menu; `onchange="window.setTheme(this.checked ? 'forest' : 'corporate')"`. Only ever flips corporate ⇄ forest.
- **Settings page** ([settings/index.blade.php](../resources/views/settings/index.blade.php)): a picker across all 8 installed DaisyUI themes (forest, light, dark, cupcake, cyberpunk, corporate, synthwave, business) for users who want something other than the two "official" options. Its `changeTheme(theme)` also calls `window.setTheme()`, then a `markActiveTheme(theme)` helper toggles each card's `.active-badge` by matching `data-theme-option`. Because it shares the same `localStorage` key, picking a theme here updates what the header toggle shows next load (checked = forest, unchecked otherwise), and vice versa.

**v4→v5 CSS variable trap.** DaisyUI v5 renamed its CSS custom properties (`hsl(var(--b1))` → `var(--color-base-100)`, `hsl(var(--bc)/0.2)` → `color-mix(in oklab, var(--color-base-content) 20%, transparent)`, `hsl(var(--p))`/`hsl(var(--pc))` → `var(--color-primary)`/`var(--color-primary-content)`). Old-style variable names don't error, they just silently fail to recolor anything — check for this pattern before assuming a themed element "doesn't support theming." Fixed instances: [`partials/_datatables-assets.blade.php`](../resources/views/partials/_datatables-assets.blade.php) (which also had DataTables **1.x** selectors — `.dataTables_wrapper`, `.paginate_button` — left over despite `datatables.net@2` being loaded; the correct v2 selectors are `.dt-container`, `.dt-search`, `.dt-length`, `.dt-paging .dt-paging-button`) and the machine-chat scrollbar in `machines/show.blade.php`.

**Theme-aware logo.** `logo-white.png` is unreadable on `bg-base-100` once that's white (the `corporate` default). [partials/_brand-logo.blade.php](../resources/views/partials/_brand-logo.blade.php) renders both `logo-black.png` and `logo-white.png` and a small CSS rule keyed off `[data-theme="forest"]` shows only the correct one — used everywhere except the login page's branding side-panel, which is a fixed dark surface (`bg-base-content text-base-100`) regardless of site theme, so it always uses the white logo directly.

## Auth pages

[layouts/guest/app.blade.php](../resources/views/layouts/guest/app.blade.php) and everything under `resources/views/auth/` are mobile-first: reduced vertical padding on small screens (`py-6 sm:py-10`), a compact footer below the `sm` breakpoint (tagline and nav links hidden, logo + copyright kept), and responsive card padding/icon sizing on the password-reset/verify pages (`w-16 h-16 sm:w-20 sm:h-20` icon circles, `p-6 sm:p-8` card bodies). The login page keeps its desktop split-screen (`lg:grid-cols-5`, branding panel `hidden lg:flex`) but tightens padding below `lg`. Registration is disabled (`Auth::routes(['register' => false])` in `routes/web.php`); `auth/register.blade.php` was deleted, and the two remaining `route('register')` links (`auth/login.blade.php`, `layouts/guest/app.blade.php`) are both wrapped in `@if(Route::has('register'))` and never render.

Classes with no effect in DaisyUI v5 (`form-control`, `label-text`, `label-text-alt`, `input-bordered` — `input` is bordered by default now) were replaced in the auth files touched during the mobile overhaul; this was later swept across the rest of the app (every form field now goes through the `<x-form.*>` components — see the "Form fields" section below) rather than being hand-fixed file by file.

## Form fields

Every form field in the app renders through an anonymous Blade component under `resources/views/components/form/`, rather than hand-rolled `form-control`/`label`/`input` markup. This is what fixed the DaisyUI v4→v5 label/input misalignment bug across the app (see the warning above) and is now the only supported way to build a form field:

| Component | Renders |
|---|---|
| `<x-form.input>` | A labelled text/number/date/etc. input. `name`, `label`, `type`, `value`, `help`, `required`, `span` (adds `md:col-span-2`), `oldKey`/`errorKey` (for an array-named field like `thresholds[metric][bound]`, where the html `name` isn't a valid `old()`/`$errors` key on its own). |
| `<x-form.select>` | A labelled `<select>`. `options` as a `value => label` array, or leave it out and provide `<option>`s directly in the slot (e.g. an Alpine `<template x-for>` list). |
| `<x-form.textarea>` | A labelled `<textarea>`. |
| `<x-form.toggle>` | The hidden-`0` + `checkbox`-`1` pattern — the only idiom that correctly persists an *unchecked* boolean, since an unchecked checkbox submits nothing at all. Use this for any boolean field, never a bare `<input type="checkbox">`. |
| `<x-form.checkbox>` | A caption-and-checkbox row (acknowledgements, "select all" style pickers) — not for a persisted boolean field, use `<x-form.toggle>` for that. |
| `<x-form.section>` | A card-wrapped group of fields with a title/subtitle/badge and a `grid grid-cols-1 md:grid-cols-2 gap-5` body. Pass `id` to make it a jump-rail anchor target (see `machines/_form.blade.php`). |
| `<x-form.errors>` | The `$errors->any()` summary banner. |
| `<x-form.actions>` | The Cancel/Submit (and optional destructive-link) footer row. |

Every control the components render carries `w-full`, every label is block-level (never relying on the dead `form-control`), and every error/help message is a block `<p>` — never an inline `<span>` that can end up sharing the control's line. `$attributes` passes through to the underlying control, so Alpine bindings (`x-model`, `@change`, `x-show`) work exactly as they would on a plain HTML element — **except** a bare colon-prefixed attribute like `:disabled="..."` collides with Blade's own dynamic-prop syntax when written directly on an `<x-form.*>` tag (Blade tries to evaluate the right-hand side as PHP). Use Alpine's long-form `x-bind:disabled="..."` instead in that one spot; nested slot content (e.g. `<option :value="...">` inside an `<x-form.select>` slot) is unaffected, since only the component tag's own attributes go through Blade's attribute compiler.

`machines/_form.blade.php` is the fullest example: a single shared partial included by both `machines/create.blade.php` and `machines/edit.blade.php`, replacing what used to be an ~800-line 4-step wizard duplicated (and already drifted) across both files. Machine `type`/`status`/fuel option lists live in `app/Enums/` (`MachineType`, `MachineStatus`, `FuelType`) rather than being hard-coded per view; `MachineType::specFields()` drives which spec fields the form shows for the selected type, mirrored in Alpine as `specFieldsByType` so the same map governs both the initial (server-rendered) and reactive (client-side show/hide) behavior.
