{{-- School Performance Widget Component --}}
@props([
    'title' => 'School Performance',
    'overallRating' => 4.5,
    'academicScore' => 85,
    'facilitiesScore' => 78,
    'teachingQuality' => 92,
    'studentSatisfaction' => 88,
    'parentSatisfaction' => 83,
    'totalReviews' => 156,
    'monthlyTrend' => 'up'
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Overall school performance metrics and ratings</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--primary-100); border-radius: var(--rounded-lg);">
            <i data-feather="award" class="feather font-primary" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Overall Rating --}}
    <div class="text-center mb-4 p-3" style="background-color: var(--success-50); border-radius: var(--rounded-lg);">
        <div class="d-flex align-items-center justify-content-center mb-2">
            <div class="me-3">
                <h2 class="f-32 f-w-700 font-success mb-0">{{ $overallRating }}</h2>
                <div class="d-flex justify-content-center mb-1">
                    @for($i = 1; $i <= 5; $i++)
                    <i data-feather="star" class="feather {{ $i <= floor($overallRating) ? 'font-warning' : 'font-secondary' }}" 
                       style="width: 14px; height: 14px; fill: {{ $i <= floor($overallRating) ? 'var(--warning-500)' : 'transparent' }};"></i>
                    @endfor
                </div>
            </div>
            <div class="text-start">
                <p class="f-12 font-secondary mb-1">Out of 5.0</p>
                <p class="f-10 font-secondary mb-0">{{ $totalReviews }} reviews</p>
                <div class="d-flex align-items-center mt-1">
                    <i data-feather="{{ $monthlyTrend === 'up' ? 'trending-up' : 'trending-down' }}" 
                       class="feather {{ $monthlyTrend === 'up' ? 'font-success' : 'font-danger' }}" 
                       style="width: 12px; height: 12px;"></i>
                    <span class="f-10 {{ $monthlyTrend === 'up' ? 'font-success' : 'font-danger' }} ms-1">
                        This month
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Performance Metrics --}}
    <div class="row g-3">
        {{-- Academic Excellence --}}
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--primary-100); border-radius: var(--rounded-md);">
                        <i data-feather="book-open" class="feather font-primary" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <p class="f-12 font-secondary mb-0">Academic Excellence</p>
                        <span class="f-12 f-w-600 font-primary">{{ $academicScore }}%</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--primary-100);">
                        <div class="progress-bar" 
                             role="progressbar" 
                             style="width: {{ $academicScore }}%; background-color: var(--primary-500); transition: width 0.6s ease;"
                             aria-valuenow="{{ $academicScore }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Teaching Quality --}}
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--success-100); border-radius: var(--rounded-md);">
                        <i data-feather="users" class="feather font-success" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <p class="f-12 font-secondary mb-0">Teaching Quality</p>
                        <span class="f-12 f-w-600 font-success">{{ $teachingQuality }}%</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--success-100);">
                        <div class="progress-bar" 
                             role="progressbar" 
                             style="width: {{ $teachingQuality }}%; background-color: var(--success-500); transition: width 0.6s ease;"
                             aria-valuenow="{{ $teachingQuality }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Facilities --}}
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--warning-100); border-radius: var(--rounded-md);">
                        <i data-feather="home" class="feather font-warning" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <p class="f-12 font-secondary mb-0">Facilities & Infrastructure</p>
                        <span class="f-12 f-w-600 font-warning">{{ $facilitiesScore }}%</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--warning-100);">
                        <div class="progress-bar" 
                             role="progressbar" 
                             style="width: {{ $facilitiesScore }}%; background-color: var(--warning-500); transition: width 0.6s ease;"
                             aria-valuenow="{{ $facilitiesScore }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Satisfaction Scores --}}
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Satisfaction Scores</p>
        <div class="row text-center">
            <div class="col-6">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i data-feather="smile" class="feather font-info me-2" style="width: 16px; height: 16px;"></i>
                    <span class="f-12 font-secondary">Students</span>
                </div>
                <h6 class="f-16 f-w-700 font-info mb-0">{{ $studentSatisfaction }}%</h6>
                <div class="progress mt-1" style="height: 4px;">
                    <div class="progress-bar bg-info" 
                         style="width: {{ $studentSatisfaction }}%"
                         role="progressbar"></div>
                </div>
            </div>
            <div class="col-6">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i data-feather="heart" class="feather font-danger me-2" style="width: 16px; height: 16px;"></i>
                    <span class="f-12 font-secondary">Parents</span>
                </div>
                <h6 class="f-16 f-w-700 font-danger mb-0">{{ $parentSatisfaction }}%</h6>
                <div class="progress mt-1" style="height: 4px;">
                    <div class="progress-bar bg-danger" 
                         style="width: {{ $parentSatisfaction }}%"
                         role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Performance Indicators Footer --}}
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="d-flex align-items-center me-3">
                <div style="width: 8px; height: 8px; background-color: var(--success-500); border-radius: 50%; margin-right: 4px;"></div>
                <span class="f-10 font-secondary">Excellent</span>
            </div>
            <div class="d-flex align-items-center me-3">
                <div style="width: 8px; height: 8px; background-color: var(--warning-500); border-radius: 50%; margin-right: 4px;"></div>
                <span class="f-10 font-secondary">Good</span>
            </div>
            <div class="d-flex align-items-center">
                <div style="width: 8px; height: 8px; background-color: var(--danger-500); border-radius: 50%; margin-right: 4px;"></div>
                <span class="f-10 font-secondary">Needs Improvement</span>
            </div>
        </div>
        <span class="f-10 font-secondary">Updated today</span>
    </div>
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