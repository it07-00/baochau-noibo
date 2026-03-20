@props([
    'title',
    'value',
    'badge' => null,
    'iconClass' => 'bg-glow-primary',
])

<div class="card shadow-custom rounded-custom h-100">
    <div class="card-body p-6">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
            <div>
                <span class="fz-13px fw-medium d-block mb-1">{{ $title }}</span>
                <h3 class="h6 mb-0 fs-5">{{ $value }}</h3>
            </div>
            <div class="btn-icon rounded-pill {{ $iconClass }}"></div>
        </div>

        @if ($badge)
            <span class="badge bg-label-info border-0">{{ $badge }}</span>
        @endif
    </div>
</div>
