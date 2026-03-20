<div>
    <style>
        [x-cloak] { display: none !important; }
        .custom-filter-label { font-size: 12px; margin-bottom: 2px; text-transform: uppercase; color: #666; }
        .form-control-xs { font-size: 12px; padding: 0.25rem 0.5rem; height: auto; }
        .table-xs td, .table-xs th { font-size: 14px; padding: 0.5rem 0.25rem; }
        .modal-title-custom { color: #ffffff !important; }
        .dropdown-item.text-wrap { white-space: normal !important; }
        .form-select.text-wrap { white-space: normal !important; height: auto !important; min-height: 31px; }
        .dropdown-custom { position: relative; }
        .dropdown-menu-custom { 
            position: absolute; 
            top: 100%; 
            left: 0; 
            z-index: 1000; 
            min-width: 100%; 
            padding: .5rem 0; 
            margin: .125rem 0 0; 
            font-size: 13px; 
            color: #212529; 
            text-align: left; 
            list-style: none; 
            background-color: #fff; 
            background-clip: padding-box; 
            border: 1px solid rgba(0,0,0,.15); 
            border-radius: .25rem;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.175);
        }
        .dropdown-menu-custom .dropdown-item { font-size: 13px; padding: 0.4rem 0.8rem; }
        .dropdown-menu-custom .form-control-sm { font-size: 13px; }
        .btn-filter { font-size: 13px; padding: 0.35rem 1rem; }
    </style>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Quản lý Hợp đồng chất thải</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Quản lý Hợp đồng chất thải</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Tìm kiếm" wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="mb-0 fw-bold">Bộ lọc Hợp đồng chất thải</h6>
            <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 15L12 9L6 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Row 1 -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold custom-filter-label">Ngày ký hợp đồng</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.signed_from">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.signed_to">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold custom-filter-label">Ngày kết thúc</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.end_from">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.end_to">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold custom-filter-label">Ngày hợp đồng về</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.returned_from">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.returned_to">
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Ngày trình ký</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.submitted_from">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.submitted_to">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold custom-filter-label">Chủ xử lý</label>
                        <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                            <button class="form-select form-control-xs text-start text-wrap" 
                                    type="button" @click.prevent="open = !open"
                                    style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                {{ $handlers->find($filter['handler_id'])?->name ?? 'Chọn chủ xử lý' }}
                            </button>
                            <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false" x-cloak style="max-height: 300px; overflow-y: auto;">
                                <input type="text" x-model="search" class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..." @click.stop>
                                <button class="dropdown-item @if(!$filter['handler_id']) active @endif" 
                                        type="button"
                                        x-show="'chọn chủ xử lý'.includes(search.toLowerCase())"
                                        wire:click="$set('filter.handler_id', '')" @click="open = false">Chọn chủ xử lý</button>
                                @foreach($handlers as $handler)
                                <button class="dropdown-item text-wrap @if($filter['handler_id'] == $handler->id) active @endif" 
                                        type="button"
                                        x-show="{{ json_encode(strtolower($handler->name)) }}.includes(search.toLowerCase())"
                                        style="white-space: normal !important; word-break: break-all;"
                                        wire:click="$set('filter.handler_id', {{ $handler->id }})" @click="open = false">
                                    {{ $handler->name }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Tỉnh thành</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.province_id">
                            <option value="">Chọn tỉnh thành</option>
                        </select>
                    </div>

                    <!-- Row 3 -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Phòng ban</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.department_id">
                            <option value="">Chọn phòng ban</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Nguồn thông tin</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.source">
                            <option value="">Chọn Nguồn...</option>
                            <option value="MỚI">MỚI</option>
                            <option value="TÁI KÝ">TÁI KÝ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Phương thức thanh toán</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.payment_method">
                            <option value="">Chọn phương thức...</option>
                            <option value="Sau ký">Sau ký</option>
                            <option value="Trước ký">Trước ký</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Tình trạng</label>
                        <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                            <button class="form-select form-control-xs text-start text-wrap" 
                                    type="button" @click.prevent="open = !open"
                                    style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                {{ $filter['status'] ?: 'Chọn tình trạng' }}
                            </button>
                            <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false" x-cloak style="max-height: 300px; overflow-y: auto;">
                                <input type="text" x-model="search" class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..." @click.stop>
                                <button class="dropdown-item @if(!$filter['status']) active @endif" type="button" wire:click="$set('filter.status', '')" @click="open = false">Chọn tình trạng</button>
                                @foreach($all_statuses as $status)
                                <button class="dropdown-item @if($filter['status'] == $status) active @endif" 
                                        type="button"
                                        x-show="{{ json_encode(strtolower($status)) }}.includes(search.toLowerCase())"
                                        wire:click="$set('filter.status', '{{ $status }}')" @click="open = false">
                                    {{ $status }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Row 4 -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Loại dịch vụ</label>
                        <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                            <button class="form-select form-control-xs text-start text-wrap" 
                                    type="button" @click.prevent="open = !open"
                                    style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                {{ $filter['service_type'] ?: 'Chọn Loại dịch vụ' }}
                            </button>
                            <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false" x-cloak style="max-height: 300px; overflow-y: auto;">
                                <input type="text" x-model="search" class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..." @click.stop>
                                <button class="dropdown-item @if(!$filter['service_type']) active @endif" type="button" wire:click="$set('filter.service_type', '')" @click="open = false">Chọn Loại dịch vụ</button>
                                @foreach($service_types as $service)
                                <button class="dropdown-item @if($filter['service_type'] == $service) active @endif" 
                                        type="button"
                                        x-show="{{ json_encode(strtolower($service)) }}.includes(search.toLowerCase())"
                                        wire:click="$set('filter.service_type', '{{ $service }}')" @click="open = false">
                                    {{ $service }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Loại chất thải</label>
                        <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                            <button class="form-select form-control-xs text-start text-wrap" 
                                    type="button" @click.prevent="open = !open"
                                    style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                {{ $filter['waste_type'] ?: 'Chọn Loại chất thải' }}
                            </button>
                            <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false" x-cloak style="max-height: 300px; overflow-y: auto;">
                                <input type="text" x-model="search" class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..." @click.stop>
                                <button class="dropdown-item @if(!$filter['waste_type']) active @endif" type="button" wire:click="$set('filter.waste_type', '')" @click="open = false">Chọn Loại chất thải</button>
                                @foreach($waste_types as $waste)
                                <button class="dropdown-item @if($filter['waste_type'] == $waste) active @endif" 
                                        type="button"
                                        x-show="{{ json_encode(strtolower($waste)) }}.includes(search.toLowerCase())"
                                        wire:click="$set('filter.waste_type', '{{ $waste }}')" @click="open = false">
                                    {{ $waste }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Tình trạng tái ký</label>
                        <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                            <button class="form-select form-control-xs text-start text-wrap" 
                                    type="button" @click.prevent="open = !open"
                                    style="width: 100%; white-space: normal !important; height: auto !important; min-height: 31px;">
                                {{ $filter['renewal_status'] ?: 'Chọn tình trạng' }}
                            </button>
                            <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false" x-cloak style="max-height: 300px; overflow-y: auto;">
                                <input type="text" x-model="search" class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..." @click.stop>
                                <button class="dropdown-item @if(!$filter['renewal_status']) active @endif" type="button" wire:click="$set('filter.renewal_status', '')" @click="open = false">Chọn tình trạng</button>
                                @foreach($renewal_statuses as $renewal)
                                <button class="dropdown-item @if($filter['renewal_status'] == $renewal) active @endif" 
                                        type="button"
                                        x-show="{{ json_encode(strtolower($renewal)) }}.includes(search.toLowerCase())"
                                        wire:click="$set('filter.renewal_status', '{{ $renewal }}')" @click="open = false">
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
                            @foreach($voucher_statuses as $vstatus)
                                <option value="{{ $vstatus }}">{{ $vstatus }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Row 5 -->
                    <div class="col-md-3 d-flex align-items-end gap-3 pb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="offset" wire:model.live="filter.is_offset">
                            <label class="form-check-label small" for="offset">Có bù trừ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="overdue" wire:model.live="filter.is_overdue">
                            <label class="form-check-label small" for="overdue">Trễ hạn</label>
                        </div>
                    </div>

                    <div class="col-md-9 d-flex align-items-end gap-2 justify-content-start">
                        <button class="btn btn-info text-white px-4 d-flex align-items-center gap-2 btn-filter" wire:click="$refresh">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                                <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Lọc
                        </button>
                        <button class="btn btn-secondary px-4 d-flex align-items-center gap-2 btn-filter" wire:click="resetFilters">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 12C3 16.9706 7.02944 21 12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C10.5 3 9 3.5 8 4.5M3 12H8V7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Xóa lọc
                        </button>
                        <button class="btn btn-success px-4 d-flex align-items-center gap-2 btn-filter">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 3V15M12 15L8 11M12 15L16 11M2 17V19C2 20.1046 2.89543 21 4 21H20C21.1046 21 22 20.1046 22 19V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Xuất Excel
                        </button>
                        <button class="btn btn-primary px-4 d-flex align-items-center gap-2 btn-filter">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 2H8C4.68629 2 2 4.68629 2 8V16C2 19.3137 4.68629 22 8 22H16C19.3137 22 22 19.3137 22 16V8C22 4.68629 19.3137 2 16 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Quy trình
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Danh sách Hợp đồng chất thải</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-xs">
                <thead class="bg-light bg-opacity-50">
                    <tr class="small text-muted text-uppercase fw-bold">
                        <th class="ps-4">Thông tin hợp đồng</th>
                        <th>Khách hàng</th>
                        <th class="text-center">Giá trị hợp đồng</th>
                        <th class="text-center">Hoa hồng</th>
                        <th class="text-center">Doanh số</th>
                        <th class="text-center">Tình trạng tái ký</th>
                        <th class="text-center">Tình trạng chứng từ</th>
                        <th class="text-center">Tình trạng</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docs as $doc)
                    <tr class="border-bottom border-light">
                        <td class="ps-4 py-4" style="max-width: 250px;">
                            <div class="d-flex flex-column">
                                <span class="small">SHD CXL: <span class="fw-bold">{{ $doc->shd_cxl }}</span></span>
                                <span class="small">Ngày ký hợp đồng:</span>
                                <span class="small fw-bold">{{ $doc->signed_at ? $doc->signed_at->format('d/m/Y') : '-' }}</span>
                                <span class="small">NVCS: <span class="fw-bold">{{ $doc->staff?->name }}</span></span>
                            </div>
                        </td>
                        <td class="py-4" style="max-width: 400px;">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-uppercase text-primary">{{ $doc->customer?->name }}</span>
                                <span class="small">{{ $doc->customer?->representative }} - {{ $doc->customer?->phone }} - {{ $doc->customer?->email }}</span>
                                <span class="small text-muted">{{ $doc->customer?->address }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->value) }}đ</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->commission) }}đ</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->revenue) }}đ</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $doc->renewal_status }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $doc->voucher_status }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <span class="small">{{ $doc->status }}</span>
                                <span class="small text-muted">Chủ xử lý</span>
                                <span class="small text-muted">{{ $doc->submitted_at ? $doc->submitted_at->format('d/m/Y') : '-' }}</span>
                            </div>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm p-0 text-danger" wire:click="viewDetail({{ $doc->id }})">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button class="btn btn-sm p-0 text-info">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M21 11.5V11C21 6.02944 16.9706 2 12 2C7.02944 2 3 6.02944 3 11V11.5C3 13.5 3.5 15.5 4.5 17L3 21L7 19.5C8.5 20.5 10.5 21 12.5 21C17.4706 21 21.5 16.9706 21.5 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">Không tìm thấy hợp đồng nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($docs->hasPages())
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
                    <h5 class="modal-title fw-bold modal-title-custom">Thông tin Hợp Đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if($selectedDoc)
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3" style="width: 25%;">Ghi chú</th>
                                    <td class="px-4 py-3">Ghi chú : {{ $selectedDoc->note }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Khách hàng</th>
                                    <td class="px-4 py-3 text-uppercase">{{ $selectedDoc->customer?->name }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Chủ xử lý</th>
                                    <td class="px-4 py-3">{{ $selectedDoc->handler?->name }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Số hợp đồng AD</th>
                                    <td class="px-4 py-3">{{ $selectedDoc->shd_ad }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Nội dung</th>
                                    <td class="px-4 py-3">{{ $selectedDoc->content }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Giá trị hợp đồng</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedDoc->value) }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Hoa hồng</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedDoc->commission) }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Doanh số</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedDoc->revenue) }}đ</td>
                                </tr>
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
                                    <td class="px-4 py-3">{{ $selectedDoc->signed_at ? $selectedDoc->signed_at->format('d/m/Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Ngày hiệu lực</th>
                                    <td class="px-4 py-3">{{ $selectedDoc->effective_at ? $selectedDoc->effective_at->format('d/m/Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Ngày kết thúc</th>
                                    <td class="px-4 py-3">{{ $selectedDoc->end_at ? $selectedDoc->end_at->format('d/m/Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Ngày trình ký</th>
                                    <td class="px-4 py-3 text-danger">{{ $selectedDoc->submitted_at ? $selectedDoc->submitted_at->format('d/m/Y') : '-' }}</td>
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
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.addEventListener('openDetailModal', () => {
            let modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
        });
    </script>
    @endpush
</div>
