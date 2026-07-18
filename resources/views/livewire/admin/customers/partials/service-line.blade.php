<div class="d-flex align-items-center justify-content-between gap-2 mb-1" title="{{ $service['label'] }}">
    <span class="badge d-inline-block bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1 text-truncate fw-semibold"
          style="font-size: 0.75rem; max-width: 220px; text-align: left;"
          title="Dịch vụ: {{ $service['label'] }}">
        <i class="fa-solid fa-gear me-1"></i>{{ $service['label'] }}
    </span>
    <div class="d-flex gap-1 align-items-center flex-shrink-0">
        <a href="{{ route('app.quotation-tracking.index', ['search' => $customer->name]) }}"
           class="badge bg-primary bg-opacity-10 text-primary text-decoration-none px-2 py-1"
           aria-label="{{ $service['quotations'] }} báo giá của {{ $customer->name }}"
           style="font-size: 0.68rem; font-weight: 600;">
            {{ $service['quotations'] }} BG
        </a>
        <a href="{{ route('app.customers.contracts', $customer) }}"
           class="badge bg-success bg-opacity-10 text-success text-decoration-none px-2 py-1"
           aria-label="{{ $service['contracts'] }} hợp đồng của {{ $customer->name }}"
           style="font-size: 0.68rem; font-weight: 600;">
            {{ $service['contracts'] }} HĐ
        </a>
    </div>
</div>
