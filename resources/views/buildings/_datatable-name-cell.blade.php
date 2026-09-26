<div class="flex items-center gap-3">
    <div class="avatar placeholder">
        <div class="bg-base-200 rounded-lg w-10 h-10">
            @if($building->image)
                <img src="{{ asset('storage/' . $building->image) }}" alt="{{ $building->name }}" class="rounded-lg object-cover w-10 h-10" />
            @else
                <svg class="w-5 h-5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            @endif
        </div>
    </div>
    <div>
        <div class="font-medium">{{ $building->name }}</div>
        <div class="text-xs opacity-60 font-mono">{{ $building->code }}</div>
    </div>
</div>
