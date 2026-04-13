<div>

    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Chất thải & Tiếng ồn</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng thống kê</a></li>
                    <li class="breadcrumb-item active">Chất thải & Tiếng ồn</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 ms-auto justify-content-end">
            @unless (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                <button wire:click="create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Hợp Đồng
                </button>
            @endunless
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Tìm kiếm"
                    wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" />
                        <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="mb-0 fw-bold">Bộ lọc Hợp đồng chất thải</h6>
            <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterCollapse">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 15L12 9L6 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body p-4">
                <div class="row g-4">
                    @unless (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                        <!-- Row 1 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold custom-filter-label">Ngày ký hợp đồng</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.signed_from">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.signed_to">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold custom-filter-label">Ngày kết thúc</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.end_from">
                                <input type="date" class="form-control form-control-xs" wire:model.live="filter.end_to">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold custom-filter-label">Ngày hợp đồng về</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.returned_from">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.returned_to">
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Ngày trình ký</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.submitted_from">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.submitted_to">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Chủ xử lý</label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select form-control-xs text-start text-wrap" type="button"
                                    @click.prevent="open = !open"
                                    style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                    {{ $handlers->find($filter['handler_id'])?->name ?? 'Chọn chủ xử lý' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false"
                                    x-cloak style="max-height: 300px; overflow-y: auto;">
                                    <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                        placeholder="Tìm kiếm..." @click.stop>
                                    <button class="dropdown-item @if (!$filter['handler_id']) active @endif"
                                        type="button"
                                        x-show="'chọn chủ xử lý'.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                        wire:click="$set('filter.handler_id', '')" @click="open = false">Chọn chủ xử
                                        lý</button>
                                    @foreach ($handlers as $handler)
                                        <button
                                            class="dropdown-item text-wrap @if ($filter['handler_id'] == $handler->id) active @endif"
                                            type="button"
                                            x-show="{{ json_encode(mb_strtolower($handler->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            style="white-space: normal !important; word-break: break-all;"
                                            wire:click="$set('filter.handler_id', {{ $handler->id }})"
                                            @click="open = false">
                                            {{ $handler->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @if (auth()->user()->hasAnyRole(['tp-kinh-doanh', 'giam-doc']))
                            <div class="col-md-3">
                                <label class="form-label fw-bold custom-filter-label">Nhân viên chăm sóc</label>
                                <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                    <button class="form-select form-control-xs text-start text-wrap" type="button"
                                        @click.prevent="open = !open"
                                        style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                        {{ $staffs->find($filter['staff_id'])?->name ?? 'Chọn nhân viên' }}
                                    </button>
                                    <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false"
                                        x-cloak style="max-height: 300px; overflow-y: auto;">
                                        <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                            placeholder="Tìm kiếm..." @click.stop>
                                        <button class="dropdown-item @if (!$filter['staff_id']) active @endif"
                                            type="button"
                                            x-show="'chọn nhân viên'.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            wire:click="$set('filter.staff_id', '')" @click="open = false">Chọn nhân
                                            viên</button>
                                        @foreach ($staffs as $staff)
                                            <button
                                                class="dropdown-item text-wrap @if ($filter['staff_id'] == $staff->id) active @endif"
                                                type="button"
                                                x-show="{{ json_encode(mb_strtolower($staff->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                                style="white-space: normal !important; word-break: break-all;"
                                                wire:click="$set('filter.staff_id', {{ $staff->id }})"
                                                @click="open = false">
                                                {{ $staff->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Tỉnh thành</label>
                            <select class="form-select form-control-xs" wire:model.live="filter.province">
                                <option value="">Chọn tỉnh thành</option>
                                @foreach ($provinces as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Row 3 -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Nguồn thông tin</label>
                            <select class="form-select form-control-xs" wire:model.live="filter.source">
                                <option value="">Chọn Nguồn...</option>
                                @foreach ($source_options as $src)
                                    <option value="{{ $src }}">{{ $src }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Phương thức thanh toán</label>
                            <select class="form-select form-control-xs" wire:model.live="filter.payment_method">
                                <option value="">Chọn phương thức...</option>
                                @foreach ($payment_methods as $pm)
                                    <option value="{{ $pm }}">{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        {{-- Bộ lọc cho tư vấn / kỹ thuật --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold custom-filter-label">Ngày ký hợp đồng</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.signed_from">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.signed_to">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Tỉnh thành</label>
                            <select class="form-select form-control-xs" wire:model.live="filter.province">
                                <option value="">Chọn tỉnh thành</option>
                                @foreach ($provinces as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endunless
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Tình trạng</label>
                        <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                            <button class="form-select form-control-xs text-start text-wrap" type="button"
                                @click.prevent="open = !open"
                                style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                {{ $filter['status'] ?: 'Chọn tình trạng' }}
                            </button>
                            <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false"
                                x-cloak style="max-height: 300px; overflow-y: auto;">
                                <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                    placeholder="Tìm kiếm..." @click.stop>
                                <button class="dropdown-item @if (!$filter['status']) active @endif"
                                    type="button" wire:click="$set('filter.status', '')" @click="open = false">Chọn
                                    tình trạng</button>
                                @foreach ($all_statuses as $status)
                                    <button class="dropdown-item @if ($filter['status'] == $status) active @endif"
                                        type="button"
                                        x-show="{{ json_encode(mb_strtolower($status)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                        wire:click="$set('filter.status', '{{ $status }}')"
                                        @click="open = false">
                                        {{ $status }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @unless (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                        <!-- Row 4 -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Loại dịch vụ</label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select form-control-xs text-start text-wrap" type="button"
                                    @click.prevent="open = !open"
                                    style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                    {{ $filter['service_type'] ?: 'Chọn Loại dịch vụ' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false"
                                    x-cloak style="max-height: 300px; overflow-y: auto;">
                                    <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                        placeholder="Tìm kiếm..." @click.stop>
                                    <button class="dropdown-item @if (!$filter['service_type']) active @endif"
                                        type="button" wire:click="$set('filter.service_type', '')"
                                        @click="open = false">Chọn Loại dịch vụ</button>
                                    @foreach ($service_types as $service)
                                        <button class="dropdown-item @if ($filter['service_type'] == $service) active @endif"
                                            type="button"
                                            x-show="{{ json_encode(mb_strtolower($service)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            wire:click="$set('filter.service_type', '{{ $service }}')"
                                            @click="open = false">
                                            {{ $service }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Loại chất thải</label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select form-control-xs text-start text-wrap" type="button"
                                    @click.prevent="open = !open"
                                    style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                    {{ $filter['waste_type'] ?: 'Chọn Loại chất thải' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false"
                                    x-cloak style="max-height: 300px; overflow-y: auto;">
                                    <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                        placeholder="Tìm kiếm..." @click.stop>
                                    <button class="dropdown-item @if (!$filter['waste_type']) active @endif"
                                        type="button" wire:click="$set('filter.waste_type', '')"
                                        @click="open = false">Chọn Loại chất thải</button>
                                    @foreach ($waste_types as $waste)
                                        <button class="dropdown-item @if ($filter['waste_type'] == $waste) active @endif"
                                            type="button"
                                            x-show="{{ json_encode(mb_strtolower($waste)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            wire:click="$set('filter.waste_type', '{{ $waste }}')"
                                            @click="open = false">
                                            {{ $waste }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Tình trạng tái ký</label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select form-control-xs text-start text-wrap" type="button"
                                    @click.prevent="open = !open"
                                    style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                    {{ $filter['renewal_status'] ?: 'Chọn tình trạng' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false"
                                    x-cloak style="max-height: 300px; overflow-y: auto;">
                                    <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                        placeholder="Tìm kiếm..." @click.stop>
                                    <button class="dropdown-item @if (!$filter['renewal_status']) active @endif"
                                        type="button" wire:click="$set('filter.renewal_status', '')"
                                        @click="open = false">Chọn tình trạng</button>
                                    @foreach ($renewal_statuses as $renewal)
                                        <button class="dropdown-item @if ($filter['renewal_status'] == $renewal) active @endif"
                                            type="button"
                                            x-show="{{ json_encode(mb_strtolower($renewal)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            wire:click="$set('filter.renewal_status', '{{ $renewal }}')"
                                            @click="open = false">
                                            {{ $renewal }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Tình trạng phản hồi chứng từ</label>
                            <select class="form-select form-control-xs" wire:model.live="filter.voucher_status">
                                <option value="">Chọn tình trạng</option>
                                @foreach ($voucher_statuses as $vstatus)
                                    <option value="{{ $vstatus }}">{{ $vstatus }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Hạng mục dịch vụ</label>
                            <select class="form-select form-control-xs" wire:model.live="filter.loai_dich_vu">
                                <option value="">Chọn hạng mục</option>
                                @foreach ($loai_dich_vu_options as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Row 5 -->
                        <div class="col-md-3 d-flex align-items-end gap-3 pb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="offset"
                                    wire:model.live="filter.is_offset">
                                <label class="form-check-label small" for="offset">Có bù trừ</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="overdue"
                                    wire:model.live="filter.is_overdue">
                                <label class="form-check-label small" for="overdue">Trễ hạn</label>
                            </div>
                        </div>
                    @else
                        <div class="col-md-2 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="overdue"
                                    wire:model.live="filter.is_overdue">
                                <label class="form-check-label small" for="overdue">Trễ hạn</label>
                            </div>
                        </div>
                    @endunless

                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Sắp xếp</label>
                        <select class="form-select form-control-xs" wire:model.live="sortDirection">
                            <option value="desc">Từ trên xuống</option>
                            <option value="asc">Từ dưới lên</option>
                        </select>
                    </div>

                    <div class="col-md-6 d-flex align-items-end gap-2 justify-content-start">
                        <button class="btn btn-info text-white px-4 btn-filter" wire:click="$refresh">
                            <i class="bi bi-search me-1"></i>Lọc
                        </button>
                        <button class="btn btn-secondary px-4 btn-filter" wire:click="resetFilters">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Xóa lọc
                        </button>
                        @unless (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                            <button wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel"
                                class="btn btn-success px-4 btn-filter">
                                <span wire:loading wire:target="exportExcel"
                                    class="spinner-border spinner-border-sm me-1"></span>
                                <i wire:loading.remove wire:target="exportExcel"
                                    class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
                            </button>
                        @endunless
                        <button class="btn btn-primary px-4 btn-filter">
                            <i class="bi bi-diagram-3 me-1"></i>Quy trình
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Danh sách Hợp đồng chất thải</h6>
        </div>
        <div class="table-responsive" style="overflow:visible; min-height:350px;">
            <table class="table table-hover align-middle mb-0 table-xs">
                <thead class="bg-light bg-opacity-50">
                    <tr class="small text-muted fw-bold">
                        <th class="ps-4">Thông tin hợp đồng</th>
                        <th>Khách hàng</th>
                        @unless (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                            <th class="text-center">Giá trị hợp đồng</th>
                            <th class="text-center">Hoa hồng</th>
                            <th class="text-center">Doanh số</th>
                        @endunless
                        <th class="text-center">Tình trạng tái ký</th>
                        <th class="text-center">Tình trạng chứng từ</th>
                        <th class="text-center">Được giao</th>
                        <th class="text-center">Tình trạng</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docs as $doc)
                        <tr class="border-bottom border-light" wire:key="waste-row-{{ $doc->id }}">
                            <td class="ps-4 py-4" style="max-width: 250px;">
                                <div class="d-flex flex-column">
                                    <span class="small">Số HĐ CXL: <span
                                            class="fw-bold">{{ $doc->shd_cxl }}</span></span>
                                    <span class="small">Số HĐ BC: <span
                                            class="fw-bold">{{ $doc->shd_bc ?: '-' }}</span></span>
                                    <span class="small">Ngày ký hợp đồng:</span>
                                    <span
                                        class="small fw-bold">{{ $doc->signed_at ? $doc->signed_at->format('d/m/Y') : '-' }}</span>
                                    <span class="small">Nhân viên CS: <span
                                            class="fw-bold">{{ $doc->staff?->name }}</span></span>
                                </div>
                            </td>
                            <td class="py-4" style="max-width: 400px;">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-primary">{{ $doc->customer?->name }}</span>
                                    <span class="small">{{ $doc->customer?->representative }} -
                                        {{ $doc->customer?->phone }} - {{ $doc->customer?->email }}</span>
                                    <span class="small text-muted">{{ $doc->customer?->address }}</span>
                                </div>
                            </td>
                            @unless (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                                <td class="text-center">
                                    <span class="fw-bold text-danger">{{ number_format($doc->value) }}đ</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-danger">{{ number_format($doc->commission) }}đ</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-danger">{{ number_format($doc->revenue) }}đ</span>
                                </td>
                            @endunless
                            <td class="text-center">
                                @php
                                    $renewalStatusKey = mb_strtolower(trim((string) ($doc->renewal_status ?? '')));
                                    $renewalBadgeClass = match ($renewalStatusKey) {
                                        'đã tái ký' => 'bg-success text-white',
                                        'chưa tái ký' => 'bg-danger text-white',
                                        'chưa đến hạn' => 'bg-warning text-dark',
                                        '', 'chưa chọn' => 'bg-light text-dark border',
                                        default => 'bg-secondary text-white',
                                    };

                                    $voucherStatusKey = mb_strtolower(trim((string) ($doc->voucher_status ?? '')));
                                    $voucherBadgeClass = match ($voucherStatusKey) {
                                        'đã đề nghị thanh toán/tạm ứng' => 'bg-info text-dark',
                                        'đã xuất hóa đơn' => 'bg-warning text-dark',
                                        'đã làm biên bản bàn giao hồ sơ' => 'bg-primary text-white',
                                        'đã làm bb bàn giao và nghiệm thu kết thúc hợp đồng' => 'bg-success text-white',
                                        '', 'chưa có', 'chưa chọn' => 'bg-light text-dark border',
                                        default => 'bg-secondary text-white',
                                    };
                                @endphp
                                <span class="badge {{ $renewalBadgeClass }}">{{ $doc->renewal_status ?: 'Chưa chọn' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $voucherBadgeClass }}">{{ $doc->voucher_status ?: 'Chưa chọn' }}</span>
                            </td>
                            <td class="text-center">
                                @if ($doc->assignments->count() > 0)
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @foreach ($doc->assignments->take(3) as $assign)
                                            <span class="badge bg-secondary" style="font-size:0.65rem;"
                                                title="{{ $assign->user?->name }}">{{ Str::limit($assign->user?->name ?? '?', 8) }}</span>
                                        @endforeach
                                        @if ($doc->assignments->count() > 3)
                                            <span class="badge bg-light text-dark"
                                                style="font-size:0.65rem;">+{{ $doc->assignments->count() - 3 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    @php
                                        $statusKey = mb_strtolower(trim((string) ($doc->status ?? '')));
                                        $statusColor = match ($statusKey) {
                                            'hoàn thành', 'đã hoàn thành', 'đã hoàn thành kh ký trước' => ['bg' => '#d1e7dd', 'text' => '#198754'],
                                            'đã hủy', 'hợp đồng hủy', 'hủy bỏ' => ['bg' => '#f8d7da', 'text' => '#dc3545'],
                                            'đã trình ký chủ xử lý' => ['bg' => '#fff3cd', 'text' => '#b45309'],
                                            'chủ xử lý đã gửi về' => ['bg' => '#d1ecf1', 'text' => '#0c5460'],
                                            'đã gửi khách hàng' => ['bg' => '#e2d9f3', 'text' => '#6f42c1'],
                                            'đang thực hiện', '' => ['bg' => '#cfe2ff', 'text' => '#0d6efd'],
                                            default => ['bg' => '#e9ecef', 'text' => '#495057'],
                                        };
                                    @endphp
                                    @php
                                        $currentUser = auth()->user();
                                        $isRestrictedTpKd = $currentUser->hasRole('tp-kinh-doanh') &&
                                            !$currentUser->hasAnyRole(['admin', 'giam-doc', 'quan-ly']);

                                        $canUpdateStatus = !$currentUser->hasAnyRole(['tu-van', 'ky-thuat']) &&
                                            (!$isRestrictedTpKd || $doc->staff_id === $currentUser->id);
                                    @endphp

                                    @if (!$canUpdateStatus)
                                        <span class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border-0"
                                            style="font-size:0.7rem; background:{{ $statusColor['bg'] }}; color:{{ $statusColor['text'] }}; cursor:default;">
                                            {{ $doc->status ?: '—' }}
                                        </span>
                                    @else
                                        <div class="position-relative" x-data="{ open: false }">
                                            <button type="button" @click="open = !open"
                                                class="btn btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-1 fw-semibold border-0"
                                                style="font-size:0.7rem; background:{{ $statusColor['bg'] }}; color:{{ $statusColor['text'] }};">
                                                {{ $doc->status ?: '—' }}
                                                <svg width="12" height="12" viewBox="0 0 12 12"
                                                    fill="currentColor">
                                                    <path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor"
                                                        stroke-width="1.5" fill="none" stroke-linecap="round" />
                                                </svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-cloak
                                                class="position-absolute bg-white rounded-3 shadow-lg py-1 mt-1"
                                                style="z-index:1050; min-width:200px; right:50%; transform:translateX(50%); max-height:250px; overflow-y:auto;">
                                                @foreach ($all_statuses as $opt)
                                                    <button type="button"
                                                        class="dropdown-item d-flex align-items-center justify-content-between px-3 py-2 {{ $doc->status === $opt ? 'fw-bold' : '' }}"
                                                        style="font-size:0.8rem;"
                                                        wire:click="updateStatus({{ $doc->id }}, '{{ $opt }}')"
                                                        @click="open = false">
                                                        {{ $opt }}
                                                        @if ($doc->status === $opt)
                                                            <i class="bi bi-check2 ms-2"></i>
                                                        @endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    <span
                                        class="small text-muted mt-1">{{ $doc->submitted_at ? $doc->submitted_at->format('d/m/Y') : '-' }}</span>
                                </div>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm p-0 text-primary"
                                        wire:click="viewDetail({{ $doc->id }})">
                                        <i class="bi bi-eye fs-5"></i>
                                    </button>
                                    @if (auth()->user()->hasAnyRole(['giam-doc', 'quan-ly', 'tp-kinh-doanh', 'it']))
                                        <button class="btn btn-sm p-0 text-success"
                                            wire:click="openAssign({{ $doc->id }})" title="Giao việc">
                                            <i class="bi bi-person-check fs-5"></i>
                                        </button>
                                    @endif
                                    @if (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                                        <button class="btn btn-sm p-0 text-info"
                                            wire:click="openWorkflow({{ $doc->id }})" title="Cập nhật tiến độ">
                                            <i class="bi bi-diagram-3 fs-5"></i>
                                        </button>
                                    @endif
                                    @unless (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                                        @php
                                            $canEditDelete = !auth()->user()->hasRole('tp-kinh-doanh') || $doc->staff_id === auth()->id();
                                        @endphp
                                        @if ($canEditDelete)
                                            <button class="btn btn-sm p-0 text-warning"
                                                wire:click="edit({{ $doc->id }})">
                                                <i class="bi bi-pencil-square fs-5"></i>
                                            </button>
                                            <button class="btn btn-sm p-0 text-danger"
                                                wire:click="delete({{ $doc->id }})"
                                                wire:confirm="Xác nhận xóa hợp đồng này?">
                                                <i class="bi bi-trash fs-5"></i>
                                            </button>
                                        @endif
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasAnyRole(['tu-van', 'ky-thuat'])? 7: 10 }}"
                                class="text-center py-5 text-muted">Không tìm thấy hợp đồng nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($docs->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $docs->links('livewire.admin.users.pagination') }}
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title fw-bold text-white">Thông tin Hợp Đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if ($selectedDoc)
                        {{-- Tabs Navigation --}}
                        <ul class="nav nav-tabs px-4 pt-3" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-semibold" data-bs-toggle="tab"
                                    data-bs-target="#tab-info-waste-{{ $selectedDoc->id }}" type="button">
                                    <i class="bi bi-info-circle me-1"></i>Thông tin HĐ
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab"
                                    data-bs-target="#tab-progress-waste-{{ $selectedDoc->id }}" type="button">
                                    <i class="bi bi-diagram-3 me-1"></i>Tiến độ hoàn thành
                                </button>
                            </li>
                            @unless (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                                <li class="nav-item">
                                    <button class="nav-link fw-semibold" data-bs-toggle="tab"
                                        data-bs-target="#tab-payment-waste-{{ $selectedDoc->id }}" type="button">
                                        <i class="bi bi-cash-stack me-1"></i>Lịch thanh toán
                                    </button>
                                </li>
                            @endunless
                        </ul>
                        <div class="tab-content">
                            {{-- Tab 1: Thông tin HĐ --}}
                            <div class="tab-pane fade show active" id="tab-info-waste-{{ $selectedDoc->id }}"
                                role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3" style="width: 25%;">Ghi chú
                                                </th>
                                                <td class="px-4 py-3">Ghi chú : {{ $selectedDoc->note }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Khách hàng</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->customer?->name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Chủ xử lý</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->handler?->name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Số hợp đồng BC</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->shd_bc }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Nội dung</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->content }}</td>
                                            </tr>
                                            @unless (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                                                <tr>
                                                    <th class="bg-light fw-bold px-4 py-3">Giá trị hợp đồng</th>
                                                    <td class="px-4 py-3 fw-bold text-danger">
                                                        {{ number_format($selectedDoc->value) }}đ</td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light fw-bold px-4 py-3">Hoa hồng</th>
                                                    <td class="px-4 py-3 fw-bold text-danger">
                                                        {{ number_format($selectedDoc->commission) }}đ</td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light fw-bold px-4 py-3">Doanh số</th>
                                                    <td class="px-4 py-3 fw-bold text-danger">
                                                        {{ number_format($selectedDoc->revenue) }}đ</td>
                                                </tr>
                                            @endunless
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Phương thức thanh toán</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->payment_method }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Nhân viên chăm sóc</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->staff?->name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Phòng ban</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->department?->name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Nguồn thông tin</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->source }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Ngày ký hợp đồng</th>
                                                <td class="px-4 py-3">
                                                    {{ $selectedDoc->signed_at ? $selectedDoc->signed_at->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Ngày hiệu lực</th>
                                                <td class="px-4 py-3">
                                                    {{ $selectedDoc->effective_at ? $selectedDoc->effective_at->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Ngày kết thúc</th>
                                                <td class="px-4 py-3">
                                                    {{ $selectedDoc->end_at ? $selectedDoc->end_at->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Ngày trình ký</th>
                                                <td class="px-4 py-3 text-danger">
                                                    {{ $selectedDoc->submitted_at ? $selectedDoc->submitted_at->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Địa chỉ xuất hóa đơn</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->billing_address }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Địa chỉ thực hiện</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->execution_address }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Địa chỉ gửi thư</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->mailing_address }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Số hợp đồng CXL</th>
                                                <td class="px-4 py-3 fw-bold">{{ $selectedDoc->shd_cxl }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Người được giao</th>
                                                <td class="px-4 py-3">
                                                    @if ($selectedDoc->assignments && $selectedDoc->assignments->count() > 0)
                                                        @foreach ($selectedDoc->assignments as $assign)
                                                            <div class="mb-1">
                                                                <span
                                                                    class="badge bg-primary me-1">{{ $assign->user?->name }}</span>
                                                                <small class="text-muted">— giao bởi
                                                                    {{ $assign->assigner?->name }} lúc
                                                                    {{ $assign->created_at?->format('d/m/Y H:i') }}</small>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted small">Chưa giao việc</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3 align-middle" colspan="2"><i
                                                        class="bi bi-journal-text me-1"></i> Ghi chú tiến độ</th>
                                            </tr>
                                            @if ($progressNotes && count($progressNotes) > 0)
                                                @foreach ($progressNotes as $pNote)
                                                    <tr>
                                                        <td colspan="2" class="py-2 ps-4">
                                                            <div class="d-flex flex-column">
                                                                <span
                                                                    class="small fw-bold text-primary">{{ $pNote->user?->name }}
                                                                    <span class="text-muted fw-normal">—
                                                                        {{ $pNote->created_at?->format('d/m/Y H:i') }}</span></span>
                                                                <span class="mt-1">{{ $pNote->note }}</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="2" class="text-muted small ps-4 py-2">Chưa có ghi
                                                        chú tiến độ nào.</td>
                                                </tr>
                                            @endif
                                            @if (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                                                <tr>
                                                    <td colspan="2" class="px-4 pb-3 pt-2">
                                                        <textarea class="form-control form-control-sm mb-2" rows="2" wire:model="progressNote"
                                                            placeholder="Nhập ghi chú tiến độ..."></textarea>
                                                        @error('progressNote')
                                                            <div class="text-danger small mb-1">{{ $message }}</div>
                                                        @enderror
                                                        <button class="btn btn-sm btn-primary"
                                                            wire:click="addProgressNote({{ $selectedDoc->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="addProgressNote">
                                                            <span wire:loading wire:target="addProgressNote"
                                                                class="spinner-border spinner-border-sm me-1"></span>
                                                            <i class="bi bi-plus me-1"></i> Thêm ghi chú
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab 2: Tiến độ --}}
                            <div class="tab-pane fade" id="tab-progress-waste-{{ $selectedDoc->id }}"
                                role="tabpanel">
                                <livewire:admin.contracts.contract-workflow-progress :contractType="'waste'" :contractId="$selectedDoc->id"
                                    :key="'progress-waste-' . $selectedDoc->id" />
                            </div>

                            @unless (auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                                {{-- Tab 3: Lịch thanh toán --}}
                                <div class="tab-pane fade" id="tab-payment-waste-{{ $selectedDoc->id }}"
                                    role="tabpanel">
                                    <livewire:admin.contracts.contract-payment-schedule-manager :contractType="'waste'"
                                        :contractId="$selectedDoc->id" :key="'payment-waste-' . $selectedDoc->id" />
                                </div>
                            @endunless
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    <div wire:ignore.self class="modal fade" id="formModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white">
                        {{ $isEditing ? 'Cập nhật Hợp đồng' : 'Thêm Hợp đồng mới' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Info -->
                            @if ($isEditing && auth()->user()->hasRole('ke-toan'))
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Số HĐ CXL</label>
                                    <input type="text" class="form-control" wire:model.defer="formData.shd_cxl">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Số HĐ BC</label>
                                    <input type="text" class="form-control" wire:model.defer="formData.shd_bc">
                                </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Khách hàng <span
                                        class="text-danger">*</span></label>
                                <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                    <button
                                        class="form-select text-start text-wrap @error('formData.customer_id') is-invalid @enderror"
                                        type="button" @click.prevent="open = !open"
                                        style="width: 100%; white-space: normal !important; height: auto !important; min-height: 38px;">
                                        {{ $customers->find($formData['customer_id'] ?? '')?->name ?? 'Chọn khách hàng' }}
                                    </button>
                                    <div class="dropdown-menu-custom w-100 p-2" x-show="open"
                                        @click.away="open = false" x-cloak
                                        style="max-height: 300px; overflow-y: auto;">
                                        <input type="text" x-model="search"
                                            class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..."
                                            @click.stop>
                                        <button class="dropdown-item @if (empty($formData['customer_id'])) active @endif"
                                            type="button" x-show="!search.length"
                                            wire:click="$set('formData.customer_id', '')" @click="open = false">Chọn
                                            khách hàng</button>
                                        @foreach ($customers as $customer)
                                            <button
                                                class="dropdown-item text-wrap @if (($formData['customer_id'] ?? '') == $customer->id) active @endif"
                                                type="button"
                                                x-show="{{ json_encode(mb_strtolower($customer->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                                style="white-space: normal !important;"
                                                wire:click="$set('formData.customer_id', {{ $customer->id }})"
                                                @click="open = false">
                                                {{ $customer->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                @error('formData.customer_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Chủ xử lý <span class="text-danger">*</span></label>
                                <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                    <button
                                        class="form-select text-start text-wrap @error('formData.handler_id') is-invalid @enderror"
                                        type="button" @click.prevent="open = !open"
                                        style="width: 100%; white-space: normal !important; height: auto !important; min-height: 38px;">
                                        {{ $handlers->find($formData['handler_id'] ?? '')?->name ?? 'Chọn chủ xử lý' }}
                                    </button>
                                    <div class="dropdown-menu-custom w-100 p-2" x-show="open"
                                        @click.away="open = false" x-cloak
                                        style="max-height: 300px; overflow-y: auto;">
                                        <input type="text" x-model="search"
                                            class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..."
                                            @click.stop>
                                        <button class="dropdown-item @if (empty($formData['handler_id'])) active @endif"
                                            type="button" x-show="!search.length"
                                            wire:click="$set('formData.handler_id', '')" @click="open = false">Chọn
                                            chủ xử lý</button>
                                        @foreach ($handlers as $h)
                                            <button
                                                class="dropdown-item text-wrap @if (($formData['handler_id'] ?? '') == $h->id) active @endif"
                                                type="button"
                                                x-show="{{ json_encode(mb_strtolower($h->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                                style="white-space: normal !important;"
                                                wire:click="$set('formData.handler_id', {{ $h->id }})"
                                                @click="open = false">
                                                {{ $h->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                @error('formData.handler_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            @if (auth()->user()->hasAnyRole(['tp-kinh-doanh', 'giam-doc']))
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Nhân viên <span class="text-danger">*</span></label>
                                    <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                        <button
                                            class="form-select text-start text-wrap @error('formData.staff_id') is-invalid @enderror"
                                            type="button" @click.prevent="open = !open"
                                            style="width: 100%; white-space: normal !important; height: auto !important; min-height: 38px;">
                                            {{ $staffs->find($formData['staff_id'] ?? '')?->name ?? 'Chọn nhân viên' }}
                                        </button>
                                        <div class="dropdown-menu-custom w-100 p-2" x-show="open"
                                            @click.away="open = false" x-cloak
                                            style="max-height: 300px; overflow-y: auto;">
                                            <input type="text" x-model="search"
                                                class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..."
                                                @click.stop>
                                            <button class="dropdown-item @if (empty($formData['staff_id'])) active @endif"
                                                type="button" x-show="!search.length"
                                                wire:click="$set('formData.staff_id', '')" @click="open = false">Chọn nhân
                                                viên</button>
                                            @foreach ($staffs as $s)
                                                <button
                                                    class="dropdown-item text-wrap @if (($formData['staff_id'] ?? '') == $s->id) active @endif"
                                                    type="button"
                                                    x-show="{{ json_encode(mb_strtolower($s->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                                    style="white-space: normal !important;"
                                                    wire:click="$set('formData.staff_id', {{ $s->id }})"
                                                    @click="open = false">
                                                    {{ $s->name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('formData.staff_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                            <div class="col-md-3 d-none">
                                <label class="form-label fw-bold">Phòng ban</label>
                                <select class="form-select" wire:model.defer="formData.department_id">
                                    <option value="">Chọn phòng ban</option>
                                    @foreach ($departments as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Nội dung</label>
                                <textarea class="form-control @error('formData.content') is-invalid @enderror" rows="2"
                                    wire:model.defer="formData.content"></textarea>
                                @error('formData.content')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Values -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Giá trị hợp đồng <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control money-input @error('formData.value') is-invalid @enderror"
                                        wire:model.defer="formData.value">
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('formData.value')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Hoa hồng</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control money-input @error('formData.commission') is-invalid @enderror"
                                        wire:model.defer="formData.commission">
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('formData.commission')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Doanh số thực</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control money-input @error('formData.revenue') is-invalid @enderror"
                                        wire:model.defer="formData.revenue">
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('formData.revenue')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Dates -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ngày ký</label>
                                <input type="date" class="form-control" wire:model.defer="formData.signed_at">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ngày hiệu lực</label>
                                <input type="date"
                                    class="form-control @error('formData.effective_at') is-invalid @enderror"
                                    wire:model.defer="formData.effective_at">
                                @error('formData.effective_at')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ngày kết thúc</label>
                                <input type="date"
                                    class="form-control @error('formData.end_at') is-invalid @enderror"
                                    wire:model.defer="formData.end_at">
                                @error('formData.end_at')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ngày trình ký</label>
                                <input type="date" class="form-control" wire:model.defer="formData.submitted_at">
                            </div>

                            <!-- Addresses -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Địa chỉ xuất HĐ</label>
                                <textarea class="form-control @error('formData.billing_address') is-invalid @enderror" rows="2"
                                    wire:model.defer="formData.billing_address"></textarea>
                                @error('formData.billing_address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Địa chỉ thực hiện</label>
                                <textarea class="form-control @error('formData.execution_address') is-invalid @enderror" rows="2"
                                    wire:model.defer="formData.execution_address"></textarea>
                                @error('formData.execution_address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Địa chỉ gửi thư</label>
                                <textarea class="form-control @error('formData.mailing_address') is-invalid @enderror" rows="2"
                                    wire:model.defer="formData.mailing_address"></textarea>
                                @error('formData.mailing_address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Statuses -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tình trạng</label>
                                <select class="form-select" wire:model.defer="formData.status">
                                    <option value="">Chọn tình trạng</option>
                                    @foreach ($all_statuses as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tình trạng tái ký</label>
                                <select class="form-select" wire:model.defer="formData.renewal_status">
                                    <option value="">Chọn tình trạng</option>
                                    <option value="CHƯA ĐẾN HẠN">Chưa đến hạn</option>
                                    <option value="ĐÃ TÁI KÝ">Đã tái ký</option>
                                    <option value="KHÔNG TÁI KÝ">Không tái ký</option>
                                    <option value="CHỜ XÁC NHẬN">Chờ xác nhận</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tình trạng chứng từ</label>
                                <select class="form-select" wire:model.defer="formData.voucher_status">
                                    <option value="">Chọn tình trạng</option>
                                    @foreach ($voucher_status_options as $voucherStatus)
                                        <option value="{{ $voucherStatus }}">{{ $voucherStatus }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Nguồn thông tin</label>
                                <input type="text" class="form-control" wire:model.defer="formData.source"
                                    list="source-list-waste" placeholder="Nhập hoặc chọn nguồn...">
                                <datalist id="source-list-waste">
                                    @foreach ($source_options as $src)
                                        <option value="{{ $src }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">PT thanh toán</label>
                                <input class="form-control" wire:model.defer="formData.payment_method"
                                    list="pm-options" placeholder="VD: Sau ký, Trước ký...">
                                <datalist id="pm-options">
                                    @foreach ($payment_methods as $pm)
                                        <option value="{{ $pm }}">
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tỉnh thành</label>
                                <input type="text" class="form-control" wire:model="formData.province"
                                    list="province-list-waste" autocomplete="off">
                                <datalist id="province-list-waste">
                                    @foreach ($provinces as $p)
                                        <option value="{{ $p }}">
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-md-6 d-flex align-items-center gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="form_offset"
                                        wire:model.defer="formData.is_offset">
                                    <label class="form-check-label" for="form_offset">Có bù trừ</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="form_overdue"
                                        wire:model.defer="formData.is_overdue">
                                    <label class="form-check-label" for="form_overdue">Trễ hạn</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="form_wt_renewal"
                                        wire:model="formData.is_renewal">
                                    <label class="form-check-label" for="form_wt_renewal">Tái ký</label>
                                </div>
                            </div>

                            @if ($formData['is_renewal'])
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">HĐ gốc (tái ký từ)</label>
                                    <select class="form-select" wire:model.defer="formData.parent_contract_id">
                                        <option value="">-- Chọn HĐ gốc --</option>
                                        @foreach ($parentContracts as $pc)
                                            <option value="{{ $pc->id }}">{{ $pc->so_hop_dong }} -
                                                {{ $pc->customer->name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Hạng mục dịch vụ</label>
                                <select class="form-select" wire:model.defer="formData.loai_dich_vu">
                                    <option value="">Chọn hạng mục</option>
                                    @foreach ($loai_dich_vu_options as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Ghi chú</label>
                                <textarea class="form-control @error('formData.note') is-invalid @enderror" rows="2"
                                    wire:model.defer="formData.note"></textarea>
                                @error('formData.note')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm"></span>
                            Lưu hợp đồng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Assignment Modal --}}
    <div wire:ignore.self class="modal fade" id="assignModalWaste" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success py-3">
                    <h5 class="modal-title fw-bold modal-title-custom"><i class="bi bi-person-check me-1"></i> Giao
                        việc hợp đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Chọn nhân viên để giao việc (có thể chọn nhiều):</p>
                    <div class="list-group" style="max-height: 320px; overflow-y: auto;">
                        @foreach ($assignable_users as $u)
                            @php
                                $roleSlug = $u->roles->first()?->name ?? '';
                                $roleDisplay = match ($roleSlug) {
                                    'it' => 'IT Admin',
                                    'giam-doc' => 'Giám đốc',
                                    'tp-kinh-doanh' => 'Trưởng phòng KD',
                                    'quan-ly' => 'Quản lý',
                                    'kinh-doanh' => 'Nhân viên KD',
                                    'ke-toan' => 'Kế toán',
                                    'tu-van' => 'Tư vấn',
                                    'ky-thuat' => 'Kỹ thuật',
                                    'marketing' => 'Marketing',
                                    default => $roleSlug,
                                };
                            @endphp
                            <label class="list-group-item list-group-item-action d-flex gap-2">
                                <input class="form-check-input flex-shrink-0 mt-1" type="checkbox"
                                    value="{{ $u->id }}" wire:model="assignUserIds">
                                <span>{{ $u->name }}<small
                                        class="text-muted d-block">{{ $roleDisplay }}</small></span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" wire:click="saveAssign"
                        wire:loading.attr="disabled" wire:target="saveAssign">
                        <span wire:loading wire:target="saveAssign"
                            class="spinner-border spinner-border-sm me-1"></span>
                        Lưu giao việc
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Workflow Modal --}}
    <div wire:ignore.self class="modal fade" id="workflowModalWaste" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-diagram-3 me-2"></i>Cập nhật tiến độ hợp đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        wire:click="closeWorkflow"></button>
                </div>
                <div class="modal-body p-0">
                    @if ($workflowContractId)
                        <livewire:admin.contracts.contract-workflow-panel :contractType="'waste'" :contractId="$workflowContractId"
                            :key="'wf-modal-waste-' . $workflowContractId" />
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.addEventListener('openFormModal', () => {
                let modal = new bootstrap.Modal(document.getElementById('formModal'));
                modal.show();
            });

            window.addEventListener('closeFormModal', () => {
                let modalElement = document.getElementById('formModal');
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            });

            window.addEventListener('openDetailModal', () => {
                let modal = new bootstrap.Modal(document.getElementById('detailModal'));
                modal.show();
            });
            window.addEventListener('openAssignModal', () => {
                new bootstrap.Modal(document.getElementById('assignModalWaste')).show();
            });
            Livewire.on('closeAssignModal', () => {
                bootstrap.Modal.getInstance(document.getElementById('assignModalWaste'))?.hide();
            });
            window.addEventListener('openWorkflowModal', () => {
                new bootstrap.Modal(document.getElementById('workflowModalWaste')).show();
            });
        </script>
    @endpush
</div>
