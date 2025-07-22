{{-- Statistics Widget Component --}}
@props([
    'title',
    'value',
    'icon' => 'trending-up',
    'color' => 'primary',
    'trend' => null,
    'trendDirection' => 'up',
    'link' => null
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            <h6 class="f-14 f-w-500 font-secondary text-uppercase mb-2">{{ $title }}</h6>
            <h3 class="f-24 f-w-700 font-primary mb-1">{{ $value }}</h3>
            
            @if($trend)
            <div class="d-flex align-items-center">
                <i data-feather="{{ $trendDirection === 'up' ? 'trending-up' : 'trending-down' }}" 
                   class="feather me-1 {{ $trendDirection === 'up' ? 'font-success' : 'font-danger' }}" 
                   style="width: 16px; height: 16px;"></i>
                <span class="f-12 {{ $trendDirection === 'up' ? 'font-success' : 'font-danger' }}">
                    {{ $trend }}
                </span>
            </div>
            @endif
        </div>
        
        <div class="flex-shrink-0">
            <div class="d-flex align-items-center justify-content-center" 
                 style="width: 48px; height: 48px; background-color: var(--{{ $color }}-100); border-radius: var(--rounded-lg);">
                <i data-feather="{{ $icon }}" 
                   class="feather font-{{ $color }}" 
                   style="width: 24px; height: 24px;"></i>
            </div>
        </div>
    </div>
    
    @if($link)
    <div class="mt-3">
        <a href="{{ $link }}" class="f-12 font-primary text-decoration-none">
            View Details <i data-feather="arrow-right" style="width: 12px; height: 12px;"></i>
        </a>
    </div>
    @endif
</div>