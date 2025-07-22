{{-- Students Statistics Widget Component --}}
@props([
    'title' => 'Students Statistics',
    'totalStudents' => 0,
    'activeStudents' => 0,
    'enrollmentTrend' => 'up',
    'gradeDistribution' => [],
    'genderDistribution' => [],
    'avgAge' => 0,
    'attendanceRate' => 0,
    'graduationRate' => 0,
    'newEnrollments' => 0
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Student population analytics and enrollment metrics</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--primary-100); border-radius: var(--rounded-lg);">
            <i data-feather="users" class="feather font-primary" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Main Statistics --}}
    <div class="text-center mb-4 p-3" style="background-color: var(--primary-50); border-radius: var(--rounded-lg);">
        <div class="row">
            <div class="col-6">
                <h3 class="f-24 f-w-700 font-primary mb-1">{{ $totalStudents }}</h3>
                <p class="f-11 font-secondary mb-2">Total Students</p>
                <div class="d-flex align-items-center justify-content-center">
                    <i data-feather="{{ $enrollmentTrend === 'up' ? 'trending-up' : 'trending-down' }}" 
                       class="feather {{ $enrollmentTrend === 'up' ? 'font-success' : 'font-danger' }} me-1" 
                       style="width: 12px; height: 12px;"></i>
                    <span class="f-10 {{ $enrollmentTrend === 'up' ? 'font-success' : 'font-danger' }}">
                        {{ $enrollmentTrend === 'up' ? '+' : '-' }}{{ $newEnrollments }} this month
                    </span>
                </div>
            </div>
            <div class="col-6">
                <h3 class="f-24 f-w-700 font-success mb-1">{{ $activeStudents }}</h3>
                <p class="f-11 font-secondary mb-2">Active Students</p>
                <span class="f-10 {{ $activeStudents == $totalStudents ? 'font-success' : 'font-warning' }}">
                    {{ number_format(($activeStudents / max($totalStudents, 1)) * 100, 1) }}% active rate
                </span>
            </div>
        </div>
    </div>
    
    {{-- Key Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="d-flex align-items-center">
                <div class="me-2">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 28px; height: 28px; background-color: var(--info-100); border-radius: var(--rounded-md);">
                        <i data-feather="calendar" class="feather font-info" style="width: 14px; height: 14px;"></i>
                    </div>
                </div>
                <div>
                    <p class="f-11 font-secondary mb-0">Avg Age</p>
                    <h6 class="f-14 f-w-600 font-info mb-0">{{ $avgAge }} years</h6>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="d-flex align-items-center">
                <div class="me-2">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 28px; height: 28px; background-color: var(--warning-100); border-radius: var(--rounded-md);">
                        <i data-feather="check-circle" class="feather font-warning" style="width: 14px; height: 14px;"></i>
                    </div>
                </div>
                <div>
                    <p class="f-11 font-secondary mb-0">Graduation</p>
                    <h6 class="f-14 f-w-600 font-warning mb-0">{{ $graduationRate }}%</h6>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Attendance Rate --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
                <i data-feather="check-square" class="feather font-success me-2" style="width: 16px; height: 16px;"></i>
                <span class="f-12 font-secondary">Attendance Rate</span>
            </div>
            <span class="f-12 f-w-600 font-success">{{ $attendanceRate }}%</span>
        </div>
        <div class="progress" style="height: 8px; background-color: var(--success-100);">
            <div class="progress-bar" 
                 role="progressbar" 
                 style="width: {{ $attendanceRate }}%; background-color: var(--success-500); transition: width 0.6s ease;"
                 aria-valuenow="{{ $attendanceRate }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <span class="f-10 font-secondary">Target: 95%</span>
            <span class="f-10 {{ $attendanceRate >= 95 ? 'font-success' : ($attendanceRate >= 90 ? 'font-warning' : 'font-danger') }}">
                {{ $attendanceRate >= 95 ? 'Excellent' : ($attendanceRate >= 90 ? 'Good' : 'Needs Improvement') }}
            </span>
        </div>
    </div>
    
    {{-- Grade Distribution --}}
    @if(count($gradeDistribution) > 0)
    <div class="mb-4">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Grade Distribution</p>
        @foreach($gradeDistribution as $grade => $count)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
                <div style="width: 8px; height: 8px; background-color: var(--primary-{{ 300 + ($loop->index * 100) }}); border-radius: 50%; margin-right: 8px;"></div>
                <span class="f-11 font-secondary">Grade {{ $grade }}</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="f-11 f-w-600 font-primary me-2">{{ $count }}</span>
                <div class="progress" style="width: 40px; height: 4px; background-color: var(--secondary-200);">
                    <div class="progress-bar bg-primary" 
                         style="width: {{ $totalStudents > 0 ? ($count / $totalStudents) * 100 : 0 }}%"
                         role="progressbar"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    
    {{-- Gender Distribution --}}
    @if(count($genderDistribution) > 0)
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Gender Distribution</p>
        <div class="row text-center">
            @foreach($genderDistribution as $gender => $count)
            <div class="col-{{ count($genderDistribution) == 2 ? '6' : '4' }}">
                <div class="mb-2">
                    <div style="width: 24px; height: 24px; background-color: var(--{{ $gender === 'male' ? 'primary' : ($gender === 'female' ? 'danger' : 'warning') }}-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-11 font-secondary mb-1 text-capitalize">{{ $gender }}</p>
                <h6 class="f-14 f-w-600 font-{{ $gender === 'male' ? 'primary' : ($gender === 'female' ? 'danger' : 'warning') }} mb-0">{{ $count }}</h6>
                <span class="f-10 font-secondary">{{ $totalStudents > 0 ? number_format(($count / $totalStudents) * 100, 1) : 0 }}%</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    {{-- Footer Stats --}}
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i data-feather="user-plus" class="feather font-primary me-1" style="width: 12px; height: 12px;"></i>
            <span class="f-10 font-primary">{{ $newEnrollments }} new this month</span>
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