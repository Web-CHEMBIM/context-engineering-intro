{{-- List Widget Component - For displaying lists like recent students, notifications, etc. --}}
@props([
    'title',
    'items' => [],
    'emptyMessage' => 'No items to display',
    'showAll' => null
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="f-16 f-w-600 font-primary mb-0">{{ $title }}</h6>
        @if($showAll)
        <a href="{{ $showAll }}" class="f-12 font-primary text-decoration-none">
            View All <i data-feather="arrow-right" style="width: 12px; height: 12px;"></i>
        </a>
        @endif
    </div>
    
    <div class="list-group list-group-flush">
        @forelse($items as $item)
        <div class="list-group-item border-0 p-0 mb-3">
            <div class="d-flex align-items-center">
                @if(isset($item['avatar']))
                <img src="{{ $item['avatar'] }}" alt="Avatar" class="avatar avatar-sm me-3">
                @elseif(isset($item['icon']))
                <div class="flex-shrink-0 me-3">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--{{ $item['iconColor'] ?? 'primary' }}-100); border-radius: var(--rounded-md);">
                        <i data-feather="{{ $item['icon'] }}" 
                           class="feather font-{{ $item['iconColor'] ?? 'primary' }}" 
                           style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                @endif
                
                <div class="flex-grow-1">
                    <h6 class="f-14 f-w-500 mb-1">{{ $item['title'] }}</h6>
                    @if(isset($item['subtitle']))
                    <p class="f-12 font-secondary mb-0">{{ $item['subtitle'] }}</p>
                    @endif
                </div>
                
                @if(isset($item['badge']))
                <div class="flex-shrink-0">
                    <span class="badge badge-{{ $item['badgeColor'] ?? 'primary' }}">
                        {{ $item['badge'] }}
                    </span>
                </div>
                @endif
                
                @if(isset($item['time']))
                <div class="flex-shrink-0 ms-2">
                    <small class="f-11 font-secondary">{{ $item['time'] }}</small>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center p-30">
            <i data-feather="inbox" class="feather mb-2" style="width: 24px; height: 24px; opacity: 0.5;"></i>
            <p class="f-12 font-secondary mb-0">{{ $emptyMessage }}</p>
        </div>
        @endforelse
    </div>
</div>