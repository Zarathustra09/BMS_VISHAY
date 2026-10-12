{{--
    The trail of what happened to alerts and who did it.
    Variables: $events (AlertEvent collection, newest first, with `user` loaded),
               $showAlert (bool, default false - also name the alert each entry belongs to).
--}}
@php
    $showAlert = $showAlert ?? false;
    $dotColor = fn (string $action) => match ($action) {
        'raised' => 'bg-error',
        'escalated' => 'bg-error',
        'acknowledged' => 'bg-warning',
        'resolved', 'auto_resolved', 'resolved_by_work_order', 'device_back_online' => 'bg-success',
        default => 'bg-base-300',
    };
@endphp

@if($events->isEmpty())
    <p class="text-sm opacity-60">Nothing has happened to this alert yet.</p>
@else
    <ol class="relative border-s border-base-300 ms-2 space-y-4" data-alert-trail>
        @foreach($events as $event)
            <li class="ms-4">
                <span class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full {{ $dotColor($event->action) }}"></span>
                <div class="text-sm">
                    <span class="font-semibold">{{ $event->actorName() }}</span>
                    <span class="opacity-80">· {{ $event->label() }}</span>
                </div>
                @if($showAlert && $event->alert)
                    <div class="text-xs opacity-80">{{ $event->alert->message }}</div>
                @endif
                <div class="text-xs opacity-60">
                    <time datetime="{{ $event->created_at->toIso8601String() }}" title="{{ $event->created_at->format('M d, Y H:i:s') }}">{{ $event->created_at->diffForHumans() }}</time>
                    @if($event->from_status && $event->to_status && $event->from_status !== $event->to_status)
                        · {{ $event->from_status }} → {{ $event->to_status }}
                    @elseif($event->to_status)
                        · {{ $event->to_status }}
                    @endif
                    @if($event->value !== null)
                        · reading {{ rtrim(rtrim((string) $event->value, '0'), '.') }}
                    @endif
                    @if($event->note)
                        · {{ $event->note }}
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
