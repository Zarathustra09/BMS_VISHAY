@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'badgeClass' => 'badge-ghost',
    'id' => null,
])

<div {{ $attributes->class(['card bg-base-100 shadow-lg border border-base-300/50', 'scroll-mt-24' => $id]) }} @if($id) id="{{ $id }}" @endif>
    <div class="card-body">
        <div class="flex items-start gap-3">
            @isset($icon)
                <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center shrink-0 text-primary">
                    {{ $icon }}
                </div>
            @endisset

            <div class="flex-1 min-w-0">
                <h2 class="card-title text-base">{{ $title }}</h2>
                @if($subtitle)
                    <p class="text-sm opacity-60">{{ $subtitle }}</p>
                @endif
            </div>

            @if($badge)
                <span class="badge badge-sm {{ $badgeClass }} shrink-0">{{ $badge }}</span>
            @endif
        </div>

        <div class="divider my-0"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{ $slot }}
        </div>
    </div>
</div>
