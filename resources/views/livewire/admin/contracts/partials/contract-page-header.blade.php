<header class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-start gap-3">
        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white p-3 shadow-sm" aria-hidden="true">
            <i class="fa-solid {{ $icon }} fs-4"></i>
        </span>
        <div>
            <h1 class="h4 fw-bold text-body mb-1">{{ $title }}</h1>
            <p class="text-secondary-emphasis mb-2">Theo dõi hồ sơ, tiến độ thực hiện và tình trạng hợp đồng.</p>
            <nav aria-label="Điều hướng trang">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng thống kê</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2">
        <div class="input-group">
            <span class="input-group-text bg-body" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="search" class="form-control" placeholder="Số hợp đồng hoặc khách hàng"
                aria-label="Tìm kiếm hợp đồng" wire:model.live.debounce.300ms="search">
        </div>
        @can($createPermission)
            <button wire:click="create" wire:loading.attr="disabled" wire:target="create"
                class="btn btn-primary text-nowrap">
                <span wire:loading.remove wire:target="create"><i class="fa-solid fa-plus me-1"></i> Thêm hợp đồng</span>
                <span wire:loading wire:target="create"><span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Đang mở</span>
            </button>
        @endcan
    </div>
</header>
