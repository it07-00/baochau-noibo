<div class="customer-directory">
    @section('title', 'Quản lý khách hàng')
    @section('page_title', 'Danh sách khách hàng')

    @push('styles')
    <style>
        .more-services summary {
            width: fit-content;
            color: var(--bs-primary);
            font-size: .74rem;
            cursor: pointer;
            list-style: none;
        }
        .more-services summary::-webkit-details-marker { display: none; }
        .customer-loading {
            height: 3px;
            overflow: hidden;
            background: var(--bs-primary-bg-subtle);
        }
        .customer-loading::after {
            content: "";
            display: block;
            width: 45%;
            height: 100%;
            background: var(--bs-primary);
            animation: customer-loading 1s ease-in-out infinite;
        }
        @keyframes customer-loading {
            from { transform: translateX(-110%); }
            to { transform: translateX(245%); }
        }
        @media (prefers-reduced-motion: reduce) {
            .customer-loading::after { animation: none; width: 100%; }
        }
    </style>
    @endpush

    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3 mt-2 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1 text-body" style="letter-spacing: -0.025em;">Danh sách khách hàng</h2>
            <p class="text-muted mb-0 small" style="max-width: 680px;">
                Theo dõi khách hàng theo tỉnh/thành, phường/xã, khu công nghiệp và hiệu suất báo giá – hợp đồng.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('customers.edit')
            <button type="button" class="btn btn-outline-secondary rounded-8px btn-mobile-touch"
                    wire:click="previewLegacyNormalization" wire:loading.attr="disabled"
                    wire:target="previewLegacyNormalization">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                Chuẩn hóa dữ liệu cũ
            </button>
            @endcan
            @can('customers.create')
            <button type="button" class="btn btn-primary rounded-8px btn-mobile-touch" wire:click="openCreate">
                <i class="fa-solid fa-plus me-1"></i>Thêm khách hàng
            </button>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-12px h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-body">{{ number_format($summary['customers']) }}</div>
                        <div class="small text-muted">Khách hàng</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-12px h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-body">{{ number_format($summary['groups']) }}</div>
                        <div class="small text-muted">
                            {{ match($groupBy) { 'ward' => 'Phường/xã', 'industrial_park' => 'KCN', default => 'Tỉnh/thành' } }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-12px h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-body">{{ number_format($summary['quotations']) }}</div>
                        <div class="small text-muted">Báo giá đã ra</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-12px h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-body">{{ number_format($summary['contracts']) }}</div>
                        <div class="small text-muted">Hợp đồng</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $hasAdvancedFilters = !empty($wardFilter) || !empty($staffFilter) || !empty($serviceQuotationFilter) || !empty($serviceContractFilter) || ($groupBy && $groupBy !== 'province');
    @endphp

    <div class="card border border-secondary-subtle bg-body shadow-sm rounded-12px mb-4"
         x-data="{ showAdvanced: @json($hasAdvancedFilters) }">
        <div class="card-body p-3 p-md-4">
            {{-- Primary Filter Row --}}
            <div class="row g-2.5 g-md-3 align-items-end">
                <div class="col-12 col-md-5 col-lg-5">
                    <label for="customer-search" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0 text-body-secondary border-secondary-subtle">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="customer-search" type="search"
                               class="form-control border-start-0 ps-0 border-secondary-subtle"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Tên, MST, người đại diện, địa chỉ...">
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-3">
                    <label for="province-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tỉnh / thành</label>
                    <select id="province-filter" class="form-select border-secondary-subtle" wire:model.live="provinceFilter">
                        <option value="">Tất cả tỉnh/thành</option>
                        @foreach($filterProvinces as $province)
                            <option value="{{ $province }}">{{ $province }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-4 col-lg-4">
                    <label for="industrial-park-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Khu công nghiệp</label>
                    <div class="d-flex gap-2">
                        <select id="industrial-park-filter" class="form-select border-secondary-subtle flex-grow-1" wire:model.live="industrialParkFilter">
                            <option value="">Tất cả KCN</option>
                            @foreach($industrialParks as $industrialPark)
                                <option value="{{ $industrialPark }}">{{ $industrialPark }}</option>
                            @endforeach
                        </select>
                        <button type="button"
                                class="btn btn-outline-secondary border-secondary-subtle d-inline-flex align-items-center gap-1 text-nowrap"
                                :class="{ 'active bg-secondary bg-opacity-10 text-primary': showAdvanced }"
                                @click="showAdvanced = !showAdvanced"
                                title="Bộ lọc nâng cao">
                            <i class="fa-solid fa-filter"></i>
                            <span class="d-none d-sm-inline">Lọc</span>
                            @if($hasAdvancedFilters)
                                <span class="badge bg-primary rounded-circle p-1 ms-1"></span>
                            @endif
                        </button>
                        <button type="button" class="btn btn-outline-primary border-secondary-subtle text-nowrap px-2.5"
                                wire:click="resetFilters" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Collapsible Advanced Filters Section --}}
            <div x-show="showAdvanced" x-collapse x-cloak class="pt-3 mt-3 border-top border-light-subtle">
                <div class="row g-3">
                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="ward-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Phường / xã</label>
                        <select id="ward-filter" class="form-select border-secondary-subtle" wire:model.live="wardFilter">
                            <option value="">Tất cả phường/xã</option>
                            @foreach($wards as $ward)
                                <option value="{{ $ward }}">{{ $ward }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="group-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Nhóm danh sách theo</label>
                        <select id="group-filter" class="form-select border-secondary-subtle" wire:model.live="groupBy">
                            <option value="province">Tỉnh / thành</option>
                            <option value="ward">Phường / xã</option>
                            <option value="industrial_park">Khu công nghiệp</option>
                            <option value="none">Không nhóm</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="staff-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Nhân viên phụ trách</label>
                        <select id="staff-filter" class="form-select border-secondary-subtle" wire:model.live="staffFilter">
                            <option value="">Tất cả nhân viên</option>
                            @foreach($staffOptions as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Dịch vụ báo giá</label>
                        <div class="dropdown" x-data="{ open: false }" @click.outside="open = false">
                            <button class="form-select text-start d-flex justify-content-between align-items-center dropdown-toggle border-secondary-subtle" type="button" @click="open = !open">
                                <span class="text-truncate me-2">
                                    @php
                                        $selectedQuotationList = is_array($serviceQuotationFilter)
                                            ? $serviceQuotationFilter
                                            : (empty($serviceQuotationFilter) ? [] : [$serviceQuotationFilter]);
                                    @endphp
                                    @if(empty($selectedQuotationList))
                                        Tất cả dịch vụ báo giá
                                    @elseif(count($selectedQuotationList) === 1)
                                        {{ $selectedQuotationList[0] }}
                                    @else
                                        {{ count($selectedQuotationList) }} dịch vụ được chọn
                                    @endif
                                </span>
                            </button>
                            <div class="dropdown-menu w-100 p-2 shadow-sm border border-secondary-subtle" :class="{ 'show': open }" style="max-height: 250px; overflow-y: auto; margin-top: 2px; z-index: 1050;">
                                @foreach($serviceQuotationOptions as $index => $service)
                                    <div class="form-check py-1">
                                        <input class="form-check-input" type="checkbox" value="{{ $service }}" id="service-quote-{{ $index }}" wire:model.live="serviceQuotationFilter">
                                        <label class="form-check-label text-body w-100 cursor-pointer" for="service-quote-{{ $index }}">
                                            {{ $service }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-2">
                        <label for="service-contract-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Dịch vụ hợp đồng</label>
                        <select id="service-contract-filter" class="form-select border-secondary-subtle" wire:model.live="serviceContractFilter">
                            <option value="">Tất cả dịch vụ hợp đồng</option>
                            @foreach($serviceContractOptions as $service)
                                <option value="{{ $service }}">{{ $service }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div wire:loading class="customer-loading" wire:target="search,provinceFilter,wardFilter,industrialParkFilter,staffFilter,serviceQuotationFilter,serviceContractFilter,groupBy,resetFilters"></div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden rounded-12px">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-uppercase text-secondary" style="font-size: 0.75rem; border-bottom: 1px solid var(--bs-border-color-translucent);">
                    <tr>
                        <th class="text-center px-3 py-3 text-nowrap" style="width: 80px">STT</th>
                        <th class="px-4 py-3 text-nowrap">Khách hàng</th>
                        <th class="px-4 py-3 text-nowrap">Khu vực</th>
                        <th class="px-4 py-3 text-nowrap">Dịch vụ & hiệu suất</th>
                        @canany(['customers.edit', 'customers.delete'])
                        <th class="text-end pe-4 py-3 text-nowrap" style="width: 120px;">Thao tác</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody class="border-0">
                    @php
                        $currentGroup = null;
                        $columnCount = auth()->user()->canAny(['customers.edit', 'customers.delete']) ? 5 : 4;
                    @endphp
                    @forelse($customers as $customer)
                        @if($groupBy !== 'none' && $currentGroup !== $this->groupValue($customer))
                            @php($currentGroup = $this->groupValue($customer))
                            <tr class="bg-body-tertiary border-top border-bottom border-light-subtle">
                                <td colspan="{{ $columnCount }}" class="px-4 py-2.5">
                                    <div class="d-flex align-items-center gap-2 fw-bold text-primary" style="font-size: 0.85rem;">
                                        <i class="fa-solid {{ $groupBy === 'industrial_park' ? 'fa-industry' : 'fa-location-dot' }}"></i>
                                        {{ $currentGroup }}
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @php($breakdown = $this->serviceBreakdown($customer))
                        <tr wire:key="customer-{{ $customer->id }}" style="border-bottom: 1px solid var(--bs-border-color-translucent);">
                            <td class="text-center text-muted fw-semibold ps-4">
                                {{ ($customers->currentPage() - 1) * $customers->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-4">
                                <div style="min-width: 220px; max-width: 320px; white-space: normal; line-height: 1.4;">
                                    <a href="{{ route('app.customers.contracts', $customer) }}"
                                       class="fw-bold text-body text-decoration-none link-primary">
                                        {{ $customer->name }}
                                    </a>
                                    @if($customer->tax_code)
                                        <div class="small text-muted mt-1">MST: {{ $customer->tax_code }}</div>
                                    @endif
                                    @if($customer->representative)
                                        <div class="small text-muted mt-1">Đại diện: {{ $customer->representative }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="d-flex flex-wrap gap-1" style="min-width: 175px;">
                                    @if($customer->province)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 fs-72">
                                            <i class="fa-solid fa-location-dot me-1"></i>{{ $customer->province }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1" style="font-size: 0.72rem;">Chưa có tỉnh/thành</span>
                                    @endif
                                    @if($customer->ward)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1" style="font-size: 0.72rem;">{{ $customer->ward }}</span>
                                    @endif
                                    @if($customer->industrial_park)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 fs-72">
                                            <i class="fa-solid fa-industry me-1"></i>{{ $customer->industrial_park }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4">
                                <div style="min-width: 310px; max-width: 440px; white-space: normal;">
                                    @forelse(array_slice($breakdown, 0, 3) as $service)
                                        @include('livewire.admin.customers.partials.service-line', ['service' => $service, 'customer' => $customer])
                                    @empty
                                        <span class="small text-muted">Chưa phát sinh dịch vụ</span>
                                    @endforelse
                                    @if(count($breakdown) > 3)
                                        <details class="more-services mt-1">
                                            <summary class="fw-semibold">+ {{ count($breakdown) - 3 }} dịch vụ khác</summary>
                                            <div class="mt-2">
                                                @foreach(array_slice($breakdown, 3) as $service)
                                                    @include('livewire.admin.customers.partials.service-line', ['service' => $service, 'customer' => $customer])
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </td>
                            @canany(['customers.edit', 'customers.delete'])
                            <td class="text-end pe-4 text-nowrap">
                                @can('customers.edit')
                                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle rounded-8px px-2 py-1.5 me-1"
                                        wire:click="openEdit({{ $customer->id }})"
                                        title="Sửa {{ $customer->name }}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                @endcan
                                @can('customers.delete')
                                <button type="button" class="btn btn-sm btn-outline-danger border-light-subtle rounded-8px px-2 py-1.5"
                                        wire:click="delete({{ $customer->id }})"
                                        wire:confirm="Xác nhận xóa khách hàng này?"
                                        title="Xóa {{ $customer->name }}"
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
                                <i class="fa-solid fa-users-slash fa-3x text-muted mb-3 opacity-40"></i>
                                <div class="fw-semibold text-muted">Không tìm thấy khách hàng</div>
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
                    <div class="small fw-bold text-primary text-uppercase mt-3 mb-2">
                        <i class="fa-solid fa-location-dot me-1"></i>{{ $mobileGroup }}
                    </div>
                @endif
                @php($breakdown = $this->serviceBreakdown($customer))
                <article wire:key="customer-mobile-{{ $customer->id }}" class="card border-0 shadow-sm rounded-12px p-3 mb-3 bg-body">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <a href="{{ route('app.customers.contracts', $customer) }}"
                               class="fw-bold text-body text-decoration-none link-primary">
                                {{ $customer->name }}
                            </a>
                            @if($customer->tax_code)
                                <div class="small text-muted mt-1">MST: {{ $customer->tax_code }}</div>
                            @endif
                            @if($customer->representative)
                                <div class="small text-muted mt-1">Đại diện: {{ $customer->representative }}</div>
                            @endif
                        </div>
                        <div class="d-flex gap-1.5 flex-shrink-0">
                            <a href="{{ route('app.quotation-tracking.index', ['search' => $customer->name]) }}"
                               class="badge bg-primary bg-opacity-10 text-primary text-decoration-none px-2 py-1.5"
                               style="font-size: 0.7rem;">
                                {{ $customer->quotations_count }} BG
                            </a>
                            <a href="{{ route('app.customers.contracts', $customer) }}"
                               class="badge bg-success bg-opacity-10 text-success text-decoration-none px-2 py-1.5"
                               style="font-size: 0.7rem;">
                                {{ $this->totalContractsCount($customer) }} HĐ
                            </a>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-1 mt-3">
                        @if($customer->province)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 fs-72">
                                <i class="fa-solid fa-location-dot me-1"></i>{{ $customer->province }}
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 fs-72">Chưa cập nhật</span>
                        @endif
                        @if($customer->ward)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 fs-72">{{ $customer->ward }}</span>
                        @endif
                        @if($customer->industrial_park)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 fs-72">
                                <i class="fa-solid fa-industry me-1"></i>{{ $customer->industrial_park }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-3">
                        @forelse(array_slice($breakdown, 0, 3) as $service)
                            @include('livewire.admin.customers.partials.service-line', ['service' => $service, 'customer' => $customer])
                        @empty
                            <span class="small text-muted">Chưa phát sinh dịch vụ</span>
                        @endforelse
                    </div>

                    @canany(['customers.edit', 'customers.delete'])
                    <div class="d-flex gap-2 border-top mt-3 pt-3">
                        @can('customers.edit')
                        <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle rounded-8px flex-fill py-2"
                                wire:click="openEdit({{ $customer->id }})">
                            <i class="fa-solid fa-pen me-1"></i>Sửa
                        </button>
                        @endcan
                        @can('customers.delete')
                        <button type="button" class="btn btn-sm btn-outline-danger border-light-subtle rounded-8px flex-fill py-2"
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
                <div class="text-center py-5 text-muted card border-0 shadow-sm rounded-12px">Không tìm thấy khách hàng.</div>
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
                <div class="modal-header border-bottom-0 bg-transparent p-4 pb-3">
                    <div>
                        <h5 id="customer-form-title" class="modal-title fw-bold text-body">
                            {{ $isEditing ? 'Cập nhật khách hàng' : 'Thêm khách hàng mới' }}
                        </h5>
                        <p class="small text-muted mb-0 mt-1">Địa chỉ sẽ được tự nhận diện tỉnh/thành, phường/xã và KCN.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 pt-0">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="customer-name" class="form-label fw-bold text-body">Tên khách hàng <span class="text-danger">*</span></label>
                                <input id="customer-name" type="text"
                                       class="form-control border-light-subtle @error('formData.name') is-invalid @enderror"
                                       wire:model.defer="formData.name" autocomplete="organization">
                                @error('formData.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <label for="customer-tax-code" class="form-label fw-bold text-body">Mã số thuế</label>
                                <input id="customer-tax-code" type="text"
                                       class="form-control border-light-subtle @error('formData.tax_code') is-invalid @enderror"
                                       wire:model.defer="formData.tax_code">
                                @error('formData.tax_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="customer-representative" class="form-label fw-bold text-body">Người đại diện</label>
                                <input id="customer-representative" type="text"
                                       class="form-control border-light-subtle @error('formData.representative') is-invalid @enderror"
                                       wire:model.defer="formData.representative" autocomplete="name">
                                @error('formData.representative') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label for="customer-address" class="form-label fw-bold text-body">Địa chỉ</label>
                                <textarea id="customer-address" rows="3"
                                          class="form-control border-light-subtle @error('formData.address') is-invalid @enderror"
                                          wire:model="formData.address"
                                          placeholder="Ví dụ: KCN Long Hậu, Xã Long Hậu, Tỉnh Tây Ninh"></textarea>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-8px mt-2"
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
                                <label for="customer-province" class="form-label fw-bold text-body">Tỉnh / thành mới</label>
                                <select id="customer-province"
                                        class="form-select border-light-subtle @error('formData.province') is-invalid @enderror"
                                        wire:model="formData.province">
                                    <option value="">Chọn tỉnh/thành</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province }}">{{ $province }}</option>
                                    @endforeach
                                </select>
                                @error('formData.province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="customer-ward" class="form-label fw-bold text-body">Phường / xã / đặc khu</label>
                                <input id="customer-ward" type="text"
                                       class="form-control border-light-subtle @error('formData.ward') is-invalid @enderror"
                                       wire:model="formData.ward" placeholder="Ví dụ: Phường Bình Hòa">
                                @error('formData.ward') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="customer-industrial-park" class="form-label fw-bold text-body">Khu công nghiệp</label>
                                <input id="customer-industrial-park" type="text"
                                       class="form-control border-light-subtle @error('formData.industrial_park') is-invalid @enderror"
                                       wire:model="formData.industrial_park" placeholder="Ví dụ: KCN Đông An">
                                @error('formData.industrial_park') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 bg-transparent p-4 pt-0 justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary rounded-8px px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-8px px-4" wire:loading.attr="disabled" wire:target="save">
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
                <div class="modal-header border-bottom-0 bg-transparent p-4 pb-3">
                    <div>
                        <h5 id="customer-normalization-title" class="modal-title fw-bold text-body">Chuẩn hóa dữ liệu khách hàng cũ</h5>
                        <p class="small text-muted mb-0 mt-1">Xem trước — chưa thay đổi dữ liệu.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    @if($normalizationPreview)
                        <div class="alert alert-primary border-0 rounded-12px">
                            Tìm thấy <strong>{{ number_format($normalizationPreview['changed']) }}</strong>
                            / {{ number_format($normalizationPreview['total']) }} khách hàng có thể chuẩn hóa.
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="border border-light-subtle bg-body-tertiary rounded-12px p-3 h-100">
                                    <div class="h5 fw-bold mb-1 text-body">{{ number_format($normalizationPreview['province_changed']) }}</div>
                                    <div class="small text-muted">Tỉnh cũ → tỉnh mới</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border border-light-subtle bg-body-tertiary rounded-12px p-3 h-100">
                                    <div class="h5 fw-bold mb-1 text-body">{{ number_format($normalizationPreview['ward_detected']) }}</div>
                                    <div class="small text-muted">Phường/xã nhận diện được</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border border-light-subtle bg-body-tertiary rounded-12px p-3 h-100">
                                    <div class="h5 fw-bold mb-1 text-body">{{ number_format($normalizationPreview['industrial_park_detected']) }}</div>
                                    <div class="small text-muted">KCN nhận diện được</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border border-warning bg-warning bg-opacity-10 rounded-12px p-3 h-100">
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
                <div class="modal-footer border-top-0 bg-transparent p-4 pt-0 justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary rounded-8px px-4" data-bs-dismiss="modal">Để sau</button>
                    <button type="button" class="btn btn-primary rounded-8px px-4"
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
