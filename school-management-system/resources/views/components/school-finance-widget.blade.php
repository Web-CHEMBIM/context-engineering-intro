{{-- School Finance Widget Component --}}
@props([
    'title' => 'School Finance',
    'totalRevenue' => 0,
    'totalExpenses' => 0,
    'netIncome' => 0,
    'tuitionFees' => 0,
    'unpaidFees' => 0,
    'monthlyTrend' => 'up',
    'expenseBreakdown' => [],
    'feeCollectionRate' => 0,
    'budgetUtilization' => 0
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Financial overview and budget management</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--warning-100); border-radius: var(--rounded-lg);">
            <i data-feather="dollar-sign" class="feather font-warning" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    {{-- Financial Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="text-center p-3" style="background-color: {{ $netIncome >= 0 ? 'var(--success-50)' : 'var(--danger-50)' }}; border-radius: var(--rounded-lg);">
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <h3 class="f-22 f-w-700 {{ $netIncome >= 0 ? 'font-success' : 'font-danger' }} mb-0 me-2">
                        ${{ number_format(abs($netIncome), 0) }}
                    </h3>
                    <i data-feather="{{ $monthlyTrend === 'up' ? 'trending-up' : 'trending-down' }}" 
                       class="feather {{ $monthlyTrend === 'up' ? 'font-success' : 'font-danger' }}" 
                       style="width: 16px; height: 16px;"></i>
                </div>
                <p class="f-12 font-secondary mb-1">{{ $netIncome >= 0 ? 'Net Profit' : 'Net Loss' }}</p>
                <span class="f-10 {{ $monthlyTrend === 'up' ? 'font-success' : 'font-danger' }}">
                    {{ $monthlyTrend === 'up' ? '+' : '-' }}{{ rand(2, 15) }}% this month
                </span>
            </div>
        </div>
    </div>
    
    {{-- Revenue vs Expenses --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="d-flex align-items-center">
                <div class="me-2">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--success-100); border-radius: var(--rounded-md);">
                        <i data-feather="arrow-up" class="feather font-success" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div>
                    <p class="f-11 font-secondary mb-1">Revenue</p>
                    <h6 class="f-14 f-w-600 font-success mb-0">${{ number_format($totalRevenue, 0) }}</h6>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="d-flex align-items-center">
                <div class="me-2">
                    <div class="d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; background-color: var(--danger-100); border-radius: var(--rounded-md);">
                        <i data-feather="arrow-down" class="feather font-danger" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div>
                    <p class="f-11 font-secondary mb-1">Expenses</p>
                    <h6 class="f-14 f-w-600 font-danger mb-0">${{ number_format($totalExpenses, 0) }}</h6>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Fee Collection Rate --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
                <i data-feather="credit-card" class="feather font-primary me-2" style="width: 16px; height: 16px;"></i>
                <span class="f-12 font-secondary">Fee Collection Rate</span>
            </div>
            <span class="f-12 f-w-600 font-primary">{{ $feeCollectionRate }}%</span>
        </div>
        <div class="progress" style="height: 8px; background-color: var(--primary-100);">
            <div class="progress-bar" 
                 role="progressbar" 
                 style="width: {{ $feeCollectionRate }}%; background-color: var(--primary-500); transition: width 0.6s ease;"
                 aria-valuenow="{{ $feeCollectionRate }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <span class="f-10 font-secondary">Collected: ${{ number_format($tuitionFees, 0) }}</span>
            <span class="f-10 font-danger">Pending: ${{ number_format($unpaidFees, 0) }}</span>
        </div>
    </div>
    
    {{-- Budget Utilization --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
                <i data-feather="pie-chart" class="feather font-warning me-2" style="width: 16px; height: 16px;"></i>
                <span class="f-12 font-secondary">Budget Utilization</span>
            </div>
            <span class="f-12 f-w-600 font-warning">{{ $budgetUtilization }}%</span>
        </div>
        <div class="progress" style="height: 8px; background-color: var(--warning-100);">
            <div class="progress-bar" 
                 role="progressbar" 
                 style="width: {{ $budgetUtilization }}%; background-color: var(--warning-500); transition: width 0.6s ease;"
                 aria-valuenow="{{ $budgetUtilization }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <span class="f-10 font-secondary">Target: 85%</span>
            <span class="f-10 {{ $budgetUtilization <= 90 ? 'font-success' : ($budgetUtilization <= 95 ? 'font-warning' : 'font-danger') }}">
                {{ $budgetUtilization <= 90 ? 'On Track' : ($budgetUtilization <= 95 ? 'High' : 'Over Budget') }}
            </span>
        </div>
    </div>
    
    {{-- Expense Breakdown --}}
    @if(count($expenseBreakdown) > 0)
    <div class="mb-4">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Expense Breakdown</p>
        @foreach($expenseBreakdown as $category)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
                <div style="width: 8px; height: 8px; background-color: var(--{{ $category['color'] ?? 'primary' }}-500); border-radius: 50%; margin-right: 8px;"></div>
                <span class="f-11 font-secondary">{{ $category['name'] }}</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="f-11 f-w-600 font-primary me-2">${{ number_format($category['amount'], 0) }}</span>
                <span class="f-10 font-secondary">{{ $category['percentage'] }}%</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    
    {{-- Financial Health Indicators --}}
    <div class="mt-4 pt-3" style="border-top: 1px solid var(--secondary-200);">
        <p class="f-12 f-w-500 font-secondary mb-3 text-uppercase">Financial Health</p>
        <div class="row text-center">
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--success-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Liquidity</p>
                <h6 class="f-12 f-w-600 font-success mb-0">Good</h6>
            </div>
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--warning-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Efficiency</p>
                <h6 class="f-12 f-w-600 font-warning mb-0">{{ $budgetUtilization }}%</h6>
            </div>
            <div class="col-4">
                <div class="mb-2">
                    <div style="width: 20px; height: 20px; background-color: var(--{{ $netIncome >= 0 ? 'success' : 'danger' }}-500); border-radius: 50%; margin: 0 auto;"></div>
                </div>
                <p class="f-10 font-secondary mb-1">Profit</p>
                <h6 class="f-12 f-w-600 font-{{ $netIncome >= 0 ? 'success' : 'danger' }} mb-0">
                    {{ $netIncome >= 0 ? 'Positive' : 'Negative' }}
                </h6>
            </div>
        </div>
    </div>
    
    {{-- Footer Stats --}}
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i data-feather="calendar" class="feather font-info me-1" style="width: 12px; height: 12px;"></i>
            <span class="f-10 font-info">Q{{ ceil(now()->month / 3) }} {{ now()->year }}</span>
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