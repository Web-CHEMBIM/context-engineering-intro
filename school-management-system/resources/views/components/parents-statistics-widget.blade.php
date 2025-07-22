{{-- Parents Statistics Widget Component --}}
@props([
    'title' => 'Parents Statistics',
    'totalParents' => 0,
    'activeParents' => 0,
    'engagementRate' => 0,
    'meetingAttendance' => 0,
    'communicationPreferences' => [],
    'satisfactionScore' => 0
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Parent engagement and communication metrics</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--info-100); border-radius: var(--rounded-lg);">
            <i data-feather="users" class="feather font-info" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Main Statistics --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="text-center p-2" style="background-color: var(--info-50); border-radius: var(--rounded-md);">
                <h4 class="f-20 f-w-700 font-info mb-1">{{ $totalParents }}</h4>
                <p class="f-11 font-secondary mb-2">Total Parents</p>
                <span class="f-10 font-success">{{ $activeParents }} active</span>
            </div>
        </div>
        <div class="col-6">
            <div class="text-center p-2" style="background-color: var(--success-50); border-radius: var(--rounded-md);">
                <h4 class="f-20 f-w-700 font-success mb-1">{{ $satisfactionScore }}%</h4>
                <p class="f-11 font-secondary mb-2">Satisfaction</p>
                <span class="f-10 font-success">High rating</span>
            </div>
        </div>
    </div>
    
    {{-- Engagement Metrics --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="f-12 font-secondary">Engagement Rate</span>
            <span class="f-12 f-w-600 font-primary">{{ $engagementRate }}%</span>
        </div>
        <div class="progress mb-3" style="height: 6px; background-color: var(--primary-100);">
            <div class="progress-bar" 
                 role="progressbar" 
                 style="width: {{ $engagementRate }}%; background-color: var(--primary-500);"
                 aria-valuenow="{{ $engagementRate }}">
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="f-12 font-secondary">Meeting Attendance</span>
            <span class="f-12 f-w-600 font-success">{{ $meetingAttendance }}%</span>
        </div>
        <div class="progress" style="height: 6px; background-color: var(--success-100);">
            <div class="progress-bar" 
                 role="progressbar" 
                 style="width: {{ $meetingAttendance }}%; background-color: var(--success-500);"
                 aria-valuenow="{{ $meetingAttendance }}">
            </div>
        </div>
    </div>
    
    {{-- Communication Preferences --}}
    @if(count($communicationPreferences) > 0)
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Communication Preferences</p>
        <div class="row text-center">
            @foreach($communicationPreferences as $method => $percentage)
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--{{ $loop->index == 0 ? 'primary' : ($loop->index == 1 ? 'success' : 'warning') }}-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1 text-capitalize">{{ $method }}</p>
                <h6 class="f-12 f-w-600 font-{{ $loop->index == 0 ? 'primary' : ($loop->index == 1 ? 'success' : 'warning') }} mb-0">{{ $percentage }}%</h6>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>