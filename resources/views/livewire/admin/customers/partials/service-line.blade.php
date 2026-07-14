<div class="d-flex align-items-center justify-content-between gap-2 px-2 py-1.5 mb-2 rounded bg-body-tertiary" title="{{ $service['label'] }}" style="font-size: 0.8rem;">
    <span class="fw-semibold text-truncate text-body" style="max-width: 180px;">{{ $service['label'] }}</span>
    <div class="d-flex gap-1.5 align-items-center flex-shrink-0">
        <a href="{{ route('app.quotation-tracking.index', ['search' => $customer->name]) }}"
           class="badge bg-primary bg-opacity-10 text-primary text-decoration-none px-2 py-1.5"
           aria-label="{{ $service['quotations'] }} báo giá của {{ $customer->name }}"
           style="font-size: 0.7rem;">
            {{ $service['quotations'] }} BG
        </a>
        <a href="{{ route('app.customers.contracts', $customer) }}"
           class="badge bg-success bg-opacity-10 text-success text-decoration-none px-2 py-1.5"
           aria-label="{{ $service['contracts'] }} hợp đồng của {{ $customer->name }}"
           style="font-size: 0.7rem;">
            {{ $service['contracts'] }} HĐ
        </a>
    </div>
</div>
