<div class="d-flex align-items-center justify-content-between gap-2 mb-1" title="{{ $service['label'] }}">
    <span class="badge px-2 py-1 text-truncate fw-semibold"
          style="font-size: 0.75rem; max-width: 220px; white-space: normal; text-align: left; line-height: 1.3; background: rgba(194, 65, 12, 0.1); color: #9a3412; border: 1px solid rgba(194, 65, 12, 0.2);"
          title="Dịch vụ: {{ $service['label'] }}">
        <i class="fa-solid fa-gear me-1"></i>{{ $service['label'] }}
    </span>
    <div class="d-flex gap-1 align-items-center flex-shrink-0">
        <a href="{{ route('app.quotation-tracking.index', ['search' => $customer->name]) }}"
           class="badge text-decoration-none px-2 py-1"
           aria-label="{{ $service['quotations'] }} báo giá của {{ $customer->name }}"
           style="font-size: 0.68rem; background: rgba(var(--bs-primary-rgb), 0.15); color: var(--bs-primary); font-weight: 600;">
            {{ $service['quotations'] }} BG
        </a>
        <a href="{{ route('app.customers.contracts', $customer) }}"
           class="badge text-decoration-none px-2 py-1"
           aria-label="{{ $service['contracts'] }} hợp đồng của {{ $customer->name }}"
           style="font-size: 0.68rem; background: rgba(var(--bs-success-rgb), 0.15); color: var(--bs-success); font-weight: 600;">
            {{ $service['contracts'] }} HĐ
        </a>
    </div>
</div>
