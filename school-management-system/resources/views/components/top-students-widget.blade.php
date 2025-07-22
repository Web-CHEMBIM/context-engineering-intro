{{-- Top Students Widget Component --}}
@props([
    'title' => 'Top Students',
    'students' => [],
    'criteriaType' => 'overall'
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Highest performing students this semester</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--warning-100); border-radius: var(--rounded-lg);">
            <i data-feather="award" class="feather font-warning" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    @if(count($students) > 0)
    <div style="max-height: 300px; overflow-y: auto;">
        @foreach($students as $student)
        <div class="d-flex align-items-center mb-3 p-2 {{ $loop->first ? 'bg-warning-50' : 'bg-light-100' }}" 
             style="border-radius: var(--rounded-md); {{ $loop->first ? 'border: 1px solid var(--warning-200);' : '' }}">
            <div class="me-3">
                <div class="d-flex align-items-center justify-content-center" 
                     style="width: 32px; height: 32px; background-color: var(--{{ $loop->first ? 'warning' : ($loop->index < 3 ? 'primary' : 'secondary') }}-500); border-radius: 50%; color: white;">
                    @if($loop->first)
                    <i data-feather="crown" style="width: 16px; height: 16px;"></i>
                    @else
                    <span class="f-12 f-w-700">{{ $loop->iteration }}</span>
                    @endif
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="f-14 f-w-600 font-primary mb-0">{{ $student['name'] }}</h6>
                    <div class="text-end">
                        <span class="f-12 f-w-700 font-{{ $loop->first ? 'warning' : 'primary' }}">{{ $student['score'] }}%</span>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="f-11 font-secondary">{{ $student['class'] ?? 'N/A' }}</span>
                    <div class="d-flex align-items-center">
                        @for($i = 1; $i <= 5; $i++)
                        <i data-feather="star" class="feather {{ $i <= $student['rating'] ? 'font-warning' : 'font-secondary' }}" 
                           style="width: 10px; height: 10px; fill: {{ $i <= $student['rating'] ? 'var(--warning-500)' : 'transparent' }};"></i>
                        @endfor
                    </div>
                </div>
                @if(!empty($student['subjects']))
                <div class="mt-2">
                    <p class="f-10 font-secondary mb-1">Top subjects:</p>
                    <div class="d-flex flex-wrap">
                        @foreach(explode(',', $student['subjects']) as $subject)
                        <span class="badge bg-{{ $loop->parent->first ? 'warning' : 'primary' }} me-1 mb-1" 
                              style="font-size: 8px; padding: 2px 6px;">{{ trim($subject) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-4">
        <i data-feather="award" class="feather font-secondary mb-2" style="width: 32px; height: 32px;"></i>
        <p class="f-12 font-secondary mb-0">No student performance data available</p>
    </div>
    @endif
    
    {{-- Performance Summary --}}
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <div class="row text-center">
            <div class="col-4">
                <div class="mb-1">
                    <div style="width: 16px; height: 16px; background-color: var(--warning-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">A Grade</p>
                <h6 class="f-12 f-w-600 font-warning mb-0">{{ count($students) >= 3 ? 3 : count($students) }}</h6>
            </div>
            <div class="col-4">
                <div class="mb-1">
                    <div style="width: 16px; height: 16px; background-color: var(--success-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Improved</p>
                <h6 class="f-12 f-w-600 font-success mb-0">{{ rand(2, 8) }}</h6>
            </div>
            <div class="col-4">
                <div class="mb-1">
                    <div style="width: 16px; height: 16px; background-color: var(--primary-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Avg Score</p>
                <h6 class="f-12 f-w-600 font-primary mb-0">{{ count($students) > 0 ? round(collect($students)->avg('score')) : 0 }}%</h6>
            </div>
        </div>
    </div>
    
    {{-- Footer --}}
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <span class="f-10 font-info">{{ ucfirst($criteriaType) }} performance</span>
        <span class="f-10 font-secondary">{{ now()->format('M Y') }}</span>
    </div>
</div>