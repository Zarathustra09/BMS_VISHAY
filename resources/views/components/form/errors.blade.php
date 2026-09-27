@props([])

@if($errors->any())
<div class="alert alert-error shadow-lg animate-fadeIn">
    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
    </svg>
    <div>
        <p class="font-semibold">Please fix the following {{ Str::plural('issue', $errors->count()) }}:</p>
        <ul class="list-disc list-inside text-sm mt-1">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif
