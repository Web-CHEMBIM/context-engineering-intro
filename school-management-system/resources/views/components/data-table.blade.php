{{-- Data Table Component --}}
@props([
    'headers' => [],
    'rows' => [],
    'striped' => true,
    'actions' => null,
    'emptyMessage' => 'No data available'
])

<div class="table-responsive">
    <table class="table {{ $striped ? 'table-striped' : '' }} {{ $attributes->get('class') }}">
        @if(!empty($headers))
        <thead>
            <tr>
                @foreach($headers as $header)
                <th class="f-14 f-w-600 font-primary">{{ $header }}</th>
                @endforeach
                @if($actions)
                <th class="f-14 f-w-600 font-primary">Actions</th>
                @endif
            </tr>
        </thead>
        @endif
        
        <tbody>
            @forelse($rows as $row)
            <tr>
                @if(is_array($row))
                    @foreach($row as $cell)
                    <td class="f-14">{{ $cell }}</td>
                    @endforeach
                @else
                    {{ $row }}
                @endif
                
                @if($actions)
                <td>
                    {{ is_callable($actions) ? $actions($row, $loop->index) : $actions }}
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($headers) + ($actions ? 1 : 0) }}" class="text-center f-14 font-secondary p-40">
                    <i data-feather="inbox" class="feather mb-2" style="width: 32px; height: 32px; opacity: 0.5;"></i>
                    <p class="mb-0">{{ $emptyMessage }}</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $slot }}