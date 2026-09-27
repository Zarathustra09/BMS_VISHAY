@props([
    'cancelHref',
    'cancelLabel' => 'Cancel',
    'submitLabel' => 'Save',
    'destructiveHref' => null,
    'destructiveLabel' => 'Delete',
    // Alpine boolean expression (e.g. "submitting") to show a spinner while the form posts.
    'submitting' => null,
])

<div {{ $attributes->class(['flex items-center gap-3 pt-4 border-t border-base-300', 'justify-between' => $destructiveHref, 'justify-end' => ! $destructiveHref]) }}>
    @if($destructiveHref)
        <a href="{{ $destructiveHref }}" class="btn btn-error btn-outline gap-2">{{ $destructiveLabel }}</a>
    @endif

    <div class="flex items-center gap-3">
        <a href="{{ $cancelHref }}" class="btn btn-ghost gap-2">{{ $cancelLabel }}</a>
        <button type="submit" class="btn btn-primary gap-2" @if($submitting) :disabled="{{ $submitting }}" @endif>
            @if($submitting)
                <span x-show="!({{ $submitting }})">{{ $submitLabel }}</span>
                <span x-show="{{ $submitting }}" class="loading loading-spinner loading-sm"></span>
            @else
                {{ $submitLabel }}
            @endif
        </button>
    </div>
</div>
