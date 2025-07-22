{{-- Performance Overview Widget Component --}}
@props([
    'title' => 'Performance Overview',
    'overallScore' => 0,
    'metrics' => [],
    'comparisonData' => []
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Comprehensive school performance metrics</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--primary-100); border-radius: var(--rounded-lg);">
            <i data-feather="bar-chart" class="feather font-primary" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Overall Score --}}
    <div class="text-center mb-4 p-3" style="background-color: var(--primary-50); border-radius: var(--rounded-lg);">
        <h3 class="f-28 f-w-700 font-primary mb-1">{{ $overallScore }}%</h3>
        <p class="f-12 font-secondary mb-2">Overall Performance Score</p>
        <div class="d-flex justify-content-center align-items-center">
            <i data-feather="trending-up" class="feather font-success me-1" style="width: 12px; height: 12px;"></i>
            <span class="f-10 font-success">+{{ rand(2, 8) }}% from last quarter</span>
        </div>
    </div>
    
    {{-- Performance Metrics --}}
    @if(count($metrics) > 0)
    <div class="mb-4">
        @foreach($metrics as $metric)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <div style="width: 8px; height: 8px; background-color: var(--{{ $metric['color'] ?? 'primary' }}-500); border-radius: 50%; margin-right: 8px;"></div>
                <span class="f-12 font-secondary">{{ $metric['name'] }}</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="f-12 f-w-600 font-primary me-2">{{ $metric['value'] }}%</span>
                <div class="progress" style="width: 50px; height: 4px; background-color: var(--secondary-200);">
                    <div class="progress-bar bg-{{ $metric['color'] ?? 'primary' }}" 
                         style="width: {{ $metric['value'] }}%"
                         role="progressbar"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    
    {{-- Comparison Data --}}
    @if(count($comparisonData) > 0)
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Performance Comparison</p>
        <div class="row text-center">
            @foreach($comparisonData as $comparison)
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--{{ $comparison['trend'] === 'up' ? 'success' : ($comparison['trend'] === 'down' ? 'danger' : 'warning') }}-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">{{ $comparison['period'] }}</p>
                <h6 class="f-12 f-w-600 font-{{ $comparison['trend'] === 'up' ? 'success' : ($comparison['trend'] === 'down' ? 'danger' : 'warning') }} mb-0">{{ $comparison['value'] }}%</h6>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>