{{-- Unpaid Fees Widget Component --}}
@props([
    'title' => 'Unpaid Fees',
    'totalUnpaid' => 0,
    'studentsWithUnpaidFees' => 0,
    'overdueAmount' => 0,
    'currentMonthDue' => 0,
    'unpaidFeesList' => [],
    'collectionRate' => 0,
    'averageDelayDays' => 0
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Outstanding fee collections and payment tracking</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--danger-100); border-radius: var(--rounded-lg);">
            <i data-feather="credit-card" class="feather font-danger" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Total Unpaid Amount --}}
    <div class="text-center mb-4 p-3" style="background-color: var(--danger-50); border-radius: var(--rounded-lg);">
        <div class="d-flex justify-content-center align-items-center mb-2">
            <div class="me-3">
                <i data-feather="alert-triangle" class="feather font-danger" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <h3 class="f-24 f-w-700 font-danger mb-0">${{ number_format($totalUnpaid, 0) }}</h3>
                <p class="f-12 font-secondary mb-1">Total Outstanding</p>
                <span class="f-10 font-danger">{{ $studentsWithUnpaidFees }} students affected</span>
            </div>
        </div>
    </div>
    
    {{-- Fee Breakdown --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--danger-100); border-radius: var(--rounded-md);">
                        <i data-feather="clock" class="feather font-danger" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div>
                    <p class="f-11 font-secondary mb-1">Overdue</p>
                    <h6 class="f-14 f-w-600 font-danger mb-0">${{ number_format($overdueAmount, 0) }}</h6>
                    <span class="f-10 font-secondary">{{ $averageDelayDays }} days avg</span>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--warning-100); border-radius: var(--rounded-md);">
                        <i data-feather="calendar" class="feather font-warning" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div>
                    <p class="f-11 font-secondary mb-1">Current Due</p>
                    <h6 class="f-14 f-w-600 font-warning mb-0">${{ number_format($currentMonthDue, 0) }}</h6>
                    <span class="f-10 font-secondary">This month</span>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Collection Rate --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
                <i data-feather="trending-down" class="feather font-danger me-2" style="width: 16px; height: 16px;"></i>
                <span class="f-12 font-secondary">Collection Rate</span>
            </div>
            <span class="f-12 f-w-600 font-danger">{{ $collectionRate }}%</span>
        </div>
        <div class="progress" style="height: 8px; background-color: var(--danger-100);">
            <div class="progress-bar" 
                 role="progressbar" 
                 style="width: {{ $collectionRate }}%; background-color: var(--danger-500); transition: width 0.6s ease;"
                 aria-valuenow="{{ $collectionRate }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <span class="f-10 font-secondary">Target: 95%</span>
            <span class="f-10 {{ $collectionRate >= 95 ? 'font-success' : ($collectionRate >= 85 ? 'font-warning' : 'font-danger') }}">
                {{ $collectionRate >= 95 ? 'Excellent' : ($collectionRate >= 85 ? 'Good' : 'Poor') }}
            </span>
        </div>
    </div>
    
    {{-- Top Unpaid Fees List --}}
    @if(count($unpaidFeesList) > 0)
    <div class="mb-4">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Recent Outstanding</p>
        <div style="max-height: 160px; overflow-y: auto;">
            @foreach($unpaidFeesList as $fee)
            <div class="d-flex justify-content-between align-items-center mb-2 p-2" 
                 style="background-color: var(--light-100); border-radius: var(--rounded-md);">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="f-12 f-w-500 font-primary mb-0">{{ $fee['student_name'] }}</h6>
                        <span class="f-12 f-w-600 font-danger">${{ number_format($fee['amount'], 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="f-10 font-secondary">{{ $fee['fee_type'] ?? 'Tuition' }}</span>
                        <span class="f-10 {{ $fee['days_overdue'] > 30 ? 'font-danger' : ($fee['days_overdue'] > 0 ? 'font-warning' : 'font-secondary') }}">
                            @if($fee['days_overdue'] > 0)
                                {{ $fee['days_overdue'] }} days overdue
                            @else
                                Due {{ $fee['due_date'] }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-3">
        <i data-feather="check-circle" class="feather font-success mb-2" style="width: 32px; height: 32px;"></i>
        <p class="f-12 font-secondary mb-0">No unpaid fees</p>
        <p class="f-10 font-success">All fees collected on time!</p>
    </div>
    @endif
    
    {{-- Payment Status Distribution --}}
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Payment Status</p>
        <div class="row text-center">
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--success-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Paid</p>
                <h6 class="f-12 f-w-600 font-success mb-0">{{ 100 - $studentsWithUnpaidFees }}%</h6>
            </div>
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--warning-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Pending</p>
                <h6 class="f-12 f-w-600 font-warning mb-0">{{ round($studentsWithUnpaidFees * 0.7) }}%</h6>
            </div>
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--danger-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Overdue</p>
                <h6 class="f-12 f-w-600 font-danger mb-0">{{ round($studentsWithUnpaidFees * 0.3) }}%</h6>
            </div>
        </div>
    </div>
    
    {{-- Quick Actions --}}
    <div class="mt-4">
        <div class="row g-2">
            <div class="col-6">
                <button type="button" class="btn btn-sm btn-outline-danger w-100" style="font-size: 11px;">
                    <i data-feather="mail" style="width: 12px; height: 12px;"></i>
                    Send Reminders
                </button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-sm btn-outline-primary w-100" style="font-size: 11px;">
                    <i data-feather="file-text" style="width: 12px; height: 12px;"></i>
                    Fee Report
                </button>
            </div>
        </div>
    </div>
    
    {{-- Footer Stats --}}
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i data-feather="alert-circle" class="feather font-danger me-1" style="width: 12px; height: 12px;"></i>
            <span class="f-10 font-danger">{{ $studentsWithUnpaidFees }} students need follow-up</span>
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

.btn-outline-danger {
    border-color: var(--danger-300);
    color: var(--danger-600);
}

.btn-outline-danger:hover {
    background-color: var(--danger-500);
    border-color: var(--danger-500);
}

.btn-outline-primary {
    border-color: var(--primary-300);
    color: var(--primary-600);
}

.btn-outline-primary:hover {
    background-color: var(--primary-500);
    border-color: var(--primary-500);
}
</style>