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
        /* Blend DataTables' own stylesheet with the DaisyUI "forest" theme rather than relying
           on Tailwind-generated utility classes for DOM DataTables injects at runtime — the
           Tailwind v4 browser CDN compiler only scans the DOM present at page load. */
        .dataTables_wrapper { color: inherit; }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            background: hsl(var(--b1));
            color: inherit;
            border: 1px solid hsl(var(--bc) / 0.2);
            border-radius: 0.5rem;
            padding: 0.25rem 0.5rem;
        }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter { color: inherit; opacity: 0.8; }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.5rem !important;
            color: inherit !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: hsl(var(--p)) !important;
            color: hsl(var(--pc)) !important;
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
