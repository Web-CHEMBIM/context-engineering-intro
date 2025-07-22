{{-- Academic Performance Widget Component --}}
@props([
    'title' => 'Academic Performance',
    'overallPerformance' => 78,
    'attendanceRate' => 92,
    'assignmentCompletion' => 85,
    'examAverage' => 73,
    'totalStudents' => 0,
    'passingGrade' => 60
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Overall academic metrics and performance indicators</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--primary-100); border-radius: var(--rounded-lg);">
            <i data-feather="trending-up" class="feather font-primary" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Main Performance Score --}}
    <div class="text-center mb-4 p-3" style="background-color: var(--primary-50); border-radius: var(--rounded-lg);">
        <div class="d-flex align-items-center justify-content-center mb-2">
            <div style="width: 60px; height: 60px; position: relative;">
                <svg style="width: 60px; height: 60px; transform: rotate(-90deg);">
                    <circle cx="30" cy="30" r="26" stroke="var(--primary-200)" stroke-width="4" fill="transparent"/>
                    <circle cx="30" cy="30" r="26" 
                            stroke="var(--primary-500)" 
                            stroke-width="4" 
                            fill="transparent"
                            stroke-dasharray="{{ 2 * pi() * 26 }}"
                            stroke-dashoffset="{{ 2 * pi() * 26 * (1 - $overallPerformance / 100) }}"
                            stroke-linecap="round"
                            style="transition: stroke-dashoffset 0.6s ease;"/>
                </svg>
                <div class="d-flex align-items-center justify-content-center" 
                     style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    <span class="f-16 f-w-700 font-primary">{{ $overallPerformance }}%</span>
                </div>
            </div>
        </div>
        <h6 class="f-14 f-w-600 font-primary mb-0">Overall Performance</h6>
        <p class="f-10 font-secondary mb-0 mt-1">Based on {{ $totalStudents }} students</p>
    </div>
    
    {{-- Performance Metrics --}}
    <div class="row g-3">
        <div class="col-6">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--success-100); border-radius: var(--rounded-md);">
                        <i data-feather="users" class="feather font-success" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <p class="f-12 font-secondary mb-1">Attendance</p>
                    <div class="d-flex align-items-center">
                        <h6 class="f-14 f-w-600 font-success mb-0 me-2">{{ $attendanceRate }}%</h6>
                        @if($attendanceRate >= 90)
                        <i data-feather="trending-up" class="feather font-success" style="width: 12px; height: 12px;"></i>
                        @elseif($attendanceRate >= 80)
                        <i data-feather="minus" class="feather font-warning" style="width: 12px; height: 12px;"></i>
                        @else
                        <i data-feather="trending-down" class="feather font-danger" style="width: 12px; height: 12px;"></i>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--warning-100); border-radius: var(--rounded-md);">
                        <i data-feather="clipboard-check" class="feather font-warning" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <p class="f-12 font-secondary mb-1">Assignments</p>
                    <div class="d-flex align-items-center">
                        <h6 class="f-14 f-w-600 font-warning mb-0 me-2">{{ $assignmentCompletion }}%</h6>
                        @if($assignmentCompletion >= 90)
                        <i data-feather="trending-up" class="feather font-success" style="width: 12px; height: 12px;"></i>
                        @elseif($assignmentCompletion >= 75)
                        <i data-feather="minus" class="feather font-warning" style="width: 12px; height: 12px;"></i>
                        @else
                        <i data-feather="trending-down" class="feather font-danger" style="width: 12px; height: 12px;"></i>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 mt-3">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--info-100); border-radius: var(--rounded-md);">
                        <i data-feather="file-text" class="feather font-info" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <p class="f-12 font-secondary mb-0">Exam Average</p>
                        <span class="f-12 f-w-600 font-info">{{ $examAverage }}%</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--info-100);">
                        <div class="progress-bar" 
                             role="progressbar" 
                             style="width: {{ ($examAverage / 100) * 100 }}%; background-color: var(--info-500); transition: width 0.6s ease;"
                             aria-valuenow="{{ $examAverage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="f-10 font-secondary">Passing: {{ $passingGrade }}%</span>
                        <span class="f-10 {{ $examAverage >= $passingGrade ? 'font-success' : 'font-danger' }}">
                            {{ $examAverage >= $passingGrade ? 'Above Average' : 'Needs Improvement' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Quick Stats Footer --}}
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <div class="row text-center">
            <div class="col-4">
                <p class="f-10 font-secondary mb-1">Grade A</p>
                <h6 class="f-12 f-w-600 font-success mb-0">{{ round($totalStudents * 0.15) }}</h6>
            </div>
            <div class="col-4">
                <p class="f-10 font-secondary mb-1">Grade B-C</p>
                <h6 class="f-12 f-w-600 font-warning mb-0">{{ round($totalStudents * 0.6) }}</h6>
            </div>
            <div class="col-4">
                <p class="f-10 font-secondary mb-1">Below C</p>
                <h6 class="f-12 f-w-600 font-danger mb-0">{{ round($totalStudents * 0.25) }}</h6>
            </div>
        </div>
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