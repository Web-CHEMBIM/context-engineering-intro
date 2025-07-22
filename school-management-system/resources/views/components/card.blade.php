{{-- Reusable Card Component --}}
@props([
    'title' => null,
    'subtitle' => null,
    'headerActions' => null,
    'footer' => null
])

<div class="card {{ $attributes->get('class') }}">
    @if($title || $headerActions)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            @if($title)
            <h5 class="f-18 f-w-600 font-primary mb-0">{{ $title }}</h5>
            @endif
            @if($subtitle)
            <p class="f-14 font-secondary mb-0 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        
        @if($headerActions)
        <div class="flex-shrink-0">
            {{ $headerActions }}
        </div>
        @endif
    </div>
    @endif
    
    <div class="card-body p-0">
        {{ $slot }}
    </div>
    
    @if($footer)
    <div class="card-footer mt-3 pt-3" style="border-top: 1px solid var(--secondary-200);">
        {{ $footer }}
    </div>
    @endif
</div>