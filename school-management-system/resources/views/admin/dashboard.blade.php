@extends('layouts.master')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="f-24 f-w-700 font-primary mb-1">Admin Dashboard</h1>
                    <p class="f-14 font-secondary mb-0">
                        Welcome back! Here's what's happening at your school.
                        @if($currentAcademicYear) 
                        Academic Year: {{ $currentAcademicYear->name }}
                        @endif
                    </p>
                </div>
                <div class="d-flex align-items-center">
                    <span class="f-12 font-secondary me-3">Last updated: {{ now()->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Statistics Row --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <x-stats-widget 
                title="Total Students" 
                :value="$stats['total_students']" 
                icon="users" 
                color="primary"
                trend="+5 this month"
                trendDirection="up"
                link="{{ route('students.index') }}"
            />
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <x-stats-widget 
                title="Total Teachers" 
                :value="$stats['total_teachers']" 
                icon="user-check" 
                color="success"
                trend="+2 this month"
                trendDirection="up"
                link="{{ route('teachers.index') }}"
            />
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <x-stats-widget 
                title="Active Classes" 
                :value="$stats['total_classes']" 
                icon="grid" 
                color="warning"
                link="{{ route('classes.index') }}"
            />
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <x-stats-widget 
                title="Total Subjects" 
                :value="$stats['total_subjects']" 
                icon="book" 
                color="info"
                link="{{ route('subjects.index') }}"
            />
        </div>
    </div>

    {{-- Academic Performance and Charts Row --}}
    <div class="row mb-4">
        {{-- Academic Performance Widget --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <x-academic-performance-widget 
                title="Academic Performance"
                :overallPerformance="$academicPerformance['overall_performance']"
                :attendanceRate="$academicPerformance['attendance_rate']"
                :assignmentCompletion="$academicPerformance['assignment_completion']"
                :examAverage="$academicPerformance['exam_average']"
                :totalStudents="$stats['total_students']"
            />
        </div>
        
        {{-- School Performance Widget --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <x-school-performance-widget 
                title="School Performance"
                :overallRating="$schoolPerformance['overall_rating']"
                :academicScore="$schoolPerformance['academic_score']"
                :facilitiesScore="$schoolPerformance['facilities_score']"
                :teachingQuality="$schoolPerformance['teaching_quality']"
                :studentSatisfaction="$schoolPerformance['student_satisfaction']"
                :parentSatisfaction="$schoolPerformance['parent_satisfaction']"
                :totalReviews="$schoolPerformance['total_reviews']"
                :monthlyTrend="$schoolPerformance['monthly_trend']"
            />
        </div>
        
    </div>

    {{-- Second Performance Row --}}
    <div class="row mb-4">
        {{-- Students by Grade Level --}}
        <div class="col-lg-6 col-md-6 mb-4">
            <x-card title="Students by Grade" class="h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <p class="f-12 font-secondary mb-0">Distribution across grade levels</p>
                    <i data-feather="bar-chart-2" class="feather font-primary" style="width: 16px; height: 16px;"></i>
                </div>
                
                @forelse($studentsByGrade as $grade => $count)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="f-12 f-w-500">Grade {{ $grade }}</span>
                        <span class="f-12 f-w-600 font-primary">{{ $count }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" 
                             style="width: {{ $stats['total_students'] > 0 ? ($count / $stats['total_students']) * 100 : 0 }}%"
                             role="progressbar"></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-3">
                    <i data-feather="users" class="feather font-secondary mb-2" style="width: 32px; height: 32px;"></i>
                    <p class="f-12 font-secondary mb-0">No student data available</p>
                </div>
                @endforelse
            </x-card>
        </div>
        
        {{-- Quick Actions --}}
        <div class="col-lg-6 col-md-6 mb-4">
            <x-card title="Quick Actions" class="h-100">
                <div class="row g-2">
                    @foreach($quickActions as $action)
                    <div class="col-6">
                        <x-quick-action 
                            :title="$action['title']"
                            :icon="$action['icon']"
                            :color="$action['color']"
                            :href="$action['href']"
                            :count="$action['count'] ?? null"
                            size="sm"
                        />
                    </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>

    {{-- Bottom Row - Lists and Activities --}}
    <div class="row mb-4">
        {{-- Top Classes --}}
        <div class="col-lg-6 mb-4">
            <x-list-widget 
                title="Top Performing Classes"
                subtitle="Classes with highest enrollment"
                :items="$topClasses"
                viewAllUrl="{{ route('classes.index') }}"
            />
        </div>
        
        {{-- Recent Students --}}
        <div class="col-lg-6 mb-4">
            <x-list-widget 
                title="Recently Enrolled Students"
                subtitle="Latest student registrations"
                :items="$recentStudents"
                viewAllUrl="{{ route('students.index') }}"
            />
        </div>
    </div>

    {{-- Subject Statistics --}}
    <div class="row mb-4">
        <div class="col-12">
            <x-card title="Subject Statistics" subtitle="Teacher and student enrollment by subject">
                @if($subjectStats->count())
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="f-12 f-w-600 text-uppercase">Subject</th>
                                <th class="f-12 f-w-600 text-uppercase text-center">Teachers</th>
                                <th class="f-12 f-w-600 text-uppercase text-center">Students</th>
                                <th class="f-12 f-w-600 text-uppercase text-center">Ratio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjectStats as $subject)
                            <tr>
                                <td class="f-13 f-w-500">{{ $subject['name'] }}</td>
                                <td class="f-12 text-center">
                                    <span class="badge bg-primary">{{ $subject['teachers'] }}</span>
                                </td>
                                <td class="f-12 text-center">
                                    <span class="badge bg-success">{{ $subject['students'] }}</span>
                                </td>
                                <td class="f-12 text-center">
                                    @if($subject['teachers'] > 0)
                                    <span class="f-11 {{ $subject['students']/$subject['teachers'] > 20 ? 'font-danger' : 'font-success' }}">
                                        1:{{ round($subject['students']/$subject['teachers']) }}
                                    </span>
                                    @else
                                    <span class="f-11 font-secondary">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i data-feather="book" class="feather font-secondary mb-2" style="width: 32px; height: 32px;"></i>
                    <p class="f-14 font-secondary mb-0">No subject data available</p>
                </div>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize Feather Icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Add any dashboard-specific JavaScript here
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh dashboard every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000); // 5 minutes
});
</script>
@endpush