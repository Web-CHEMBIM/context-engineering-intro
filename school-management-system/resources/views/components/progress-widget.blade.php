{{-- Progress Widget Component - For displaying progress bars and performance metrics --}}
@props([
    'title',
    'percentage' => 0,
    'label' => null,
    'color' => 'primary',
    'showPercentage' => true,
    'size' => 'md',
    'subtitle' => null
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h6 class="f-16 f-w-600 font-primary mb-0">{{ $title }}</h6>
                @if($subtitle)
                <p class="f-12 font-secondary mb-0 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
            @if($showPercentage)
            <span class="f-14 f-w-600 font-{{ $color }}">{{ $percentage }}%</span>
            @endif
        </div>
        
        @if($label)
        <p class="f-12 font-secondary mb-2">{{ $label }}</p>
        @endif
    </div>
    
    <div class="progress {{ $size === 'sm' ? 'progress-sm' : ($size === 'lg' ? 'progress-lg' : '') }}" 
         style="height: {{ $size === 'sm' ? '4px' : ($size === 'lg' ? '12px' : '8px') }}; background-color: var(--{{ $color }}-100);">
        <div class="progress-bar" 
             role="progressbar" 
             style="width: {{ $percentage }}%; background-color: var(--{{ $color }}-500); transition: width 0.6s ease;"
             aria-valuenow="{{ $percentage }}" 
             aria-valuemin="0" 
             aria-valuemax="100">
        </div>
    </div>
    
    {{ $slot }}
</div>

<style>
.progress {
    border-radius: var(--rounded-full);
    overflow: hidden;
}

.progress-bar {
    border-radius: var(--rounded-full);
}
</style>