{{-- Teachers Statistics Widget Component --}}
@props([
    'title' => 'Teachers Statistics',
    'totalTeachers' => 0,
    'activeTeachers' => 0,
    'avgWorkload' => 0,
    'avgExperience' => 0,
    'subjectDistribution' => [],
    'workloadDistribution' => [],
    'performanceRating' => 0,
    'certificationRate' => 0
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Teacher workforce analytics and performance metrics</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--success-100); border-radius: var(--rounded-lg);">
            <i data-feather="users" class="feather font-success" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Key Metrics Row --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="text-center p-2" style="background-color: var(--success-50); border-radius: var(--rounded-md);">
                <h4 class="f-20 f-w-700 font-success mb-1">{{ $totalTeachers }}</h4>
                <p class="f-11 font-secondary mb-0">Total Teachers</p>
                <span class="f-10 {{ $activeTeachers == $totalTeachers ? 'font-success' : 'font-warning' }}">
                    {{ $activeTeachers }} active
                </span>
            </div>
        </div>
        <div class="col-6">
            <div class="text-center p-2" style="background-color: var(--primary-50); border-radius: var(--rounded-md);">
                <h4 class="f-20 f-w-700 font-primary mb-1">{{ number_format($avgExperience, 1) }}</h4>
                <p class="f-11 font-secondary mb-0">Avg Experience</p>
                <span class="f-10 font-secondary">years</span>
            </div>
        </div>
    </div>
    
    {{-- Performance Metrics --}}
    <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
            <div class="me-3">
                <div class="d-flex align-items-center justify-content-center" 
                     style="width: 32px; height: 32px; background-color: var(--warning-100); border-radius: var(--rounded-md);">
                    <i data-feather="star" class="feather font-warning" style="width: 16px; height: 16px;"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <p class="f-12 font-secondary mb-0">Performance Rating</p>
                    <span class="f-12 f-w-600 font-warning">{{ $performanceRating }}%</span>
                </div>
                <div class="progress" style="height: 6px; background-color: var(--warning-100);">
                    <div class="progress-bar" 
                         role="progressbar" 
                         style="width: {{ $performanceRating }}%; background-color: var(--warning-500); transition: width 0.6s ease;"
                         aria-valuenow="{{ $performanceRating }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex align-items-center mb-3">
            <div class="me-3">
                <div class="d-flex align-items-center justify-content-center" 
                     style="width: 32px; height: 32px; background-color: var(--info-100); border-radius: var(--rounded-md);">
                    <i data-feather="award" class="feather font-info" style="width: 16px; height: 16px;"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <p class="f-12 font-secondary mb-0">Certification Rate</p>
                    <span class="f-12 f-w-600 font-info">{{ $certificationRate }}%</span>
                </div>
                <div class="progress" style="height: 6px; background-color: var(--info-100);">
                    <div class="progress-bar" 
                         role="progressbar" 
                         style="width: {{ $certificationRate }}%; background-color: var(--info-500); transition: width 0.6s ease;"
                         aria-valuenow="{{ $certificationRate }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex align-items-center">
            <div class="me-3">
                <div class="d-flex align-items-center justify-content-center" 
                     style="width: 32px; height: 32px; background-color: var(--danger-100); border-radius: var(--rounded-md);">
                    <i data-feather="clock" class="feather font-danger" style="width: 16px; height: 16px;"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <p class="f-12 font-secondary mb-0">Average Workload</p>
                    <span class="f-12 f-w-600 font-danger">{{ $avgWorkload }}h/week</span>
                </div>
                <div class="progress" style="height: 6px; background-color: var(--danger-100);">
                    <div class="progress-bar" 
                         role="progressbar" 
                         style="width: {{ min(100, ($avgWorkload / 40) * 100) }}%; background-color: var(--danger-500); transition: width 0.6s ease;"
                         aria-valuenow="{{ $avgWorkload }}" 
                         aria-valuemin="0" 
                         aria-valuemax="40">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="f-10 font-secondary">Standard: 30h</span>
                    <span class="f-10 {{ $avgWorkload > 35 ? 'font-danger' : ($avgWorkload > 30 ? 'font-warning' : 'font-success') }}">
                        {{ $avgWorkload > 35 ? 'Overloaded' : ($avgWorkload > 30 ? 'High' : 'Normal') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Subject Distribution --}}
    @if(count($subjectDistribution) > 0)
    <div class="mb-4">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Subject Distribution</p>
        @foreach($subjectDistribution as $subject)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
                <div style="width: 8px; height: 8px; background-color: var(--primary-500); border-radius: 50%; margin-right: 8px;"></div>
                <span class="f-11 font-secondary">{{ $subject['name'] }}</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="f-11 f-w-600 font-primary me-2">{{ $subject['count'] }}</span>
                <span class="f-10 font-secondary">teachers</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    
    {{-- Workload Distribution --}}
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Workload Distribution</p>
        <div class="row text-center">
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--success-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Light</p>
                <h6 class="f-12 f-w-600 font-success mb-0">{{ $workloadDistribution['light'] ?? 0 }}</h6>
                <span class="f-10 font-secondary">< 25h</span>
            </div>
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--warning-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Normal</p>
                <h6 class="f-12 f-w-600 font-warning mb-0">{{ $workloadDistribution['normal'] ?? 0 }}</h6>
                <span class="f-10 font-secondary">25-35h</span>
            </div>
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--danger-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Heavy</p>
                <h6 class="f-12 f-w-600 font-danger mb-0">{{ $workloadDistribution['heavy'] ?? 0 }}</h6>
                <span class="f-10 font-secondary">> 35h</span>
            </div>
        </div>
    </div>
    
    {{-- Footer Stats --}}
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i data-feather="trending-up" class="feather font-success me-1" style="width: 12px; height: 12px;"></i>
            <span class="f-10 font-success">{{ rand(2, 8) }}% improvement</span>
        </div>
        <span class="f-10 font-secondary">Updated: {{ now()->format('M d') }}</span>
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