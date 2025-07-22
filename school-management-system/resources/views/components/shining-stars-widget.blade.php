{{-- Shining Stars Widget Component --}}
@props([
    'title' => 'Shining Stars',
    'students' => [],
    'categories' => []
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Students with outstanding achievements</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--warning-100); border-radius: var(--rounded-lg);">
            <i data-feather="star" class="feather font-warning" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    @if(count($students) > 0)
    <div style="max-height: 260px; overflow-y: auto;">
        @foreach($students as $student)
        <div class="d-flex align-items-center mb-3 p-2" 
             style="background-color: var(--warning-50); border-radius: var(--rounded-md); border: 1px solid var(--warning-200);">
            <div class="me-3">
                <div class="d-flex align-items-center justify-content-center" 
                     style="width: 36px; height: 36px; background-color: var(--warning-500); border-radius: 50%; color: white;">
                    <i data-feather="star" style="width: 18px; height: 18px; fill: white;"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="f-14 f-w-600 font-primary mb-0">{{ $student['name'] }}</h6>
                    <span class="f-10 font-secondary">{{ $student['date'] ?? 'Recent' }}</span>
                </div>
                <p class="f-12 font-secondary mb-2">{{ $student['achievement'] }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="f-11 font-secondary">{{ $student['class'] ?? 'N/A' }}</span>
                    <span class="badge bg-warning text-dark" 
                          style="font-size: 8px; padding: 2px 8px;">
                        {{ $student['category'] ?? 'Academic' }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-4">
        <i data-feather="star" class="feather font-secondary mb-2" style="width: 32px; height: 32px;"></i>
        <p class="f-12 font-secondary mb-0">No recent achievements</p>
    </div>
    @endif
    
    {{-- Achievement Categories --}}
    @if(count($categories) > 0)
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <div class="row text-center">
            @foreach($categories as $category => $count)
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--warning-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1 text-capitalize">{{ $category }}</p>
                <h6 class="f-12 f-w-600 font-warning mb-0">{{ $count }}</h6>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>