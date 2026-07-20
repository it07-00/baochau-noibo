<div>

    <header class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-start gap-3">
            <span class="d-none d-sm-inline-flex align-items-center justify-content-center rounded-3 text-bg-primary p-3 shadow-sm" aria-hidden="true"><i class="fa-solid fa-recycle fa-lg"></i></span>
            <div>
            <h1 class="h4 fw-bold text-body mb-1">Hợp đồng chất thải và tiếng ồn</h1>
            <p class="text-secondary-emphasis mb-1">Theo dõi tài chính, chứng từ, phân công và tiến độ thực hiện hợp đồng.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng thống kê</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chất thải và tiếng ồn</li>
                </ol>
            </nav>
            </div>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            <div class="input-group">
                <span class="input-group-text bg-body-tertiary text-primary"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                <input type="search" class="form-control" placeholder="Tìm khách hàng, số hợp đồng..." wire:model.live.debounce.300ms="search" aria-label="Tìm kiếm hợp đồng">
            </div>
            @can('contracts-waste.create')
                <button type="button" wire:click="create" class="btn btn-primary text-nowrap d-inline-flex align-items-center justify-content-center gap-2" wire:loading.attr="disabled" wire:target="create">
                    <span wire:loading.remove wire:target="create"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
                    <span wire:loading wire:target="create" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <span>Thêm hợp đồng</span>
                </button>
            @endcan
        </div>
    </header>

    <!-- Filter Card -->
    <section class="card border shadow-sm mb-4" aria-labelledby="waste-contract-filter-heading">
        <div class="card-header bg-body py-3 px-3 px-lg-4 d-flex align-items-center justify-content-between border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-2 text-bg-primary p-2" aria-hidden="true"><i class="fa-solid fa-sliders"></i></span>
                <div>
                    <h2 id="waste-contract-filter-heading" class="h6 fw-bold text-body mb-1">Bộ lọc hợp đồng</h2>
                    <p class="small text-secondary-emphasis mb-0">Lọc theo thời gian, phụ trách, phân loại và trạng thái xử lý.</p>
                </div>
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-controls="filterCollapse" aria-expanded="true">
                <i class="fa-solid fa-chevron-up" aria-hidden="true"></i><span class="visually-hidden">Thu gọn bộ lọc</span>
            </button>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3">
                    @unless (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                        <!-- Row 1 -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Ngày ký hợp đồng</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control"
                                    wire:model.live="filter.signed_from">
                                <input type="date" class="form-control"
                                    wire:model.live="filter.signed_to">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Ngày kết thúc</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control"
                                    wire:model.live="filter.end_from">
                                <input type="date" class="form-control" wire:model.live="filter.end_to">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Ngày xuất hóa đơn</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control"
                                    wire:model.live="filter.returned_from">
                                <input type="date" class="form-control"
                                    wire:model.live="filter.returned_to">
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Ngày trình ký</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control"
                                    wire:model.live="filter.submitted_from">
                                <input type="date" class="form-control"
                                    wire:model.live="filter.submitted_to">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Nhà thầu phụ</label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select text-start text-wrap" type="button"
                                    @click.prevent="open = !open"
                                    >
                                    {{ $handlers->find($filter['handler_id'])?->name ?? 'Chọn nhà thầu phụ' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open" @click.away="open = false"
                                    x-cloak >
                                    <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                        placeholder="Tìm kiếm..." @click.stop>
                                    <button class="dropdown-item @if (!$filter['handler_id']) active @endif"
                                        type="button"
                                        x-show="'chọn nhà thầu phụ'.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                        wire:click="$set('filter.handler_id', '')" @click="open = false">Chọn nhà thầu phụ</button>
                                    @foreach ($handlers as $handler)
                                        <button
                                            class="dropdown-item text-wrap @if ($filter['handler_id'] == $handler->id) active @endif"
                                            type="button"
                                            x-show="{{ json_encode(mb_strtolower($handler->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            class="text-break"
                                            wire:click="$set('filter.handler_id', {{ $handler->id }})"
                                            @click="open = false">
                                            {{ $handler->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @if (auth()->user()->hasAnyRole([\App\Enums\Role::TP_KINH_DOANH->value, \App\Enums\Role::GIAM_DOC->value]))
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Nhân viên chăm sóc</label>
                                <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                    <button class="form-select text-start text-wrap" type="button"
                                        @click.prevent="open = !open"
                                        >
                                        {{ $staffs->find($filter['staff_id'])?->name ?? 'Chọn nhân viên' }}
                                    </button>
                                    <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open" @click.away="open = false"
                                        x-cloak >
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
                                                class="text-break"
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
                            <label class="form-label fw-semibold small">Tỉnh thành</label>
                            <select class="form-select" wire:model.live="filter.province">
                                <option value="">Chọn tỉnh thành</option>
                                @foreach ($provinces as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Row 3 -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Nguồn thông tin</label>
                            <select class="form-select" wire:model.live="filter.info_source">
                                <option value="">Chọn Nguồn...</option>
                                @foreach ($info_sources as $src)
                                    <option value="{{ $src }}">{{ $src }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Phương thức thanh toán</label>
                            <select class="form-select" wire:model.live="filter.payment_method">
                                <option value="">Chọn phương thức...</option>
                                @foreach ($payment_methods as $pm)
                                    <option value="{{ $pm }}">{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        {{-- Bộ lọc cho tư vấn / kỹ thuật --}}
                        @include('livewire.admin.contracts.partials.restricted-contract-filters')
                    @endunless
                    @unless (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Tình trạng</label>
                        <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                            <button class="form-select text-start text-wrap" type="button"
                                @click.prevent="open = !open"
                                >
                                {{ $filter['status'] ?: 'Chọn tình trạng' }}
                            </button>
                            <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open" @click.away="open = false"
                                x-cloak >
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

                    @unless (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                        <!-- Row 4 -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Loại dịch vụ</label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select text-start text-wrap" type="button"
                                    @click.prevent="open = !open"
                                    >
                                    {{ $filter['service_type'] ?: 'Chọn Loại dịch vụ' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open" @click.away="open = false"
                                    x-cloak >
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
                            <label class="form-label fw-semibold small">Loại chất thải</label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select text-start text-wrap" type="button"
                                    @click.prevent="open = !open"
                                    >
                                    {{ $filter['waste_type'] ?: 'Chọn Loại chất thải' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open" @click.away="open = false"
                                    x-cloak >
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
                            <label class="form-label fw-semibold small">Tình trạng tái ký</label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select text-start text-wrap" type="button"
                                    @click.prevent="open = !open"
                                    >
                                    {{ $filter['renewal_status'] ?: 'Chọn tình trạng' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open" @click.away="open = false"
                                    x-cloak >
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
                            <label class="form-label fw-semibold small">Tình trạng phản hồi chứng từ</label>
                            <select class="form-select" wire:model.live="filter.voucher_status">
                                <option value="">Chọn tình trạng</option>
                                @foreach ($voucher_statuses as $vstatus)
                                    <option value="{{ $vstatus }}">{{ $vstatus }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Hạng mục dịch vụ</label>
                            <select class="form-select" wire:model.live="filter.loai_dich_vu">
                                <option value="">Chọn hạng mục</option>
                                @foreach ($loai_dich_vu_options as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Row 5 -->
                        <div class="col-md-3 d-flex align-items-end flex-wrap gap-3 pb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="offset"
                                    wire:model.live="filter.is_offset">
                                <label class="form-check-label " for="offset">Có bù trừ</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="overdue"
                                    wire:model.live="filter.is_overdue">
                                <label class="form-check-label " for="overdue">Trễ hạn</label>
                            </div>
                        </div>
                    @else
                        <div class="col-md-2 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="overdue"
                                    wire:model.live="filter.is_overdue">
                                <label class="form-check-label " for="overdue">Trễ hạn</label>
                            </div>
                        </div>
                    @endunless

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Sắp xếp</label>
                        <select class="form-select" wire:model.live="sortDirection">
                            <option value="asc">Cũ nhất trước</option>
                            <option value="desc">Mới nhất trước</option>
                        </select>
                    </div>
                    @endunless

                    <div class="col-md-6 d-flex align-items-end gap-2 flex-wrap justify-content-start">
                        <button class="btn btn-primary px-3" wire:click="$refresh" wire:loading.attr="disabled">
                            <i class="fa-solid fa-magnifying-glass me-1"></i>Lọc
                        </button>
                        <button class="btn btn-outline-secondary px-3" wire:click="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters">
                            <i class="fa-solid fa-rotate-left me-1"></i>Xóa lọc
                        </button>
                        @if ($this->canBulkDelete)
                            <button class="btn btn-danger px-3" wire:click="bulkDeleteSelected"
                                wire:confirm="Xác nhận xóa các hợp đồng đã chọn?"
                                @if (empty($selectedDocIds)) disabled @endif>
                                <i class="fa-solid fa-trash me-1"></i>Xóa đã chọn ({{ count($selectedDocIds) }})
                            </button>
                        @endif
                        @unless (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                            <button wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel"
                                class="btn btn-success px-3">
                                <span wire:loading wire:target="exportExcel"
                                    class="spinner-border spinner-border-sm me-1"></span>
                                <i wire:loading.remove wire:target="exportExcel"
                                    class="fa-solid fa-file-excel me-1"></i>Xuất Excel
                            </button>
                        @endunless
                        <button type="button" class="btn btn-outline-primary px-3">
                            <i class="fa-solid fa-sitemap me-1"></i>Quy trình
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Table Card -->
    <section class="card border shadow-sm overflow-hidden" aria-labelledby="waste-contract-list-heading">
        <div class="card-header bg-body px-3 px-lg-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary p-2" aria-hidden="true"><i class="fa-solid fa-table-list"></i></span>
                <div>
                    <h2 id="waste-contract-list-heading" class="h6 fw-bold text-body mb-1">Danh sách hợp đồng</h2>
                    <p class="small text-secondary-emphasis mb-0">Hợp đồng chất thải và tiếng ồn theo phạm vi được phân quyền</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-primary rounded-pill px-3 py-2">{{ number_format($docs->total(), 0, ',', '.') }} hợp đồng</span>
                <div wire:loading.flex wire:target="search,filter,sortDirection,resetFilters" class="align-items-center gap-2 small text-primary" role="status"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>Đang cập nhật...</span></div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr class="text-nowrap">
                        @if ($this->canBulkDelete)
                            <th class="text-center w-42px" >Chọn</th>
                        @endif
                        <th class="text-center w-45px" >STT</th>
                        <th class="ps-4 col-ct-customer">Thông tin hợp đồng</th>
                        @unless (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                            <th class="text-center col-ct-finance">Tài chính</th>
                        @endunless
                        <th class="text-center">Tình trạng tái ký</th>
                        @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                        <th class="text-center voucher-status-cell">Tình trạng chứng từ</th>
                        @endunless
                        @unless (auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value))
                            <th class="text-center col-ct-assigned">Được giao</th>
                            <th class="text-center col-ct-deadline">Hạn chót</th>
                        @endunless
                        <th class="text-center col-ct-status">Tình trạng</th>
                        <th class="text-center col-ct-actions pe-2">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @forelse($docs as $doc)
                        <tr class="border-bottom border-light" wire:key="waste-row-{{ $doc->id }}">
                            @if ($this->canBulkDelete)
                                <td class="text-center">
                                    @if (!auth()->user()->hasRole(\App\Enums\Role::TP_KINH_DOANH->value) || $doc->staff_id === auth()->id())
                                        <input class="form-check-input" type="checkbox"
                                            wire:model.live="selectedDocIds" value="{{ $doc->id }}">
                                    @endif
                                </td>
                            @endif
                            <td class="text-center text-muted  fw-semibold">
                                {{ ($docs->currentPage() - 1) * $docs->perPage() + $loop->iteration }}
                            </td>
                            <td class="ps-3 py-2 col-ct-customer">
                                <div class="d-flex flex-column gap-1">
                                    <a href="{{ $doc->customer ? route('app.customers.contracts', $doc->customer->slug) : '#' }}" class="fw-bold text-primary text-decoration-none lh-sm">
                                        {{ $doc->customer?->name }}
                                    </a>
                                    @if($doc->customer?->representative || $doc->customer?->phone)
                                    <span class="text-muted fs-85">{{ implode(' - ', array_filter([$doc->customer?->representative, $doc->customer?->phone])) }}</span>
                                    @endif
                                    @if($doc->customer?->address)
                                    <span class="text-muted fs-85">{{ Str::limit($doc->customer?->address, 50) }}</span>
                                    @endif
                                    <div class="d-flex gap-2 flex-wrap contract-text-08 border-top mt-1 pt-1 text-secondary">
                                        @if($doc->shd_cxl)<span>NTP: <span class="fw-semibold text-dark">{{ $doc->shd_cxl }}</span></span>@endif
                                        @if($doc->shd_bc)<span>BC: <span class="fw-semibold text-dark">{{ $doc->shd_bc }}</span></span>@endif
                                        @if($doc->signed_at)<span>Ký: <span class="fw-semibold text-dark">{{ $doc->signed_at->format('d/m/Y') }}</span></span>@endif
                                        @if($doc->staff?->name)<span>CS: <span class="fw-semibold text-dark">{{ $doc->staff->name }}</span></span>@endif
                                    </div>
                                </div>
                            </td>
                            @unless (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                                <td class="py-2 px-3 col-ct-finance">
                                    <div class="d-flex flex-column gap-1 contract-text-08">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Giá trị HĐ:</span>
                                            <span class="fw-bold text-danger">{{ number_format($doc->value) }}đ</span>
                                        </div>
                                        @if($doc->commission)
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Hoa hồng:</span>
                                            <span class="fw-bold text-danger">{{ number_format($doc->commission) }}đ</span>
                                        </div>
                                        @endif
                                        @if($doc->revenue)
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Doanh số:</span>
                                            <span class="fw-bold text-danger">{{ number_format($doc->revenue) }}đ</span>
                                        </div>
                                        @endif
                                        @if($doc->ncc_payment)
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Chi nhà cung cấp:</span>
                                            <span class="fw-bold text-danger">{{ number_format($doc->ncc_payment) }}đ</span>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            @endunless
                            <td class="text-center">
                                <span
                                    class="badge {{ $this->renewalBadgeClassForDoc($doc) }}">{{ $doc->renewal_status ?: 'Chưa chọn' }}</span>
                            </td>
                            @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                            <td class="text-center voucher-status-cell">
                                <span class="badge voucher-status-badge {{ $this->voucherBadgeInfoForDoc($doc)['class'] }}"
                                    title="{{ $this->voucherBadgeInfoForDoc($doc)['full_value'] }}">
                                    {{ $this->voucherBadgeInfoForDoc($doc)['label'] }}
                                </span>
                            </td>
                            @endunless
                            @unless (auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value))
                                <td class="text-center">
                                    @if ($doc->assignments->count() > 0)
                                        <div class="d-flex flex-column gap-1 align-items-center">
                                            @include('livewire.admin.contracts.partials.assignment-compact-list', [
                                                'assignments' => $doc->assignments,
                                            ])
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    <div class="mt-2">
                                        <progress class="w-100" value="{{ $this->workflowProgressMeta($doc)['progressPercent'] }}" max="100" aria-label="Tiến độ {{ $this->workflowProgressMeta($doc)['progressPercent'] }} phần trăm"></progress>
                                        <span class="small fw-semibold text-{{ $this->workflowProgressMeta($doc)['progressColor'] }}">{{ $this->workflowProgressMeta($doc)['completedSteps'] }}/{{ $this->workflowProgressMeta($doc)['totalSteps'] }} bước</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($this->deadlineMeta($doc)['deadline'])
                                        @if($this->deadlineMeta($doc)['isFinished'])
                                            <span class="fw-semibold text-success fs-85" >{{ $this->deadlineMeta($doc)['deadline']->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-success fs-60" ><i class="fa-solid fa-circle-check me-1"></i>Hoàn thành</span>
                                        @elseif($this->deadlineMeta($doc)['isOverdue'])
                                            <span class="fw-bold text-danger fs-85" >{{ $this->deadlineMeta($doc)['deadline']->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-danger fs-60" ><i class="fa-solid fa-triangle-exclamation me-1"></i>Quá hạn {{ abs($this->deadlineMeta($doc)['daysLeft']) }} ngày</span>
                                        @elseif($this->deadlineMeta($doc)['isNearDue'])
                                            <span class="fw-semibold text-warning fs-85" >{{ $this->deadlineMeta($doc)['deadline']->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-warning text-dark fs-60" ><i class="fa-solid fa-clock me-1"></i>Còn {{ $this->deadlineMeta($doc)['daysLeft'] }} ngày</span>
                                        @else
                                            <span class="fw-semibold text-success fs-85" >{{ $this->deadlineMeta($doc)['deadline']->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-success bg-opacity-75 fs-60" >Còn {{ $this->deadlineMeta($doc)['daysLeft'] }} ngày</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endunless
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    @if (!$this->canUpdateStatusForDoc($doc))
                                        <span class="btn btn-sm rounded-pill fw-bold text-nowrap {{ $this->wasteStatusBootstrapClassForDoc($doc) }}" aria-label="Tình trạng {{ $doc->status ?: 'chưa cập nhật' }}">{{ $doc->status ?: '—' }}</span>
                                    @else
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm dropdown-toggle rounded-pill fw-bold text-nowrap {{ $this->wasteStatusBootstrapClassForDoc($doc) }}" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">{{ $doc->status ?: '—' }}</button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                @foreach ($all_statuses as $opt)
                                                <li><button type="button" class="dropdown-item d-flex align-items-center justify-content-between gap-3 {{ $doc->status === $opt ? 'active' : '' }}" wire:click="updateStatus({{ $doc->id }}, '{{ addslashes($opt) }}')"><span>{{ $opt }}</span>@if ($doc->status === $opt)<i class="fa-solid fa-check" aria-hidden="true"></i>@endif</button></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <span class="small text-secondary-emphasis mt-1">{{ $doc->submitted_at ? $doc->submitted_at->format('d/m/Y') : '-' }}</span>
                                </div>
                            </td>
                            <td class="text-center pe-3 pe-lg-4">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="viewDetail({{ $doc->id }})"><i class="fa-regular fa-eye me-1" aria-hidden="true"></i>Xem</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false"><span class="visually-hidden">Mở thêm thao tác</span></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><button type="button" class="dropdown-item" wire:click="viewDetailDocs({{ $doc->id }})"><i class="fa-regular fa-file-pdf me-2 text-danger" aria-hidden="true"></i>Tài liệu hợp đồng</button></li>
                                    @if ($this->canAssign())
                                    <li><button type="button" class="dropdown-item" wire:click="openAssign({{ $doc->id }})"><i class="fa-solid fa-user-check me-2 text-success" aria-hidden="true"></i>Giao việc</button></li>
                                    @endif
                                    @if (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                                    <li><button type="button" class="dropdown-item" wire:click="openWorkflow({{ $doc->id }})"><i class="fa-solid fa-list-check me-2 text-info" aria-hidden="true"></i>Cập nhật tiến độ</button></li>
                                    @endif
                                    @can('contracts-waste.edit')
                                        @if ($this->canManageOwnedDoc($doc))
                                            @if (!auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value))
                                    <li><button type="button" class="dropdown-item" wire:click="duplicate({{ $doc->id }})"><i class="fa-regular fa-copy me-2 text-secondary" aria-hidden="true"></i>Nhân bản</button></li>
                                            @endif
                                    <li><button type="button" class="dropdown-item" wire:click="edit({{ $doc->id }})"><i class="fa-regular fa-pen-to-square me-2 text-warning" aria-hidden="true"></i>Chỉnh sửa</button></li>
                                        @endif
                                    @endcan
                                    @can('contracts-waste.delete')
                                        @if ($this->canManageOwnedDoc($doc))
                                    <li><hr class="dropdown-divider"></li>
                                    <li><button type="button" class="dropdown-item text-danger" wire:click="delete({{ $doc->id }})" wire:confirm="Xác nhận xóa hợp đồng này?"><i class="fa-regular fa-trash-can me-2" aria-hidden="true"></i>Xóa hợp đồng</button></li>
                                        @endif
                                    @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($this->isRestrictedRole ? 6 : 9) + ($this->canBulkDelete ? 1 : 0) }}"
                                class="text-center py-5 text-muted">Không tìm thấy hợp đồng nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($docs->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $docs->links('livewire.admin.contracts.pagination') }}
            </div>
        @endif
    </section>

    <!-- Detail Modal -->
    <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title fw-bold text-white">HĐ {{ $contractTypeName }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if ($selectedDoc)
                        <div x-data="{ tab: @js($detailActiveTab ?? 'info') }">
                        {{-- Tabs Navigation --}}
                        <ul class="nav nav-tabs px-4 pt-3" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link fw-semibold" :class="{ active: tab === 'info' }"
                                    @click="tab = 'info'" type="button">
                                    <i class="fa-solid fa-circle-info me-1"></i>Thông tin HĐ
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold" :class="{ active: tab === 'progress' }"
                                    @click="tab = 'progress'" type="button">
                                    <i class="fa-solid fa-sitemap me-1"></i>Tiến độ hoàn thành
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold" :class="{ active: tab === 'docs' }"
                                    @click="tab = 'docs'" type="button">
                                    <i class="fa-solid fa-paperclip me-1"></i>Tài liệu đính kèm
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            {{-- Tab 1: Thông tin HĐ --}}
                            <div class="tab-pane" :class="{ 'show active': tab === 'info' }" id="tab-info-waste-{{ $selectedDoc->id }}"
                                role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3 w-25">Ghi chú</th>
                                                <td class="px-4 py-3">{{ $selectedDoc->notes }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Khách hàng</th>
                                                <td class="px-4 py-3">
                                                    <a href="{{ $selectedDoc->customer ? route('app.customers.contracts', $selectedDoc->customer->slug) : '#' }}" class="text-decoration-none fw-bold text-primary">
                                                        {{ $selectedDoc->customer?->name }}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3">Nhà thầu phụ</th>
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
                                            @unless (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
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
                                                <tr>
                                                    <th class="bg-light fw-bold px-4 py-3">Chi nhà cung cấp</th>
                                                    <td class="px-4 py-3 fw-bold text-danger">
                                                        {{ number_format($selectedDoc->ncc_payment ?? 0) }}đ</td>
                                                </tr>
                                            @endunless
                                            @include('livewire.admin.contracts.partials.contract-detail-extra-fields')
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
                                                <td class="px-4 py-3">{{ $selectedDoc->info_source }}</td>
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
                                                <th class="bg-light fw-bold px-4 py-3">Ngày xuất hóa đơn</th>
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
                                                                    class="badge {{ $assign->user_id ? 'bg-primary' : 'bg-warning text-dark' }} me-1">{{ $assign->user?->name ?? $assign->external_assignee }}{{ $assign->user_id ? '' : ' (Ngoài)' }}</span>
                                                                <small class="text-muted">— giao bởi
                                                                    {{ $assign->assigner?->name }} lúc
                                                                    {{ $assign->created_at?->format('d/m/Y H:i') }}</small>
                                                                @if($assign->deadline)
                                                                    <br><small class="text-warning fw-semibold"><i class="fa-solid fa-calendar-day me-1"></i>Hạn: {{ $assign->deadline->format('d/m/Y') }}</small>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted ">Chưa giao việc</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light fw-bold px-4 py-3 align-middle" colspan="2"><i
                                                        class="fa-solid fa-book me-1"></i> Ghi chú tiến độ</th>
                                            </tr>
                                            @if ($progressNotes && count($progressNotes) > 0)
                                                @foreach ($progressNotes as $pNote)
                                                    <tr>
                                                        <td colspan="2" class="py-2 ps-4">
                                                            <div class="d-flex flex-column">
                                                                <span
                                                                    class=" fw-bold text-primary">{{ $pNote->user?->name }}
                                                                    <span class="text-muted fw-normal">—
                                                                        {{ $pNote->created_at?->format('d/m/Y H:i') }}</span></span>
                                                                <span class="mt-1">{{ $pNote->note }}</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="2" class="text-muted  ps-4 py-2">Chưa có ghi
                                                        chú tiến độ nào.</td>
                                                </tr>
                                            @endif
                                            @if (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                                                <tr>
                                                    <td colspan="2" class="px-4 pb-3 pt-2">
                                                        <textarea class="form-control form-control-sm mb-2" rows="2" wire:model="progressNote"
                                                            placeholder="Nhập ghi chú tiến độ..."></textarea>
                                                        @error('progressNote')
                                                            <div class="text-danger  mb-1">{{ $message }}</div>
                                                        @enderror
                                                        <button class="btn btn-sm btn-primary"
                                                            wire:click="addProgressNote({{ $selectedDoc->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="addProgressNote">
                                                            <span wire:loading wire:target="addProgressNote"
                                                                class="spinner-border spinner-border-sm me-1"></span>
                                                            <i class="fa-solid fa-plus me-1"></i> Thêm ghi chú
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab 2: Tiến độ --}}
                            <div wire:ignore wire:key="progress-tab-waste-{{ $selectedDoc->id }}" class="tab-pane" :class="{ 'show active': tab === 'progress' }" id="tab-progress-waste-{{ $selectedDoc->id }}"
                                role="tabpanel">
                                <livewire:admin.contracts.contract-workflow-progress :contractType="'waste'" :contractId="$selectedDoc->id"
                                    :key="'progress-waste-' . $selectedDoc->id" />
                            </div>

                            {{-- Tab 3: Tài liệu đính kèm --}}
                            <div class="tab-pane" :class="{ 'show active': tab === 'docs' }" id="tab-docs-waste-{{ $selectedDoc->id }}" role="tabpanel">
                                <div class="p-3">
                                    @include('livewire.admin.contracts.partials.contract-pdf-files')
                                </div>
                            </div>
                        </div>
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
                        @if ($isEditing)
                            Cập nhật Hợp đồng
                        @elseif ($isDuplicating)
                            Nhân bản Hợp đồng
                        @else
                            Thêm Hợp đồng mới
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold text-primary small text-uppercase" style="white-space:nowrap"><i class="fa-solid fa-file-text me-1"></i>Thông tin hợp đồng</span>
                                    <hr class="flex-fill my-0 border-primary border-opacity-25">
                                </div>
                            </div>
                            @if ($isEditing && auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value))
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Số HĐ NTP</label>
                                    <input type="text" class="form-control" wire:model.defer="formData.shd_cxl">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Số HĐ BC</label>
                                    <input type="text" class="form-control" wire:model.defer="formData.shd_bc">
                                </div>
                            @endif
                            <div class="{{ ($isEditing && auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value)) ? 'col-md-4' : 'col-md-6' }}">
                                <label class="form-label small fw-semibold">Khách hàng <span
                                        class="text-danger">*</span></label>
                                <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                    <button
                                        class="form-select text-start text-wrap @error('formData.customer_id') is-invalid @enderror select-full"
                                        type="button" @click.prevent="open = !open"
                                        >
                                        {{ $customers->find($formData['customer_id'] ?? '')?->name ?? 'Chọn khách hàng' }}
                                    </button>
                                    <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open"
                                        @click.away="open = false" x-cloak
                                        >
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
                                                class="text-wrap"
                                                wire:click="$set('formData.customer_id', {{ $customer->id }})"
                                                @click="open = false">
                                                {{ $customer->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="text" wire:model="newCustomerName" class="form-control mt-2"
                                    placeholder="Hoặc nhập tên khách hàng mới">
                                @error('newCustomerName')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                                @error('formData.customer_id')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Nhà thầu phụ</label>
                                <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                    <button
                                        class="form-select text-start text-wrap @error('formData.handler_id') is-invalid @enderror select-full"
                                        type="button" @click.prevent="open = !open"
                                        >
                                        {{ $handlers->find($formData['handler_id'] ?? '')?->name ?? 'Chọn nhà thầu phụ' }}
                                    </button>
                                    <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open"
                                        @click.away="open = false" x-cloak
                                        >
                                        <input type="text" x-model="search"
                                            class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..."
                                            @click.stop>
                                        <button class="dropdown-item @if (empty($formData['handler_id'])) active @endif"
                                            type="button" x-show="!search.length"
                                            wire:click="$set('formData.handler_id', '')" @click="open = false">Chọn
                                            nhà thầu phụ</button>
                                        @foreach ($handlers as $h)
                                            <button
                                                class="dropdown-item text-wrap @if (($formData['handler_id'] ?? '') == $h->id) active @endif"
                                                type="button"
                                                x-show="{{ json_encode(mb_strtolower($h->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                                class="text-wrap"
                                                wire:click="$set('formData.handler_id', {{ $h->id }})"
                                                @click="open = false">
                                                {{ $h->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                @error('formData.handler_id')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            @if (auth()->user()->hasAnyRole([\App\Enums\Role::TP_KINH_DOANH->value, \App\Enums\Role::GIAM_DOC->value]))
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Nhân viên <span
                                            class="text-danger">*</span></label>
                                    <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                        <button
                                            class="form-select text-start text-wrap @error('formData.staff_id') is-invalid @enderror select-full"
                                            type="button" @click.prevent="open = !open"
                                            >
                                            {{ $staffs->find($formData['staff_id'] ?? '')?->name ?? 'Chọn nhân viên' }}
                                        </button>
                                        <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open"
                                            @click.away="open = false" x-cloak
                                            >
                                            <input type="text" x-model="search"
                                                class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..."
                                                @click.stop>
                                            <button
                                                class="dropdown-item @if (empty($formData['staff_id'])) active @endif"
                                                type="button" x-show="!search.length"
                                                wire:click="$set('formData.staff_id', '')" @click="open = false">Chọn
                                                nhân
                                                viên</button>
                                            @foreach ($staffs as $s)
                                                <button
                                                    class="dropdown-item text-wrap @if (($formData['staff_id'] ?? '') == $s->id) active @endif"
                                                    type="button"
                                                    x-show="{{ json_encode(mb_strtolower($s->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                                    class="text-wrap"
                                                    wire:click="$set('formData.staff_id', {{ $s->id }})"
                                                    @click="open = false">
                                                    {{ $s->name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('formData.staff_id')
                                        <div class="text-danger  mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                            <div class="{{ auth()->user()->hasAnyRole([\App\Enums\Role::TP_KINH_DOANH->value, \App\Enums\Role::GIAM_DOC->value]) ? 'col-md-4' : 'col-md-8' }}">
                                <label class="form-label small fw-semibold">Nội dung</label>
                                <textarea class="form-control @error('formData.content') is-invalid @enderror" rows="2"
                                    wire:model.defer="formData.content"></textarea>
                                @error('formData.content')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 d-none">
                                <label class="form-label small fw-semibold">Phòng ban</label>
                                <select class="form-select" wire:model.defer="formData.department_id">
                                    <option value="">Chọn phòng ban</option>
                                    @foreach ($departments as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold text-primary small text-uppercase" style="white-space:nowrap"><i class="fa-solid fa-coins me-1"></i>Giá trị & Tài chính</span>
                                    <hr class="flex-fill my-0 border-primary border-opacity-25">
                                </div>
                            </div>
                            @include('livewire.admin.contracts.partials.payment-percentage-field')
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Giá trị hợp đồng <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control money-input @error('formData.value') is-invalid @enderror"
                                        wire:model.defer="formData.value">
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('formData.value')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Hoa hồng</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control money-input @error('formData.commission') is-invalid @enderror"
                                        wire:model.defer="formData.commission">
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('formData.commission')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Doanh số thực</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control money-input @error('formData.revenue') is-invalid @enderror"
                                        wire:model.defer="formData.revenue">
                                    <span class="input-group-text">đ</span>
                                </div>
                            @error('formData.revenue')
                                <div class="text-danger  mt-1">{{ $message }}</div>
                            @enderror
                            </div>

                            @if ($isEditing && auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value))
                            <div class="col-md-3"
                                 x-data="{
                                     ncc: {{ (int)($formData['ncc_payment'] ?? 0) }},
                                     revenue: {{ (int)($formData['revenue'] ?? 0) }},
                                     commission: {{ (int)($formData['commission'] ?? 0) }},
                                     get net() { return Math.max(0, this.revenue - this.commission - this.ncc); },
                                     fmt(n) { return new Intl.NumberFormat('vi-VN').format(n); }
                                 }">
                                <label class="form-label small fw-semibold">Chi Nhà Cung Cấp</label>
                                <div class="input-group">
                                    <input type="text" class="form-control money-input"
                                           wire:model.defer="formData.ncc_payment"
                                           x-on:input="ncc = parseInt($event.target.value.replace(/\D/g, '')) || 0"
                                           placeholder="0">
                                    <span class="input-group-text">đ</span>
                                </div>
                                <small class="text-muted">Thực nhận: <strong class="text-success" x-text="fmt(net) + 'đ'"></strong></small>
                            </div>
                            @elseif($selectedDoc && $selectedDoc->ncc_payment > 0)
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Chi Nhà Cung Cấp</label>
                                <p class="mb-0 text-danger fw-bold">{{ number_format($selectedDoc->ncc_payment) }}đ</p>
                                <small class="text-muted">Thực nhận: <strong class="text-success">{{ number_format($selectedDoc->revenue - $selectedDoc->commission - $selectedDoc->ncc_payment) }}đ</strong></small>
                            </div>
                            @endif

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">PT thanh toán</label>
                                @include('livewire.admin.contracts.partials.payment-method-checkboxes')
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Nguồn thông tin</label>
                                <select class="form-select" wire:model.defer="formData.info_source">
                                    <option value="">-- Chọn nguồn thông tin --</option>
                                    @foreach ($info_sources as $src)
                                        <option value="{{ $src }}">{{ $src }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold text-primary small text-uppercase" style="white-space:nowrap"><i class="fa-solid fa-calendar-days me-1"></i>Thời gian</span>
                                    <hr class="flex-fill my-0 border-primary border-opacity-25">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Ngày ký</label>
                                <input type="date" class="form-control" wire:model.defer="formData.signed_at">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Ngày hiệu lực</label>
                                <input type="date"
                                    class="form-control @error('formData.effective_at') is-invalid @enderror"
                                    wire:model.defer="formData.effective_at">
                                @error('formData.effective_at')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Ngày kết thúc</label>
                                <input type="date"
                                    class="form-control @error('formData.end_at') is-invalid @enderror"
                                    wire:model.defer="formData.end_at">
                                @error('formData.end_at')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Ngày xuất hóa đơn</label>
                                <input type="date" class="form-control" wire:model.defer="formData.submitted_at">
                            </div>

                            <div class="col-12 mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold text-primary small text-uppercase" style="white-space:nowrap"><i class="fa-solid fa-location-dot me-1"></i>Địa chỉ</span>
                                    <hr class="flex-fill my-0 border-primary border-opacity-25">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Địa chỉ thực hiện</label>
                                <textarea class="form-control @error('formData.execution_address') is-invalid @enderror" rows="3"
                                    wire:model.defer="formData.execution_address"></textarea>
                                @error('formData.execution_address')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Địa chỉ gửi thư</label>
                                <textarea class="form-control @error('formData.mailing_address') is-invalid @enderror" rows="3"
                                    wire:model.defer="formData.mailing_address"></textarea>
                                @error('formData.mailing_address')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Địa chỉ xuất HĐ</label>
                                <textarea class="form-control @error('formData.billing_address') is-invalid @enderror" rows="3"
                                    wire:model.defer="formData.billing_address"></textarea>
                                @error('formData.billing_address')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Tỉnh thành</label>
                                <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                    <button class="form-select text-start select-full" type="button"
                                        @click.prevent="open = !open">
                                        {{ $formData['province'] ?? 'Chọn tỉnh thành' }}
                                    </button>
                                    <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open"
                                        @click.away="open = false" x-cloak>
                                        <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                            placeholder="Tìm tỉnh thành..." @click.stop>
                                        <button class="dropdown-item @if(empty($formData['province'])) active @endif"
                                            type="button" x-show="!search.length"
                                            wire:click="$set('formData.province', '')" @click="open = false">-- Chọn tỉnh thành --</button>
                                        @foreach ($provinces as $p)
                                            <button
                                                class="dropdown-item @if(($formData['province'] ?? '') == $p) active @endif"
                                                type="button"
                                                x-show="{{ json_encode(mb_strtolower($p)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                                wire:click="$set('formData.province', '{{ $p }}')"
                                                @click="open = false">
                                                {{ $p }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold text-primary small text-uppercase" style="white-space:nowrap"><i class="fa-solid fa-tags me-1"></i>Trạng thái & Phân loại</span>
                                    <hr class="flex-fill my-0 border-primary border-opacity-25">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Tình trạng</label>
                                <select class="form-select" wire:model.defer="formData.status">
                                    <option value="">Chọn tình trạng</option>
                                    @foreach ($all_statuses as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Tình trạng tái ký</label>
                                <select class="form-select" wire:model.defer="formData.renewal_status">
                                    <option value="">Chọn tình trạng</option>
                                    @foreach ($renewal_status_options as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Tình trạng chứng từ</label>
                                <select class="form-select" wire:model.defer="formData.voucher_status">
                                    <option value="">Chọn tình trạng</option>
                                    @foreach ($voucher_status_options as $voucherStatus)
                                        <option value="{{ $voucherStatus }}">{{ $voucherStatus }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Hạng mục dịch vụ</label>
                                <select class="form-select" wire:model.defer="formData.loai_dich_vu">
                                    <option value="">Chọn hạng mục</option>
                                    @foreach ($loai_dich_vu_options as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="d-flex flex-wrap align-items-center gap-3 bg-light rounded-2 px-3 py-2">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="form_offset"
                                            wire:model.defer="formData.is_offset">
                                        <label class="form-check-label" for="form_offset">Có bù trừ</label>
                                    </div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="form_overdue"
                                            wire:model.defer="formData.is_overdue">
                                        <label class="form-check-label" for="form_overdue">Trễ hạn</label>
                                    </div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="form_wt_renewal"
                                            wire:model="formData.is_renewal">
                                        <label class="form-check-label" for="form_wt_renewal">Tái ký</label>
                                    </div>
                                </div>
                            </div>

                            @if ($formData['is_renewal'])
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">HĐ gốc (tái ký từ)</label>
                                    <select class="form-select" wire:model.defer="formData.parent_contract_id">
                                        <option value="">-- Chọn HĐ gốc --</option>
                                        @foreach ($parentContracts as $pc)
                                            <option value="{{ $pc->id }}">{{ $pc->so_hop_dong }} -
                                                {{ $pc->customer->name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @include('livewire.admin.contracts.partials.service-submission-fields')
                            <div class="col-12 mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold text-primary small text-uppercase" style="white-space:nowrap"><i class="fa-solid fa-pen me-1"></i>Ghi chú</span>
                                    <hr class="flex-fill my-0 border-primary border-opacity-25">
                                </div>
                            </div>
                            <div class="col-12">
                                <textarea class="form-control @error('formData.notes') is-invalid @enderror" rows="3"
                                    wire:model.defer="formData.notes" placeholder="Nhập ghi chú..."></textarea>
                                @error('formData.notes')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm"></span>
                            <i class="fa-solid fa-floppy-disk me-1"></i>Lưu
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
                    <h5 class="modal-title fw-bold modal-title-custom"><i class="fa-solid fa-user-check me-1"></i> Giao
                        việc hợp đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted  mb-3">Chọn nhân viên để giao việc (có thể chọn nhiều):</p>
                    <div class="list-group mh-320-scroll" >
                        @foreach ($assignable_users as $u)
                            <label class="list-group-item list-group-item-action d-flex gap-2">
                                <input class="form-check-input flex-shrink-0 mt-1" type="checkbox"
                                    value="{{ $u->id }}" wire:model="assignUserIds">
                                <span>{{ $u->name }}<small
                                        class="text-muted d-block">{{ $this->roleDisplayFromSlug($u->roles->first()?->name ?? '') }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Người ngoài công ty</label>
                        <input type="text" class="form-control" wire:model="assignExternal"
                            placeholder="Tên người ngoài (nếu có)">
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Hạn chót</label>
                        <input type="date" class="form-control" wire:model="assignDeadline">
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
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-sitemap me-2"></i>Cập nhật tiến độ hợp đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        wire:click="closeWorkflow"></button>
                </div>
                <div class="modal-body p-0">
                    @if ($workflowContractId)
                        <div wire:ignore wire:key="wf-panel-waste-{{ $workflowContractId }}">
                            <livewire:admin.contracts.contract-workflow-panel :contractType="'waste'" :contractId="$workflowContractId"
                                :key="'wf-modal-waste-' . $workflowContractId" />
                            </div>
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
