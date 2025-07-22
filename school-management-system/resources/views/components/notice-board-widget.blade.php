{{-- Notice Board Widget Component --}}
@props([
    'title' => 'Notice Board',
    'notices' => []
])

<div class="widget {{ $attributes->get('class') }}">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="f-18 f-w-600 font-primary mb-1">{{ $title }}</h5>
            <p class="f-12 font-secondary mb-0">Important announcements and updates</p>
        </div>
        <div class="d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background-color: var(--info-100); border-radius: var(--rounded-lg);">
            <i data-feather="bell" class="feather font-info" style="width: 20px; height: 20px;"></i>
        </div>
    </div>
    
    @if(count($notices) > 0)
    <div style="max-height: 280px; overflow-y: auto;">
        @foreach($notices as $notice)
        <div class="mb-3 p-3" style="background-color: var(--{{ $notice['priority'] === 'high' ? 'danger' : ($notice['priority'] === 'medium' ? 'warning' : 'info') }}-50); border-left: 4px solid var(--{{ $notice['priority'] === 'high' ? 'danger' : ($notice['priority'] === 'medium' ? 'warning' : 'info') }}-500); border-radius: var(--rounded-md);">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="f-14 f-w-600 font-primary mb-0">{{ $notice['title'] }}</h6>
                <span class="badge bg-{{ $notice['priority'] === 'high' ? 'danger' : ($notice['priority'] === 'medium' ? 'warning' : 'info') }}" 
                      style="font-size: 8px; padding: 2px 6px;">
                    {{ ucfirst($notice['priority'] ?? 'info') }}
                </span>
            </div>
            <p class="f-12 font-secondary mb-2">{{ $notice['content'] }}</p>
            <div class="d-flex justify-content-between align-items-center">
                <span class="f-10 font-secondary">{{ $notice['category'] ?? 'General' }}</span>
                <span class="f-10 font-secondary">{{ $notice['date'] }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-4">
        <i data-feather="bell-off" class="feather font-secondary mb-2" style="width: 32px; height: 32px;"></i>
        <p class="f-12 font-secondary mb-0">No current notices</p>
    </div>
    @endif
</div>