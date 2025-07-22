{{-- New Enrolled Students Widget Component --}}
@props([
    'title' => 'New Enrolled Students',
    'students' => [],
    'totalNewStudents' => 0,
    'monthlyTarget' => 20,
    'conversionRate' => 0
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Recent student admissions and enrollment tracking</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--success-100); border-radius: var(--rounded-lg);">
            <i data-feather="user-plus" class="feather font-success" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Enrollment Progress --}}
    <div class="text-center mb-4 p-3" style="background-color: var(--success-50); border-radius: var(--rounded-lg);">
        <div class="d-flex justify-content-center align-items-center mb-2">
            <div class="me-3">
                <h3 class="f-24 f-w-700 font-success mb-0">{{ $totalNewStudents }}</h3>
                <p class="f-12 font-secondary mb-1">New Students</p>
            </div>
            <div class="text-start">
                <div class="progress" style="width: 60px; height: 8px; background-color: var(--success-100);">
                    <div class="progress-bar" 
                         style="width: {{ ($totalNewStudents / $monthlyTarget) * 100 }}%; background-color: var(--success-500);"
                         role="progressbar"></div>
                </div>
                <span class="f-10 font-secondary mt-1 d-block">Target: {{ $monthlyTarget }}</span>
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center">
            <i data-feather="trending-up" class="feather font-success me-1" style="width: 12px; height: 12px;"></i>
            <span class="f-10 font-success">{{ $conversionRate }}% conversion rate</span>
        </div>
    </div>
    
    @if(count($students) > 0)
    <div style="max-height: 200px; overflow-y: auto;">
        @foreach($students as $student)
        <div class="d-flex align-items-center mb-3 p-2" 
             style="background-color: var(--light-100); border-radius: var(--rounded-md);">
            <div class="me-3">
                <div class="d-flex align-items-center justify-content-center" 
                     style="width: 32px; height: 32px; background-color: var(--success-500); border-radius: 50%; color: white;">
                    <span class="f-12 f-w-700">{{ substr($student['name'], 0, 1) }}</span>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="f-12 f-w-500 font-primary mb-0">{{ $student['name'] }}</h6>
                    <span class="f-10 font-secondary">{{ $student['enrollment_date'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="f-11 font-secondary">{{ $student['class'] ?? 'N/A' }}</span>
                    <span class="badge bg-{{ $student['status'] === 'confirmed' ? 'success' : 'warning' }}" 
                          style="font-size: 8px; padding: 2px 6px;">
                        {{ ucfirst($student['status'] ?? 'pending') }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-3">
        <i data-feather="user-plus" class="feather font-secondary mb-2" style="width: 32px; height: 32px;"></i>
        <p class="f-12 font-secondary mb-0">No new enrollments this month</p>
    </div>
    @endif
</div>