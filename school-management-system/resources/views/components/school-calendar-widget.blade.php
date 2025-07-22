{{-- School Calendar Widget Component --}}
@props([
    'title' => 'School Calendar',
    'upcomingEvents' => [],
    'currentMonth' => null
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Upcoming events and important dates</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--warning-100); border-radius: var(--rounded-lg);">
            <i data-feather="calendar" class="feather font-warning" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Current Month Display --}}
    <div class="text-center mb-4 p-2" style="background-color: var(--warning-50); border-radius: var(--rounded-lg);">
        <h4 class="f-18 f-w-700 font-warning mb-0">{{ $currentMonth ?? now()->format('F Y') }}</h4>
        <span class="f-10 font-secondary">{{ count($upcomingEvents) }} events scheduled</span>
    </div>
    
    @if(count($upcomingEvents) > 0)
    <div style="max-height: 220px; overflow-y: auto;">
        @foreach($upcomingEvents as $event)
        <div class="d-flex align-items-start mb-3 p-2" 
             style="background-color: var(--light-100); border-radius: var(--rounded-md);">
            <div class="me-3 text-center" style="min-width: 40px;">
                <div class="d-flex flex-column align-items-center justify-content-center" 
                     style="width: 40px; height: 40px; background-color: var(--{{ $event['type'] === 'exam' ? 'danger' : ($event['type'] === 'event' ? 'success' : 'primary') }}-100); border-radius: var(--rounded-md);">
                    <span class="f-10 f-w-600 font-{{ $event['type'] === 'exam' ? 'danger' : ($event['type'] === 'event' ? 'success' : 'primary') }}">
                        {{ $event['day'] ?? date('d', strtotime($event['date'])) }}
                    </span>
                    <span class="f-8 font-secondary">{{ $event['month'] ?? date('M', strtotime($event['date'])) }}</span>
                </div>
            </div>
            <div class="flex-grow-1">
                <h6 class="f-12 f-w-500 font-primary mb-1">{{ $event['title'] }}</h6>
                <p class="f-11 font-secondary mb-1">{{ $event['description'] ?? '' }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="f-10 font-secondary">{{ $event['time'] ?? 'All day' }}</span>
                    <span class="badge bg-{{ $event['type'] === 'exam' ? 'danger' : ($event['type'] === 'event' ? 'success' : 'primary') }}" 
                          style="font-size: 8px; padding: 2px 6px;">
                        {{ ucfirst($event['type'] ?? 'event') }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-3">
        <i data-feather="calendar" class="feather font-secondary mb-2" style="width: 32px; height: 32px;"></i>
        <p class="f-12 font-secondary mb-0">No upcoming events</p>
    </div>
    @endif
    
    {{-- Quick Calendar Navigation --}}
    <div class="mt-4 pt-3 d-flex justify-content-center" style="border-top: 1px solid var(--secondary-200);">
        <button type="button" class="btn btn-sm btn-outline-primary" style="font-size: 11px;">
            <i data-feather="calendar" style="width: 12px; height: 12px;"></i>
            View Full Calendar
        </button>
    </div>
</div>