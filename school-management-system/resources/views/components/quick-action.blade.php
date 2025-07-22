{{-- Quick Action Component - For dashboard quick actions and shortcuts --}}
@props([
    'title',
    'icon',
    'color' => 'primary',
    'href' => '#',
    'count' => null,
    'badge' => null
])

<a href="{{ $href }}" class="text-decoration-none {{ $attributes->get('class') }}">
    <div class="widget text-center position-relative" 
         style="transition: var(--transition-all); cursor: pointer;">
        
        @if($badge)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-{{ $color }}" 
              style="font-size: 10px; z-index: 10;">
            {{ $badge }}
        </span>
        @endif
        
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-center mx-auto" 
                 style="width: 64px; height: 64px; background-color: var(--{{ $color }}-100); border-radius: var(--rounded-xl);">
                <i data-feather="{{ $icon }}" 
                   class="feather font-{{ $color }}" 
                   style="width: 32px; height: 32px;"></i>
            </div>
        </div>
        
        <h6 class="f-14 f-w-500 font-primary mb-1">{{ $title }}</h6>
        
        @if($count !== null)
        <p class="f-24 f-w-700 font-{{ $color }} mb-0">{{ $count }}</p>
        @endif
        
        {{ $slot }}
    </div>
</a>

<style>
.quick-action-hover:hover .widget {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
</style>