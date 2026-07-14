<div class="d-flex align-items-center justify-content-between gap-2 px-2 py-1 mb-1 rounded-2" title="{{ $service['label'] }}" style="font-size: 0.8rem; border: 1px solid var(--bs-border-color-translucent);">
    <span class="fw-semibold text-truncate text-body" style="max-width: 180px;">
        <i class="fa-solid fa-circle-dot me-1 opacity-50" style="font-size: 0.55rem; vertical-align: middle; color: var(--bs-primary);"></i>{{ $service['label'] }}
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
