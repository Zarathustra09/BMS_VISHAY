<div class="space-y-6">
    <!-- Stats Cards Skeleton -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @for($i = 0; $i < 4; $i++)
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="skeleton h-12 w-12 shrink-0 rounded-lg"></div>
                    <div class="flex flex-col gap-2 flex-1">
                        <div class="skeleton h-4 w-20"></div>
                        <div class="skeleton h-6 w-16"></div>
                    </div>
                </div>
            </div>
        </div>
        @endfor
    </div>

    <!-- Charts Section Skeleton -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="skeleton h-6 w-48 mb-4"></div>
                <div class="skeleton h-64 w-full"></div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="skeleton h-6 w-48 mb-4"></div>
                <div class="skeleton h-64 w-full"></div>
            </div>
        </div>
    </div>

    <!-- Table/List Skeleton -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="skeleton h-6 w-48 mb-4"></div>
            <div class="space-y-3">
                @for($i = 0; $i < 5; $i++)
                <div class="flex items-center gap-4">
                    <div class="skeleton h-12 w-12 shrink-0 rounded-full"></div>
                    <div class="flex flex-col gap-2 flex-1">
                        <div class="skeleton h-4 w-full max-w-md"></div>
                        <div class="skeleton h-3 w-32"></div>
                    </div>
                    <div class="skeleton h-8 w-20 rounded-btn"></div>
                </div>
                @endfor
            </div>
        </div>
    </div>
</div>

