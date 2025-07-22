{{-- Attendance Tracking Widget Component --}}
@props([
    'title' => 'Attendance Tracking',
    'todayAttendance' => 0,
    'totalStudentsToday' => 0,
    'weeklyAverage' => 0,
    'monthlyAverage' => 0,
    'absentStudents' => [],
    'lateArrivals' => 0,
    'earlyDepartures' => 0,
    'attendanceTrend' => 'up',
    'classAttendance' => []
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Daily attendance monitoring and trends</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--success-100); border-radius: var(--rounded-lg);">
            <i data-feather="user-check" class="feather font-success" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Today's Attendance Summary --}}
    <div class="text-center mb-4 p-3" style="background-color: var(--success-50); border-radius: var(--rounded-lg);">
        <div class="d-flex justify-content-center align-items-center mb-2">
            <div class="me-3">
                <div style="width: 60px; height: 60px; position: relative;">
                    <svg style="width: 60px; height: 60px; transform: rotate(-90deg);">
                        <circle cx="30" cy="30" r="26" stroke="var(--success-200)" stroke-width="4" fill="transparent"/>
                        <circle cx="30" cy="30" r="26" 
                                stroke="var(--success-500)" 
                                stroke-width="4" 
                                fill="transparent"
                                stroke-dasharray="{{ 2 * pi() * 26 }}"
                                stroke-dashoffset="{{ $totalStudentsToday > 0 ? 2 * pi() * 26 * (1 - $todayAttendance / $totalStudentsToday) : 2 * pi() * 26 }}"
                                stroke-linecap="round"
                                style="transition: stroke-dashoffset 0.6s ease;"/>
                    </svg>
                    <div class="d-flex align-items-center justify-content-center" 
                         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                        <span class="f-14 f-w-700 font-success">{{ $totalStudentsToday > 0 ? round(($todayAttendance / $totalStudentsToday) * 100) : 0 }}%</span>
                    </div>
                </div>
            </div>
            <div class="text-start">
                <h4 class="f-20 f-w-700 font-success mb-0">{{ $todayAttendance }}/{{ $totalStudentsToday }}</h4>
                <p class="f-12 font-secondary mb-1">Present Today</p>
                <div class="d-flex align-items-center">
                    <i data-feather="{{ $attendanceTrend === 'up' ? 'trending-up' : 'trending-down' }}" 
                       class="feather {{ $attendanceTrend === 'up' ? 'font-success' : 'font-danger' }} me-1" 
                       style="width: 12px; height: 12px;"></i>
                    <span class="f-10 {{ $attendanceTrend === 'up' ? 'font-success' : 'font-danger' }}">
                        vs yesterday
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Attendance Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="text-center">
                <div class="d-flex align-items-center justify-content-center mb-2" 
                     style="width: 32px; height: 32px; background-color: var(--warning-100); border-radius: var(--rounded-md); margin: 0 auto;">
                    <i data-feather="clock" class="feather font-warning" style="width: 16px; height: 16px;"></i>
                </div>
                <h6 class="f-14 f-w-600 font-warning mb-1">{{ $lateArrivals }}</h6>
                <p class="f-10 font-secondary mb-0">Late Arrivals</p>
            </div>
        </div>
        <div class="col-4">
            <div class="text-center">
                <div class="d-flex align-items-center justify-content-center mb-2" 
                     style="width: 32px; height: 32px; background-color: var(--danger-100); border-radius: var(--rounded-md); margin: 0 auto;">
                    <i data-feather="x-circle" class="feather font-danger" style="width: 16px; height: 16px;"></i>
                </div>
                <h6 class="f-14 f-w-600 font-danger mb-1">{{ $totalStudentsToday - $todayAttendance }}</h6>
                <p class="f-10 font-secondary mb-0">Absent</p>
            </div>
        </div>
        <div class="col-4">
            <div class="text-center">
                <div class="d-flex align-items-center justify-content-center mb-2" 
                     style="width: 32px; height: 32px; background-color: var(--info-100); border-radius: var(--rounded-md); margin: 0 auto;">
                    <i data-feather="log-out" class="feather font-info" style="width: 16px; height: 16px;"></i>
                </div>
                <h6 class="f-14 f-w-600 font-info mb-1">{{ $earlyDepartures }}</h6>
                <p class="f-10 font-secondary mb-0">Early Leave</p>
            </div>
        </div>
    </div>
    
    {{-- Weekly vs Monthly Average --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="f-12 font-secondary">Weekly Average</span>
            <span class="f-12 f-w-600 font-primary">{{ $weeklyAverage }}%</span>
        </div>
        <div class="progress mb-3" style="height: 6px; background-color: var(--primary-100);">
            <div class="progress-bar" 
                 role="progressbar" 
                 style="width: {{ $weeklyAverage }}%; background-color: var(--primary-500); transition: width 0.6s ease;"
                 aria-valuenow="{{ $weeklyAverage }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="f-12 font-secondary">Monthly Average</span>
            <span class="f-12 f-w-600 font-success">{{ $monthlyAverage }}%</span>
        </div>
        <div class="progress" style="height: 6px; background-color: var(--success-100);">
            <div class="progress-bar" 
                 role="progressbar" 
                 style="width: {{ $monthlyAverage }}%; background-color: var(--success-500); transition: width 0.6s ease;"
                 aria-valuenow="{{ $monthlyAverage }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
    </div>
    
    {{-- Absent Students List --}}
    @if(count($absentStudents) > 0)
    <div class="mb-4">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Today's Absent Students</p>
        <div style="max-height: 140px; overflow-y: auto;">
            @foreach($absentStudents as $student)
            <div class="d-flex justify-content-between align-items-center mb-2 p-2" 
                 style="background-color: var(--danger-50); border-radius: var(--rounded-md);">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <div style="width: 8px; height: 8px; background-color: var(--danger-500); border-radius: 50%;"></div>
                    </div>
                    <div>
                        <h6 class="f-12 f-w-500 font-primary mb-0">{{ $student['name'] }}</h6>
                        <span class="f-10 font-secondary">{{ $student['class'] ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="text-end">
                    @if(!empty($student['reason']))
                    <span class="f-10 {{ $student['excused'] ? 'font-success' : 'font-danger' }}">
                        {{ $student['excused'] ? 'Excused' : 'Unexcused' }}
                    </span>
                    @else
                    <span class="f-10 font-secondary">No reason</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    {{-- Class-wise Attendance --}}
    @if(count($classAttendance) > 0)
    <div class="mb-4">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Class-wise Attendance</p>
        @foreach($classAttendance as $class)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="f-11 font-secondary">{{ $class['name'] }}</span>
            <div class="d-flex align-items-center">
                <span class="f-11 f-w-600 font-primary me-2">{{ $class['present'] }}/{{ $class['total'] }}</span>
                <div class="progress" style="width: 40px; height: 4px; background-color: var(--secondary-200);">
                    <div class="progress-bar {{ $class['percentage'] >= 90 ? 'bg-success' : ($class['percentage'] >= 75 ? 'bg-warning' : 'bg-danger') }}" 
                         style="width: {{ $class['percentage'] }}%"
                         role="progressbar"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    
    {{-- Attendance Status Summary --}}
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <div class="row text-center">
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--success-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Excellent</p>
                <h6 class="f-12 f-w-600 font-success mb-0">> 95%</h6>
            </div>
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--warning-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Good</p>
                <h6 class="f-12 f-w-600 font-warning mb-0">85-95%</h6>
            </div>
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--danger-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Poor</p>
                <h6 class="f-12 f-w-600 font-danger mb-0">< 85%</h6>
            </div>
        </div>
    </div>
    
    {{-- Footer Stats --}}
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i data-feather="calendar" class="feather font-info me-1" style="width: 12px; height: 12px;"></i>
            <span class="f-10 font-info">{{ now()->format('l, M d') }}</span>
        </div>
        <span class="f-10 font-secondary">Live tracking</span>
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