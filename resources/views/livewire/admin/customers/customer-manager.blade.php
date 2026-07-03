<div class="customer-directory">
    @section('title', 'Quản lý khách hàng')
    @section('page_title', 'Danh sách khách hàng')

    @push('styles')
    <style>
        .customer-directory {
            --customer-primary: #5b45f3;
            --customer-primary-soft: rgba(91, 69, 243, .09);
            --customer-success: #087f5b;
            --customer-success-soft: rgba(8, 127, 91, .1);
            --customer-warning: #b25b00;
            --customer-warning-soft: rgba(244, 147, 36, .12);
        }

        .customer-page-header h2 { letter-spacing: -.025em; }
        .customer-page-header .subtitle { max-width: 680px; }
        .customer-stat-card {
            min-height: 104px;
            border: 1px solid var(--bs-border-color);
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .customer-stat-card:hover {
            border-color: rgba(91, 69, 243, .28);
            box-shadow: 0 8px 24px rgba(38, 33, 74, .07);
        }
        .customer-stat-icon {
            width: 42px;
            height: 42px;
            display: inline-grid;
            place-items: center;
            border-radius: 12px;
            color: var(--customer-primary);
            background: var(--customer-primary-soft);
        }
        .customer-stat-icon.success {
            color: var(--customer-success);
            background: var(--customer-success-soft);
        }
        .customer-stat-icon.warning {
            color: var(--customer-warning);
            background: var(--customer-warning-soft);
        }
        .customer-filter-card {
            border: 1px solid var(--bs-border-color);
        }
        .customer-filter-card .form-label {
            font-size: .72rem;
            margin-bottom: .35rem;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: .045em;
        }
        .customer-search .input-group-text,
        .customer-search .form-control {
            background: var(--bs-body-bg);
        }
        .customer-group-row td {
            padding: .7rem 1rem !important;
            color: var(--customer-primary);
            background: var(--customer-primary-soft) !important;
            border-top: 1px solid rgba(91, 69, 243, .16);
            border-bottom: 1px solid rgba(91, 69, 243, .16);
        }
        .customer-directory .table-responsive > table {
            min-width: 1180px;
        }
        .customer-name {
            min-width: 220px;
            max-width: 310px;
            white-space: normal;
            line-height: 1.35;
        }
        .region-stack {
            min-width: 175px;
            white-space: normal;
        }
        .region-stack > span {
            display: block;
            margin-bottom: .2rem;
        }
        .service-performance {
            min-width: 310px;
            max-width: 440px;
            white-space: normal;
        }
        .service-line {
            display: grid;
            grid-template-columns: minmax(120px, 1fr) auto auto;
            align-items: center;
            gap: .4rem;
            padding: .32rem .45rem;
            margin-bottom: .3rem;
            border-radius: .45rem;
            background: var(--bs-tertiary-bg);
        }
        .service-line-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: .76rem;
            font-weight: 600;
        }
        .metric-badge {
            display: inline-block;
            min-width: 42px;
            padding: .23rem .38rem;
            border-radius: .35rem;
            font-size: .68rem;
            font-variant-numeric: tabular-nums;
            text-align: center;
            text-decoration: none;
            transition: opacity .15s ease, transform .15s ease;
        }
        a.metric-badge:hover {
            opacity: .85;
            transform: translateY(-1px);
        }
        .metric-quote {
            color: #3d2cc8;
            background: rgba(91, 69, 243, .12);
        }
        .metric-contract {
            color: #076648;
            background: rgba(8, 127, 91, .12);
        }
        .more-services summary {
            width: fit-content;
            color: var(--customer-primary);
            font-size: .74rem;
            cursor: pointer;
            list-style: none;
        }
        .more-services summary::-webkit-details-marker { display: none; }
        .customer-number {
            min-width: 48px;
            font-size: .86rem;
            font-variant-numeric: tabular-nums;
        }
        .customer-action {
            width: 40px;
            height: 40px;
            display: inline-grid;
            place-items: center;
        }
        .customer-mobile-card {
            border: 1px solid var(--bs-border-color);
            background: var(--bs-body-bg);
        }
        .customer-mobile-card .service-performance {
            min-width: 0;
            max-width: none;
        }
        .customer-loading {
            height: 3px;
            overflow: hidden;
            background: var(--customer-primary-soft);
        }
        .customer-loading::after {
            content: "";
            display: block;
            width: 45%;
            height: 100%;
            background: var(--customer-primary);
            animation: customer-loading 1s ease-in-out infinite;
        }
        @keyframes customer-loading {
            from { transform: translateX(-110%); }
            to { transform: translateX(245%); }
        }
        @media (prefers-reduced-motion: reduce) {
            .customer-stat-card { transition: none; }
            .customer-loading::after { animation: none; width: 100%; }
        }
        @media (max-width: 575.98px) {
            .customer-directory .btn-mobile-touch { min-height: 44px; }
            .service-line { grid-template-columns: minmax(0, 1fr) auto auto; }
        }
    </style>
    @endpush

    <div class="customer-page-header d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3 mt-2 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1">Danh sách khách hàng</h2>
            <p class="subtitle text-muted mb-0">
                Theo dõi khách hàng theo tỉnh/thành, phường/xã, khu công nghiệp và hiệu suất báo giá – hợp đồng.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('customers.edit')
            <button type="button" class="btn btn-outline-secondary btn-sm btn-mobile-touch"
                    wire:click="previewLegacyNormalization" wire:loading.attr="disabled"
                    wire:target="previewLegacyNormalization">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                Chuẩn hóa dữ liệu cũ
            </button>
            @endcan
            @can('customers.create')
            <button type="button" class="btn btn-primary btn-sm btn-mobile-touch" wire:click="openCreate">
                <i class="fa-solid fa-plus me-1"></i>Thêm khách hàng
            </button>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="customer-stat-card card h-100 shadow-none">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <span class="customer-stat-icon"><i class="fa-solid fa-users"></i></span>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ number_format($summary['customers']) }}</div>
                        <div class="small text-muted">Khách hàng</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="customer-stat-card card h-100 shadow-none">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <span class="customer-stat-icon"><i class="fa-solid fa-location-dot"></i></span>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ number_format($summary['groups']) }}</div>
                        <div class="small text-muted">
                            {{ match($groupBy) { 'ward' => 'Phường/xã', 'industrial_park' => 'KCN', default => 'Tỉnh/thành' } }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="customer-stat-card card h-100 shadow-none">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <span class="customer-stat-icon warning"><i class="fa-solid fa-file-lines"></i></span>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ number_format($summary['quotations']) }}</div>
                        <div class="small text-muted">Báo giá đã ra</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="customer-stat-card card h-100 shadow-none">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <span class="customer-stat-icon success"><i class="fa-solid fa-file-signature"></i></span>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ number_format($summary['contracts']) }}</div>
                        <div class="small text-muted">Hợp đồng</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="customer-filter-card card shadow-none mb-3">
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-4">
                    <label for="customer-search" class="form-label fw-bold">Tìm kiếm</label>
                    <div class="customer-search input-group">
                        <span class="input-group-text border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="customer-search" type="search"
                               class="form-control border-start-0 ps-0"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Tên, MST, người đại diện, địa chỉ...">
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label for="province-filter" class="form-label fw-bold">Tỉnh / thành mới</label>
                    <select id="province-filter" class="form-select" wire:model.live="provinceFilter">
                        <option value="">Tất cả tỉnh/thành</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province }}">{{ $province }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label for="ward-filter" class="form-label fw-bold">Phường / xã</label>
                    <select id="ward-filter" class="form-select" wire:model.live="wardFilter">
                        <option value="">Tất cả phường/xã</option>
                        @foreach($wards as $ward)
                            <option value="{{ $ward }}">{{ $ward }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label for="industrial-park-filter" class="form-label fw-bold">Khu công nghiệp</label>
                    <select id="industrial-park-filter" class="form-select" wire:model.live="industrialParkFilter">
                        <option value="">Tất cả KCN</option>
                        @foreach($industrialParks as $industrialPark)
                            <option value="{{ $industrialPark }}">{{ $industrialPark }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-5 col-lg-2">
                    <label for="group-filter" class="form-label fw-bold">Nhóm danh sách theo</label>
                    <select id="group-filter" class="form-select" wire:model.live="groupBy">
                        <option value="province">Tỉnh / thành</option>
                        <option value="ward">Phường / xã</option>
                        <option value="industrial_park">Khu công nghiệp</option>
                        <option value="none">Không nhóm</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-5">
                    <label for="service-quotation-filter" class="form-label fw-bold">Dịch vụ báo giá</label>
                    <select id="service-quotation-filter" class="form-select" wire:model.live="serviceQuotationFilter">
                        <option value="">Tất cả dịch vụ báo giá</option>
                        @foreach($serviceQuotationOptions as $service)
                            <option value="{{ $service }}">{{ $service }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-5">
                    <label for="service-contract-filter" class="form-label fw-bold">Dịch vụ hợp đồng</label>
                    <select id="service-contract-filter" class="form-select" wire:model.live="serviceContractFilter">
                        <option value="">Tất cả dịch vụ hợp đồng</option>
                        @foreach($serviceContractOptions as $service)
                            <option value="{{ $service }}">{{ $service }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-2 d-grid">
                    <button type="button" class="btn btn-outline-secondary btn-mobile-touch" wire:click="resetFilters">
                        <i class="fa-solid fa-rotate-left me-1"></i>Xóa bộ lọc
                    </button>
                </div>
            </div>
        </div>
        <div wire:loading class="customer-loading" wire:target="search,provinceFilter,wardFilter,industrialParkFilter,serviceQuotationFilter,serviceContractFilter,groupBy,resetFilters"></div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center ps-3" style="width: 62px">STT</th>
                        <th>Khách hàng</th>
                        <th>Khu vực</th>
                        <th>Dịch vụ & hiệu suất</th>
                        <th>Đại diện</th>
                        @canany(['customers.edit', 'customers.delete'])
                        <th class="text-end pe-3">Thao tác</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentGroup = null;
                        $columnCount = auth()->user()->canAny(['customers.edit', 'customers.delete']) ? 6 : 5;
                    @endphp
                    @forelse($customers as $customer)
                        @if($groupBy !== 'none' && $currentGroup !== $this->groupValue($customer))
                            @php($currentGroup = $this->groupValue($customer))
                            <tr class="customer-group-row">
                                <td colspan="{{ $columnCount }}">
                                    <div class="d-flex align-items-center gap-2 fw-bold">
                                        <i class="fa-solid {{ $groupBy === 'industrial_park' ? 'fa-industry' : 'fa-location-dot' }}"></i>
                                        {{ $currentGroup }}
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @php($breakdown = $this->serviceBreakdown($customer))
                        <tr wire:key="customer-{{ $customer->id }}">
                            <td class="text-center text-muted fw-semibold ps-3">
                                {{ ($customers->currentPage() - 1) * $customers->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <div class="customer-name">
                                    <a href="{{ route('app.customers.contracts', $customer) }}"
                                       class="fw-bold text-body text-decoration-none link-hover-primary">
                                        {{ $customer->name }}
                                    </a>
                                    @if($customer->tax_code)
                                        <div class="small text-muted mt-1">MST: {{ $customer->tax_code }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="region-stack small">
                                    <span class="fw-semibold">
                                        <i class="fa-solid fa-map-location-dot text-primary me-1"></i>
                                        {{ $customer->province ?: 'Chưa có tỉnh/thành' }}
                                    </span>
                                    @if($customer->ward)
                                        <span class="text-muted">{{ $customer->ward }}</span>
                                    @endif
                                    @if($customer->industrial_park)
                                        <span class="text-success fw-semibold">
                                            <i class="fa-solid fa-industry me-1"></i>{{ $customer->industrial_park }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="service-performance">
                                    @forelse(array_slice($breakdown, 0, 3) as $service)
                                        @include('livewire.admin.customers.partials.service-line', ['service' => $service, 'customer' => $customer])
                                    @empty
                                        <span class="small text-muted">Chưa phát sinh dịch vụ</span>
                                    @endforelse
                                    @if(count($breakdown) > 3)
                                        <details class="more-services mt-1">
                                            <summary>+ {{ count($breakdown) - 3 }} dịch vụ khác</summary>
                                            <div class="mt-1">
                                                @foreach(array_slice($breakdown, 3) as $service)
                                                    @include('livewire.admin.customers.partials.service-line', ['service' => $service, 'customer' => $customer])
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="small">{{ $customer->representative ?: '—' }}</div>
                            </td>
                            @canany(['customers.edit', 'customers.delete'])
                            <td class="text-end pe-3 text-nowrap">
                                @can('customers.edit')
                                <button type="button" class="customer-action btn btn-light text-primary rounded-circle"
                                        wire:click="openEdit({{ $customer->id }})"
                                        title="Sửa {{ $customer->name }}"
                                        aria-label="Sửa {{ $customer->name }}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                @endcan
                                @can('customers.delete')
                                <button type="button" class="customer-action btn btn-light text-danger rounded-circle"
                                        wire:click="delete({{ $customer->id }})"
                                        wire:confirm="Xác nhận xóa khách hàng này?"
                                        title="Xóa {{ $customer->name }}"
                                        aria-label="Xóa {{ $customer->name }}"
                                        @disabled($this->totalContractsCount($customer) > 0)>
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                @endcan
                            </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $columnCount }}" class="text-center py-5">
                                <i class="fa-solid fa-users-slash fa-2x text-muted mb-3"></i>
                                <div class="fw-semibold">Không tìm thấy khách hàng</div>
                                <div class="small text-muted mt-1">Thử thay đổi từ khóa hoặc xóa bộ lọc.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-md-none p-3">
            @php($mobileGroup = null)
            @forelse($customers as $customer)
                @if($groupBy !== 'none' && $mobileGroup !== $this->groupValue($customer))
                    @php($mobileGroup = $this->groupValue($customer))
                    <div class="small fw-bold text-primary text-uppercase mt-2 mb-2">
                        <i class="fa-solid fa-location-dot me-1"></i>{{ $mobileGroup }}
                    </div>
                @endif
                @php($breakdown = $this->serviceBreakdown($customer))
                <article wire:key="customer-mobile-{{ $customer->id }}" class="customer-mobile-card rounded-3 p-3 mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <a href="{{ route('app.customers.contracts', $customer) }}"
                               class="fw-bold text-body text-decoration-none">
                                {{ $customer->name }}
                            </a>
                            @if($customer->tax_code)
                                <div class="small text-muted mt-1">MST: {{ $customer->tax_code }}</div>
                            @endif
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <a href="{{ route('app.quotation-tracking.index', ['search' => $customer->name]) }}" class="metric-badge metric-quote text-decoration-none">{{ $customer->quotations_count }} BG</a>
                            <a href="{{ route('app.customers.contracts', $customer) }}" class="metric-badge metric-contract text-decoration-none">{{ $this->totalContractsCount($customer) }} HĐ</a>
                        </div>
                    </div>

                    <div class="small mt-3">
                        <div class="mb-1"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>{{ $customer->province ?: 'Chưa cập nhật' }}</div>
                        @if($customer->ward)
                            <div class="text-muted mb-1 ms-4">{{ $customer->ward }}</div>
                        @endif
                        @if($customer->industrial_park)
                            <div class="text-success fw-semibold ms-4"><i class="fa-solid fa-industry me-1"></i>{{ $customer->industrial_park }}</div>
                        @endif
                    </div>

                    <div class="service-performance mt-3">
                        @forelse(array_slice($breakdown, 0, 3) as $service)
                            @include('livewire.admin.customers.partials.service-line', ['service' => $service, 'customer' => $customer])
                        @empty
                            <span class="small text-muted">Chưa phát sinh dịch vụ</span>
                        @endforelse
                    </div>

                    @canany(['customers.edit', 'customers.delete'])
                    <div class="d-flex gap-2 border-top mt-3 pt-3">
                        @can('customers.edit')
                        <button type="button" class="btn btn-outline-primary btn-mobile-touch flex-fill"
                                wire:click="openEdit({{ $customer->id }})">
                            <i class="fa-solid fa-pen me-1"></i>Sửa
                        </button>
                        @endcan
                        @can('customers.delete')
                        <button type="button" class="btn btn-outline-danger btn-mobile-touch flex-fill"
                                wire:click="delete({{ $customer->id }})"
                                wire:confirm="Xác nhận xóa khách hàng này?"
                                @disabled($this->totalContractsCount($customer) > 0)>
                            <i class="fa-solid fa-trash me-1"></i>Xóa
                        </button>
                        @endcan
                    </div>
                    @endcanany
                </article>
            @empty
                <div class="text-center py-5 text-muted">Không tìm thấy khách hàng.</div>
            @endforelse
        </div>

        @if($customers->hasPages())
            <div class="card-footer border-top bg-body px-3 px-md-4 py-3">
                {{ $customers->links('livewire.admin.users.pagination') }}
            </div>
        @endif
    </div>

    <div wire:ignore.self class="modal fade" id="customerFormModal" tabindex="-1" aria-labelledby="customer-form-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 id="customer-form-title" class="modal-title fw-bold">
                            {{ $isEditing ? 'Cập nhật khách hàng' : 'Thêm khách hàng mới' }}
                        </h5>
                        <p class="small text-muted mb-0 mt-1">Địa chỉ sẽ được tự nhận diện tỉnh/thành, phường/xã và KCN.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="customer-name" class="form-label fw-bold">Tên khách hàng <span class="text-danger">*</span></label>
                                <input id="customer-name" type="text"
                                       class="form-control @error('formData.name') is-invalid @enderror"
                                       wire:model.defer="formData.name" autocomplete="organization">
                                @error('formData.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <label for="customer-tax-code" class="form-label fw-bold">Mã số thuế</label>
                                <input id="customer-tax-code" type="text"
                                       class="form-control @error('formData.tax_code') is-invalid @enderror"
                                       wire:model.defer="formData.tax_code">
                                @error('formData.tax_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="customer-representative" class="form-label fw-bold">Người đại diện</label>
                                <input id="customer-representative" type="text"
                                       class="form-control @error('formData.representative') is-invalid @enderror"
                                       wire:model.defer="formData.representative" autocomplete="name">
                                @error('formData.representative') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label for="customer-address" class="form-label fw-bold">Địa chỉ</label>
                                <textarea id="customer-address" rows="3"
                                          class="form-control @error('formData.address') is-invalid @enderror"
                                          wire:model="formData.address"
                                          placeholder="Ví dụ: KCN Long Hậu, Xã Long Hậu, Tỉnh Tây Ninh"></textarea>
                                <div class="form-text">
                                    Hệ thống chỉ gợi ý phần nhận diện được; bạn vẫn có thể sửa lại bên dưới.
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                        wire:click="detectAddressRegion"
                                        wire:loading.attr="disabled"
                                        wire:target="detectAddressRegion">
                                    <span wire:loading wire:target="detectAddressRegion" class="spinner-border spinner-border-sm me-1"></span>
                                    <i wire:loading.remove wire:target="detectAddressRegion" class="fa-solid fa-location-crosshairs me-1"></i>
                                    Nhận diện từ địa chỉ
                                </button>
                                @error('formData.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="customer-province" class="form-label fw-bold">Tỉnh / thành mới</label>
                                <select id="customer-province"
                                        class="form-select @error('formData.province') is-invalid @enderror"
                                        wire:model="formData.province">
                                    <option value="">Chọn tỉnh/thành</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province }}">{{ $province }}</option>
                                    @endforeach
                                </select>
                                @error('formData.province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="customer-ward" class="form-label fw-bold">Phường / xã / đặc khu</label>
                                <input id="customer-ward" type="text"
                                       class="form-control @error('formData.ward') is-invalid @enderror"
                                       wire:model="formData.ward" placeholder="Ví dụ: Phường Bình Hòa">
                                @error('formData.ward') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="customer-industrial-park" class="form-label fw-bold">Khu công nghiệp</label>
                                <input id="customer-industrial-park" type="text"
                                       class="form-control @error('formData.industrial_park') is-invalid @enderror"
                                       wire:model="formData.industrial_park" placeholder="Ví dụ: KCN Đông An">
                                @error('formData.industrial_park') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-body-tertiary">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            Lưu khách hàng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="customerNormalizationModal" tabindex="-1"
         aria-labelledby="customer-normalization-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 id="customer-normalization-title" class="modal-title fw-bold">Chuẩn hóa dữ liệu khách hàng cũ</h5>
                        <p class="small text-muted mb-0 mt-1">Xem trước — chưa thay đổi dữ liệu.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-4">
                    @if($normalizationPreview)
                        <div class="alert alert-primary border-0">
                            Tìm thấy <strong>{{ number_format($normalizationPreview['changed']) }}</strong>
                            / {{ number_format($normalizationPreview['total']) }} khách hàng có thể chuẩn hóa.
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="h5 fw-bold mb-1">{{ number_format($normalizationPreview['province_changed']) }}</div>
                                    <div class="small text-muted">Tỉnh cũ → tỉnh mới</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="h5 fw-bold mb-1">{{ number_format($normalizationPreview['ward_detected']) }}</div>
                                    <div class="small text-muted">Phường/xã nhận diện được</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="h5 fw-bold mb-1">{{ number_format($normalizationPreview['industrial_park_detected']) }}</div>
                                    <div class="small text-muted">KCN nhận diện được</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border border-warning rounded-3 p-3 h-100">
                                    <div class="h5 fw-bold text-warning mb-1">{{ number_format($normalizationPreview['needs_review']) }}</div>
                                    <div class="small text-muted">Cần rà soát phường/xã</div>
                                </div>
                            </div>
                        </div>
                        <p class="small text-muted mt-3 mb-0">
                            Công cụ không tự đoán phường/xã khi địa chỉ thiếu thông tin. Các ô đã có dữ liệu sẽ được giữ nguyên.
                        </p>
                    @endif
                </div>
                <div class="modal-footer bg-body-tertiary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Để sau</button>
                    <button type="button" class="btn btn-primary"
                            wire:click="normalizeLegacyCustomers"
                            wire:confirm="Áp dụng chuẩn hóa cho dữ liệu khách hàng cũ?"
                            wire:loading.attr="disabled"
                            wire:target="normalizeLegacyCustomers">
                        <span wire:loading wire:target="normalizeLegacyCustomers" class="spinner-border spinner-border-sm me-1"></span>
                        Áp dụng chuẩn hóa
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.addEventListener('openCustomerFormModal', () => {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('customerFormModal')).show();
        });

        window.addEventListener('closeCustomerFormModal', () => {
            bootstrap.Modal.getInstance(document.getElementById('customerFormModal'))?.hide();
        });

        window.addEventListener('openCustomerNormalizationModal', () => {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('customerNormalizationModal')).show();
        });

        window.addEventListener('closeCustomerNormalizationModal', () => {
            bootstrap.Modal.getInstance(document.getElementById('customerNormalizationModal'))?.hide();
        });
    </script>
    @endpush
</div>
