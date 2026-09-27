@extends('layouts.app')

@section('title', 'Delete Work Order — ' . $workOrder->code)

@section('breadcrumbs')
    <li><a href="{{ route('work-orders.index') }}">Work Orders</a></li>
    <li><a href="{{ route('work-orders.show', $workOrder) }}">{{ $workOrder->code }}</a></li>
    <li>Delete</li>
@endsection

@section('back', route('work-orders.show', $workOrder))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-error">Delete Work Order</h1>
            <p class="text-sm opacity-70 mt-1">This action cannot be undone</p>
        </div>
    </div>

    <div class="alert alert-error shadow-lg">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <div>
            <p class="font-bold text-base">You are about to permanently delete this work order</p>
            <p class="text-sm opacity-90 mt-0.5">
                <span class="font-semibold">{{ $workOrder->code }} — {{ $workOrder->title }}</span> will be removed.
                @if($workOrder->alert)
                    Its linked alert will <strong>not</strong> be automatically resolved.
                @endif
            </p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl border border-error/30" x-data="{ confirmed: false, deleting: false }">
        <div class="card-body space-y-5">
            <label class="flex items-start gap-3 p-4 rounded-xl border border-base-300 bg-base-200/50 cursor-pointer hover:bg-base-200 transition-colors"
                   :class="confirmed ? 'border-error/40 bg-error/5' : ''">
                <input type="checkbox" class="checkbox checkbox-error mt-0.5" x-model="confirmed"/>
                <div>
                    <p class="font-semibold text-sm">I understand this is permanent and irreversible</p>
                </div>
            </label>

            <div class="flex items-center justify-between pt-2 border-t border-base-300">
                <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-ghost gap-2">Cancel</a>

                <form action="{{ route('work-orders.destroy', $workOrder) }}" method="POST"
                      @submit.prevent="deleting = true; $el.submit()">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error gap-2" :disabled="!confirmed || deleting">
                        <span x-show="deleting" class="loading loading-spinner loading-sm"></span>
                        <span x-text="deleting ? 'Deleting...' : 'Delete Work Order'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
