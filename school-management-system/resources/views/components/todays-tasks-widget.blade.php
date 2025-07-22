{{-- Today's Tasks Widget Component --}}
@props([
    'title' => "Today's Tasks",
    'tasks' => [],
    'completedTasks' => 0,
    'totalTasks' => 0,
    'highPriorityTasks' => 0,
    'overdueTasks' => 0
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Daily administrative tasks and priorities</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--info-100); border-radius: var(--rounded-lg);">
            <i data-feather="check-square" class="feather font-info" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Task Progress Summary --}}
    <div class="text-center mb-4 p-3" style="background-color: var(--info-50); border-radius: var(--rounded-lg);">
        <div class="d-flex justify-content-center align-items-center mb-2">
            <div class="me-3">
                <div style="width: 50px; height: 50px; position: relative;">
                    <svg style="width: 50px; height: 50px; transform: rotate(-90deg);">
                        <circle cx="25" cy="25" r="20" stroke="var(--info-200)" stroke-width="3" fill="transparent"/>
                        <circle cx="25" cy="25" r="20" 
                                stroke="var(--info-500)" 
                                stroke-width="3" 
                                fill="transparent"
                                stroke-dasharray="{{ 2 * pi() * 20 }}"
                                stroke-dashoffset="{{ $totalTasks > 0 ? 2 * pi() * 20 * (1 - $completedTasks / $totalTasks) : 2 * pi() * 20 }}"
                                stroke-linecap="round"
                                style="transition: stroke-dashoffset 0.6s ease;"/>
                    </svg>
                    <div class="d-flex align-items-center justify-content-center" 
                         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                        <span class="f-12 f-w-700 font-info">{{ $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0 }}%</span>
                    </div>
                </div>
            </div>
            <div class="text-start">
                <h4 class="f-20 f-w-700 font-info mb-0">{{ $completedTasks }}/{{ $totalTasks }}</h4>
                <p class="f-11 font-secondary mb-1">Tasks Completed</p>
                <span class="f-10 font-info">{{ $totalTasks - $completedTasks }} remaining</span>
            </div>
        </div>
    </div>
    
    {{-- Task Alerts --}}
    @if($overdueTasks > 0 || $highPriorityTasks > 0)
    <div class="row g-2 mb-4">
        @if($overdueTasks > 0)
        <div class="col-6">
            <div class="d-flex align-items-center p-2" style="background-color: var(--danger-50); border-radius: var(--rounded-md);">
                <i data-feather="alert-circle" class="feather font-danger me-2" style="width: 16px; height: 16px;"></i>
                <div>
                    <p class="f-10 font-secondary mb-0">Overdue</p>
                    <h6 class="f-14 f-w-600 font-danger mb-0">{{ $overdueTasks }}</h6>
                </div>
            </div>
        </div>
        @endif
        @if($highPriorityTasks > 0)
        <div class="col-6">
            <div class="d-flex align-items-center p-2" style="background-color: var(--warning-50); border-radius: var(--rounded-md);">
                <i data-feather="zap" class="feather font-warning me-2" style="width: 16px; height: 16px;"></i>
                <div>
                    <p class="f-10 font-secondary mb-0">High Priority</p>
                    <h6 class="f-14 f-w-600 font-warning mb-0">{{ $highPriorityTasks }}</h6>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
    
    {{-- Task List --}}
    @if(count($tasks) > 0)
    <div class="mb-4">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Today's Schedule</p>
        <div style="max-height: 200px; overflow-y: auto;">
            @foreach($tasks as $task)
            <div class="d-flex align-items-start mb-3 p-2 {{ $task['completed'] ? 'opacity-75' : '' }}" 
                 style="background-color: var(--light-100); border-radius: var(--rounded-md);">
                <div class="me-3 mt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" {{ $task['completed'] ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="f-12 f-w-500 font-primary mb-0 {{ $task['completed'] ? 'text-decoration-line-through' : '' }}">
                            {{ $task['title'] }}
                        </h6>
                        @if(!empty($task['time']))
                        <span class="f-10 font-secondary">{{ $task['time'] }}</span>
                        @endif
                    </div>
                    @if(!empty($task['description']))
                    <p class="f-10 font-secondary mb-1">{{ $task['description'] }}</p>
                    @endif
                    <div class="d-flex align-items-center">
                        @if(!empty($task['priority']))
                        <span class="badge bg-{{ $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'secondary') }} me-2" 
                              style="font-size: 8px; padding: 2px 6px;">
                            {{ ucfirst($task['priority']) }}
                        </span>
                        @endif
                        @if(!empty($task['category']))
                        <span class="f-10 font-secondary">{{ $task['category'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-4">
        <i data-feather="check-circle" class="feather font-success mb-2" style="width: 32px; height: 32px;"></i>
        <p class="f-12 font-secondary mb-0">No tasks scheduled for today</p>
        <p class="f-10 font-success">You're all caught up!</p>
    </div>
    @endif
    
    {{-- Quick Actions --}}
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <div class="row g-2">
            <div class="col-6">
                <button type="button" class="btn btn-sm btn-outline-primary w-100" style="font-size: 11px;">
                    <i data-feather="plus" style="width: 12px; height: 12px;"></i>
                    Add Task
                </button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" style="font-size: 11px;">
                    <i data-feather="calendar" style="width: 12px; height: 12px;"></i>
                    View All
                </button>
            </div>
        </div>
    </div>
    
    {{-- Footer Stats --}}
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            @if($completedTasks === $totalTasks && $totalTasks > 0)
            <i data-feather="check-circle" class="feather font-success me-1" style="width: 12px; height: 12px;"></i>
            <span class="f-10 font-success">All tasks completed!</span>
            @else
            <i data-feather="clock" class="feather font-primary me-1" style="width: 12px; height: 12px;"></i>
            <span class="f-10 font-primary">{{ $totalTasks - $completedTasks }} pending</span>
            @endif
        </div>
        <span class="f-10 font-secondary">{{ now()->format('M d, Y') }}</span>
    </div>
</div>

<style>
.form-check-input:checked {
    background-color: var(--success-500);
    border-color: var(--success-500);
}

.btn-outline-primary {
    border-color: var(--primary-300);
    color: var(--primary-600);
}

.btn-outline-primary:hover {
    background-color: var(--primary-500);
    border-color: var(--primary-500);
}

.btn-outline-secondary {
    border-color: var(--secondary-300);
    color: var(--secondary-600);
}

.btn-outline-secondary:hover {
    background-color: var(--secondary-500);
    border-color: var(--secondary-500);
}
</style>