<div class="service-line" title="{{ $service['label'] }}">
    <span class="service-line-name">{{ $service['label'] }}</span>
    <a href="{{ route('app.quotation-tracking.index', ['search' => $customer->name]) }}" class="metric-badge metric-quote" aria-label="{{ $service['quotations'] }} báo giá của {{ $customer->name }}">{{ $service['quotations'] }} BG</a>
    <a href="{{ route('app.customers.contracts', $customer) }}" class="metric-badge metric-contract" aria-label="{{ $service['contracts'] }} hợp đồng của {{ $customer->name }}">{{ $service['contracts'] }} HĐ</a>
</div>
