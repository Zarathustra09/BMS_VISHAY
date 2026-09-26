{{--
    DataTables assets: jQuery + DataTables (CDN).

    yajra/laravel-datatables (installed via composer) is server-side PHP only — it builds the
    JSON response. It ships no browser JS/CSS. This project had no jQuery and no DataTables JS
    at all before this, so both come from CDN here, included only on pages that render a table
    (not globally in the layout).

    @once ensures these only get pushed once even if a page somehow includes this partial
    more than once.
--}}
@once
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-dt@2/css/dataTables.dataTables.min.css">
    <style>
        /* Blend DataTables' own stylesheet with whichever DaisyUI theme is active (light
           "corporate" by default, dark "forest" via the header toggle) rather than relying
           on Tailwind-generated utility classes for DOM DataTables injects at runtime — the
           Tailwind v4 browser CDN compiler only scans the DOM present at page load.
           DaisyUI v5 color variables (--color-base-100 etc.), not the old v4 hsl(var(--b1))
           names. Selectors are DataTables 2.x's own (.dt-*) — v1.x used .dataTables_wrapper
           etc., which don't exist in the datatables.net@2 build loaded below. */
        .dt-container { color: inherit; }
        .dt-search input,
        .dt-length select {
            background: var(--color-base-100);
            color: inherit;
            border: 1px solid color-mix(in oklab, var(--color-base-content) 20%, transparent);
            border-radius: 0.5rem;
            padding: 0.25rem 0.5rem;
        }
        .dt-info,
        .dt-length,
        .dt-search { color: inherit; opacity: 0.8; }
        .dt-paging .dt-paging-button {
            border-radius: 0.5rem !important;
            color: inherit !important;
        }
        .dt-paging .dt-paging-button.current {
            background: var(--color-primary) !important;
            color: var(--color-primary-content) !important;
            border-color: transparent !important;
        }
        table.dataTable tbody tr { background: transparent; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@2/js/dataTables.min.js"></script>
    <script>
        // The layout hides #actual-content (display:none) and reveals it ~300ms after
        // window `load` (see layouts/app.blade.php). A table initialised while hidden
        // measures zero-width columns. Every page using DataTables sets autoWidth:false
        // as the primary fix; this is a safety net that re-measures once visible.
        window.addEventListener('load', function () {
            setTimeout(function () {
                if (window.jQuery && jQuery.fn.dataTable) {
                    jQuery.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
                }
            }, 400);
        });
    </script>
@endpush
@endonce
