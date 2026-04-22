@props([
    'title',
    'value',
    'badge' => null,
    'iconClass' => 'bg-glow-primary',
])

<div class="card shadow-custom rounded-custom h-100">
    <div class="card-body p-0 position-relative z-1">
        <div class="d-flex align-items-start gap-3 p-6">
            <div class="btn-icon {{ $iconClass }} rounded-pill flex-shrink-0"></div>
            <div class="flex-fill">
                <span class="fz-13px fw-medium d-block mb-1 text-muted">{{ $title }}</span>
                <h3 class="fs-5 mb-0 fw-bold">{{ $value }}</h3>
                @if ($badge)
                    <span class="badge bg-label-info border-0 mt-2">{{ $badge }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
