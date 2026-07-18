<div>
    @section('title', 'Dòng tiền')
    @section('page_title', 'Dòng tiền')

    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3 mt-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
                <h2 class="h4 fw-bold text-body mb-0">Tài chính dòng tiền</h2>
            </div>
            <p class="text-muted small mb-0">
                Theo dõi doanh số, hoa hồng, chi nhà thầu phụ và số thực nhận theo từng hợp đồng.
            </p>
        </div>
        @can('cash-flow.export')
            <button wire:click="exportExcel" class="btn btn-success rounded-8px text-nowrap"
                wire:loading.attr="disabled" wire:target="exportExcel">
                <span wire:loading wire:target="exportExcel" class="spinner-border spinner-border-sm me-1"></span>
                <i class="fa-solid fa-file-excel me-1" wire:loading.remove wire:target="exportExcel"></i>
                Xuất Excel
            </button>
        @endcan
    </div>

    {{-- Bộ lọc --}}
    <div class="card border border-secondary-subtle bg-body shadow-sm rounded-12px mb-4 overflow-hidden">
        <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                    <i class="fa-solid fa-filter"></i>
                </span>
                <div>
                    <h3 class="h6 fw-bold text-body mb-0">Bộ lọc dữ liệu</h3>
                    <small class="text-muted">Số liệu đang hiển thị cho {{ $periodLabel }}</small>
                </div>
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="cash-flow-year" class="form-label small fw-semibold text-body mb-1">Năm</label>
                    <select id="cash-flow-year" wire:model.live="filterYear" class="form-select">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="cash-flow-period" class="form-label small fw-semibold text-body mb-1">Kỳ báo cáo</label>
                    <select id="cash-flow-period" wire:model.live="filterPeriodType" class="form-select">
                        <option value="year">Cả năm</option>
                        <option value="quarter">Theo quý</option>
                        <option value="month">Theo tháng</option>
                    </select>
                </div>
                @if($filterPeriodType === 'quarter')
                    <div class="col-6 col-md-3 col-xl-2">
                        <label for="cash-flow-quarter" class="form-label small fw-semibold text-body mb-1">Quý</label>
                        <select id="cash-flow-quarter" wire:model.live="filterQuarter" class="form-select">
                            <option value="0">-- Chọn quý --</option>
                            @foreach([1, 2, 3, 4] as $q)
                                <option value="{{ $q }}">Quý {{ $q }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($filterPeriodType === 'month')
                    <div class="col-6 col-md-3 col-xl-2">
                        <label for="cash-flow-month" class="form-label small fw-semibold text-body mb-1">Tháng</label>
                        <select id="cash-flow-month" wire:model.live="filterMonth" class="form-select">
                            <option value="0">-- Chọn tháng --</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">Tháng {{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="cash-flow-contract-type" class="form-label small fw-semibold text-body mb-1">Loại hợp đồng</label>
                    <select id="cash-flow-contract-type" wire:model.live="filterContractType" class="form-select">
                        <option value="all">Tất cả loại hợp đồng</option>
                        @foreach($contractTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="cash-flow-service" class="form-label small fw-semibold text-body mb-1">Hạng mục dịch vụ</label>
                    <select id="cash-flow-service" wire:model.live="filterServiceCategory" class="form-select">
                        <option value="all">Tất cả hạng mục</option>
                        @foreach($serviceCategoryOptions as $serviceCategory)
                            <option value="{{ $serviceCategory }}">{{ $serviceCategory }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 col-xl-3">
                    <label for="cash-flow-handler" class="form-label small fw-semibold text-body mb-1">Đơn vị thực hiện</label>
                    <select id="cash-flow-handler" wire:model.live="filterHandlerType" class="form-select">
                        <option value="all">Tất cả</option>
                        <option value="tdx">TĐX (Trái Đất Xanh)</option>
                        <option value="non_tdx">BC (Bảo Châu)</option>
                    </select>
                </div>
                <div class="col-12 col-md-8 col-xl">
                    <label for="cash-flow-search" class="form-label small fw-semibold text-body mb-1">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary text-body-secondary border-secondary-subtle">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="cash-flow-search" type="search" wire:model.live.debounce.300ms="search"
                            class="form-control border-secondary-subtle"
                            placeholder="Tên công ty, số HĐ Bảo Châu, nhà thầu phụ...">
                        @if($search !== '')
                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('search', '')"
                                title="Xóa nội dung tìm kiếm" aria-label="Xóa nội dung tìm kiếm">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4 summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-12px h-100">
                <div class="card-body d-flex align-items-start gap-3 p-3 p-lg-4">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-3 flex-shrink-0">
                        <i class="fa-solid fa-coins fs-5"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="small text-muted mb-1">Tổng doanh số</div>
                        <div class="h5 fw-bold text-body mb-1 text-break">{{ number_format($totals['revenue']) }}đ</div>
                        <small class="text-muted">{{ number_format($totals['count']) }} hợp đồng</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-12px h-100">
                <div class="card-body d-flex align-items-start gap-3 p-3 p-lg-4">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning p-3 flex-shrink-0">
                        <i class="fa-solid fa-hand-holding-dollar fs-5"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="small text-muted mb-1">Tổng hoa hồng</div>
                        <div class="h5 fw-bold text-body mb-1 text-break">{{ number_format($totals['commission']) }}đ</div>
                        <small class="text-muted">Chi phí kinh doanh</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-12px h-100">
                <div class="card-body d-flex align-items-start gap-3 p-3 p-lg-4">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger p-3 flex-shrink-0">
                        <i class="fa-solid fa-building-circle-arrow-right fs-5"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="small text-muted mb-1">Tổng chi nhà thầu phụ</div>
                        <div class="h5 fw-bold text-body mb-1 text-break">{{ number_format($totals['ncc_payment']) }}đ</div>
                        <small class="text-muted">Chi phí thực hiện</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-12px h-100">
                <div class="card-body d-flex align-items-start gap-3 p-3 p-lg-4">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success p-3 flex-shrink-0">
                        <i class="fa-solid fa-wallet fs-5"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="small text-muted mb-1">Tổng thực nhận</div>
                        <div class="h5 fw-bold {{ $totals['net_received'] >= 0 ? 'text-success' : 'text-danger' }} mb-1 text-break">
                            {{ number_format($totals['net_received']) }}đ
                        </div>
                        <small class="text-muted">Doanh số trừ chi nhà thầu phụ</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng chi tiết --}}
    <div class="card border-0 shadow-sm rounded-12px overflow-hidden">
        <div class="card-header bg-body p-3 p-lg-4 border-bottom border-secondary-subtle d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                    <i class="fa-solid fa-table-list"></i>
                </span>
                <div>
                    <h3 class="h6 mb-0 fw-bold text-body">Chi tiết dòng tiền</h3>
                    <small class="text-muted">{{ $periodLabel }}</small>
                </div>
            </div>
            <span class="d-inline-flex align-items-center gap-1 rounded-3 bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 small fw-semibold">
                <i class="fa-solid fa-file-signature" aria-hidden="true"></i>
                {{ number_format($totals['count']) }} hợp đồng
            </span>
        </div>
        <div class="card-body p-0">
            <div wire:loading.flex
                wire:target="filterYear,filterPeriodType,filterQuarter,filterMonth,filterContractType,filterServiceCategory,filterHandlerType,search"
                class="align-items-center gap-2 px-3 px-lg-4 py-2 bg-primary bg-opacity-10 text-primary small fw-semibold border-bottom border-primary-subtle">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Đang cập nhật số liệu...
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary text-uppercase text-secondary small">
                        <tr>
                            <th class="text-center py-3 px-3">STT</th>
                            <th class="py-3 mnw-300px">Hợp đồng</th>
                            <th class="py-3 mnw-300px">Khách hàng</th>
                            <th class="text-end py-3 text-nowrap">Giá trị chưa VAT</th>
                            <th class="text-end py-3 text-nowrap">Doanh số</th>
                            <th class="text-end py-3 text-nowrap">Hoa hồng</th>
                            <th class="text-end py-3 text-nowrap">Chi nhà thầu phụ</th>
                            <th class="text-end py-3 text-nowrap">Thực nhận</th>
                            <th class="text-center py-3 mnw-220px">Thanh toán</th>
                            @if($canManageNccPayment)
                                <th class="text-center py-3 mnw-120px">Hành động</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                            <tr>
                                <td class="text-center text-muted px-3 py-3">
                                    {{ ($rows->currentPage() - 1) * $rows->perPage() + $i + 1 }}</td>
                                <td class="py-3 mnw-300px">
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex flex-column gap-1">
                                            <span
                                                class="d-inline-block {{ $row['type_badge_class'] ?? 'bg-light text-dark border' }} align-self-start rounded-2 text-start text-wrap lh-sm px-2 py-1 small fw-semibold">{{ $row['type'] }}</span>
                                            @if(!empty($row['service_category']))
                                                <small class="text-muted lh-sm mt-1">{{ $row['service_category'] }}</small>
                                            @endif
                                        </div>
                                        @if($canEditBaoChauInvoice)
                                            <label class="form-label small text-muted mb-0">Số HĐ Bảo Châu</label>
                                            <input type="text" class="form-control form-control-sm fw-semibold text-center small"
                                                value="{{ $row['shd_bc'] }}"
                                                placeholder="Nhập số HĐ BC"
                                                wire:change="updateBaoChauInvoiceNumber('{{ $row['source_key'] }}', {{ $row['id'] }}, $event.target.value)">
                                            @if($this->baoChauMessageFor($this->stateKey($row['source_key'], $row['id'])))
                                                <small
                                                    class="{{ $this->baoChauMessageFor($this->stateKey($row['source_key'], $row['id']))['type'] === 'error' ? 'text-danger' : 'text-success' }}">{{ $this->baoChauMessageFor($this->stateKey($row['source_key'], $row['id']))['text'] }}</small>
                                            @endif
                                        @else
                                            <div>
                                                <small class="text-muted d-block">Số HĐ Bảo Châu</small>
                                                <span class="fw-semibold">{{ $row['shd_bc'] ?: '—' }}</span>
                                            </div>
                                        @endif
                                        @if(!empty($row['handler']) || !empty($row['shd_cxl']))
                                            <div class="border-top pt-2">
                                                @if($this->isTdxRow($row))
                                                    <small class="text-muted d-block">Nhà thầu phụ <span
                                                            class="badge bg-light text-dark border">TĐX</span></small>
                                                @else
                                                    <small class="text-muted d-block">Nhà thầu phụ</small>
                                                @endif
                                                <div class="fw-semibold text-dark">{{ $row['handler'] ?: '—' }}</div>
                                                @if($canEditBaoChauInvoice)
                                                    <label
                                                        class="form-label small text-muted mb-0 mt-1">{{ $this->isTdxRow($row) ? 'Số HĐ TĐX' : 'Số HĐ/HĐ NTP' }}</label>
                                                    <input type="text" class="form-control form-control-sm fw-semibold text-center small"
                                                        value="{{ $row['shd_cxl'] }}"
                                                        placeholder="{{ $this->isTdxRow($row) ? 'Nhập số HĐ TĐX' : 'Nhập số HĐ NTP' }}"
                                                        wire:change="updateSubcontractorInvoiceNumber('{{ $row['source_key'] }}', {{ $row['id'] }}, $event.target.value)">
                                                    @if($this->subcontractorMessageFor($this->stateKey($row['source_key'], $row['id'])))
                                                        <small
                                                            class="{{ $this->subcontractorMessageFor($this->stateKey($row['source_key'], $row['id']))['type'] === 'error' ? 'text-danger' : 'text-success' }}">{{ $this->subcontractorMessageFor($this->stateKey($row['source_key'], $row['id']))['text'] }}</small>
                                                    @endif
                                                @else
                                                    <small
                                                        class="text-muted">{{ $this->isTdxRow($row) ? 'Số HĐ TĐX' : 'Số HĐ NTP' }}:
                                                        <span
                                                            class="fw-semibold text-dark">{{ $row['shd_cxl'] ?: '—' }}</span></small>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 mnw-300px">
                                    @if(!empty($row['customer_slug']))
                                        <a href="{{ route('app.customers.contracts', ['customer' => $row['customer_slug']]) }}"
                                            class="text-decoration-none fw-bold text-primary">
                                            {{ $row['customer'] }}
                                        </a>
                                    @else
                                        <span class="fw-bold text-primary">{{ $row['customer'] ?? '—' }}</span>
                                    @endif
                                    <div class="d-flex flex-wrap gap-3 text-muted small mt-1">
                                        <span><i class="fa-regular fa-user me-1"></i>{{ $row['staff'] ?? '—' }}</span>
                                        <span><i class="fa-regular fa-calendar me-1"></i>{{ $row['signed_at'] ?? '—' }}</span>
                                    </div>
                                    @if($canEditInvoiceDate)
                                        <div class="mt-2">
                                            <label class="form-label small text-muted mb-0">Ngày xuất hóa đơn</label>
                                            <input type="date" class="form-control form-control-sm"
                                                wire:model.live="invoiceDates.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                wire:change="updateInvoiceDate('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                        </div>
                                    @else
                                        @if(!empty($row['submitted_at']))
                                            <div class="mt-1 small">
                                                <span class="text-muted">Xuất HĐ: </span>
                                                <span class="fw-semibold">{{ $row['submitted_at'] }}</span>
                                            </div>
                                        @endif
                                    @endif
                                    @if(!empty($row['contract_note']))
                                        <div class="text-muted small mt-2 text-break">
                                            <span class="fw-semibold text-dark">Ghi chú HĐ:</span> {{ $row['contract_note'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap py-3">
                                    {{ $row['value_without_vat'] > 0 ? number_format($row['value_without_vat']) : '—' }}
                                </td>
                                <td class="text-end text-nowrap py-3 fw-semibold text-body">
                                    {{ $row['revenue'] > 0 ? number_format($row['revenue']) : '—' }}</td>
                                <td class="text-end text-warning text-nowrap py-3">
                                    {{ $row['commission'] > 0 ? number_format($row['commission']) : '—' }}</td>
                                <td class="text-end text-danger text-nowrap py-3">
                                    <div class="d-inline-flex flex-column align-items-end gap-1">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <span
                                                class="fw-semibold text-danger">{{ $row['ncc_payment'] > 0 ? number_format($row['ncc_payment']) : '—' }}</span>
                                            @if(!empty($row['ncc_payment_sheet_url']))
                                                <a href="{{ $row['ncc_payment_sheet_url'] }}" target="_blank"
                                                    rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm py-0 px-2"
                                                    title="Mở Google Sheet">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                </a>
                                            @endif
                                        </div>
                                        @if(!empty($row['ncc_payment_updated_at']))
                                            <small class="text-muted">Cập nhật: {{ $row['ncc_payment_updated_at'] }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td
                                    class="text-end fw-bold text-nowrap py-3 {{ $row['net_received'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($row['net_received']) }}</td>
                                <td class="text-center py-3">
                                    <div class="d-inline-flex flex-column align-items-center gap-1">
                                        @if($canManageNccPayment)
                                            <select class="form-select form-select-sm w-100"
                                                wire:model.live="paymentStatuses.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                <option value="unpaid">Chưa thanh toán</option>
                                                <option value="paid">Đã thanh toán</option>
                                            </select>
                                            @if($this->selectedPaymentStatus($this->stateKey($row['source_key'], $row['id']), $row) === 'paid')
                                                <input type="date" class="form-control form-control-sm w-100"
                                                    wire:model.live="paymentDates.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                    wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                            @endif
                                        @else
                                            <span class="badge {{ $row['ncc_payment_status_badge_class'] }} px-3 py-2">{{ $row['ncc_payment_status_label'] }}</span>
                                            @if(!empty($row['ncc_payment_paid_at']))
                                                <small class="text-muted">{{ $row['ncc_payment_paid_at'] }}</small>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                @if($canManageNccPayment)
                                    <td class="text-center py-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-semibold"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#{{ $this->collapseId($row['source_key'], $row['id']) }}"
                                            aria-expanded="false"
                                            aria-controls="{{ $this->collapseId($row['source_key'], $row['id']) }}">
                                            <i class="fa-solid fa-link me-1"></i>Nguồn chi
                                        </button>
                                    </td>
                                @endif
                            </tr>
                            @if($canManageNccPayment)
                                <tr class="bg-body-tertiary">
                                    <td colspan="{{ $canManageNccPayment ? 10 : 9 }}" class="p-0 border-0">
                                        <div id="{{ $this->collapseId($row['source_key'], $row['id']) }}" class="collapse">
                                            <div class="p-3 p-lg-4 border-bottom border-secondary-subtle">
                                                <div class="d-flex align-items-center gap-2 mb-3">
                                                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </span>
                                                    <div>
                                                        <div class="fw-bold text-body">Cập nhật chi nhà thầu phụ</div>
                                                        <small class="text-muted">Nhập trực tiếp hoặc đồng bộ số tiền từ Google Sheet</small>
                                                    </div>
                                                </div>
                                                <div class="row g-3 align-items-end">
                                                    <div class="col-12 col-lg-4">
                                                        <label class="form-label small fw-semibold text-body mb-1">Nhập tay số tiền</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control"
                                                                wire:model.defer="manualNccAmounts.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                                placeholder="VD: 5.000.000"
                                                                oninput="this.value=this.value.replace(/[^\d]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.')">
                                                            <button type="button" class="btn btn-success px-3 fw-semibold"
                                                                wire:click="updateNccPaymentManual('{{ $row['source_key'] }}', {{ $row['id'] }})"
                                                                wire:loading.attr="disabled"
                                                                wire:target="updateNccPaymentManual('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                                <span wire:loading
                                                                    wire:target="updateNccPaymentManual('{{ $row['source_key'] }}', {{ $row['id'] }})"
                                                                    class="spinner-border spinner-border-sm me-1"></span>
                                                                Lưu
                                                            </button>
                                                        </div>
                                                        @if($row['ncc_payment'] > 0)
                                                            <small class="text-muted">Hiện tại: <span
                                                                    class="fw-semibold text-danger">{{ number_format($row['ncc_payment']) }}đ</span></small>
                                                        @endif
                                                    </div>
                                                    <div class="col-12 col-lg-5">
                                                        <label class="form-label small fw-semibold text-body mb-1">Google Sheet công khai</label>
                                                        <div class="input-group">
                                                            <input type="url" class="form-control"
                                                                wire:model.defer="sheetUrls.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                                placeholder="Dán liên kết Google Sheet">
                                                            <button type="button" class="btn btn-primary px-3 fw-semibold"
                                                                wire:click="importNccPaymentFromSheet('{{ $row['source_key'] }}', {{ $row['id'] }})"
                                                                wire:loading.attr="disabled"
                                                                wire:target="importNccPaymentFromSheet('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                                <span wire:loading
                                                                    wire:target="importNccPaymentFromSheet('{{ $row['source_key'] }}', {{ $row['id'] }})"
                                                                    class="spinner-border spinner-border-sm me-1"></span>
                                                                Đồng bộ
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-3">
                                                        <label class="form-label small fw-semibold text-body mb-1">Tình trạng thanh toán</label>
                                                        <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1">
                                                            <select class="form-select"
                                                                wire:model.live="paymentStatuses.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                                wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                                <option value="unpaid">Chưa thanh toán</option>
                                                                <option value="paid">Đã thanh toán</option>
                                                            </select>
                                                            @if($this->selectedPaymentStatus($this->stateKey($row['source_key'], $row['id']), $row) === 'paid')
                                                                <input type="date" class="form-control"
                                                                    wire:model.live="paymentDates.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                                    wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end mt-3">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#{{ $this->collapseId($row['source_key'], $row['id']) }}"
                                                        aria-expanded="true"
                                                        aria-controls="{{ $this->collapseId($row['source_key'], $row['id']) }}">
                                                        <i class="fa-solid fa-xmark me-1"></i>Đóng
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ $canManageNccPayment ? 10 : 9 }}" class="text-center py-5">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-10 text-secondary p-3 mb-3">
                                        <i class="fa-solid fa-folder-open fs-3"></i>
                                    </div>
                                    <div class="fw-semibold text-body mb-1">Chưa có dữ liệu dòng tiền</div>
                                    <div class="text-muted small">Thử thay đổi kỳ báo cáo hoặc điều kiện tìm kiếm.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($totals['count'] > 0)
                        <tfoot class="bg-body-tertiary fw-bold border-top border-secondary-subtle">
                            <tr>
                                <td colspan="3" class="text-end py-3">Tổng cộng</td>
                                <td class="text-end py-3">{{ number_format($totals['value_without_vat']) }}</td>
                                <td class="text-end text-primary py-3">{{ number_format($totals['revenue']) }}</td>
                                <td class="text-end text-warning py-3">{{ number_format($totals['commission']) }}</td>
                                <td class="text-end text-danger py-3">{{ number_format($totals['ncc_payment']) }}</td>
                                <td class="text-end text-success py-3">{{ number_format($totals['net_received']) }}</td>
                                <td></td>
                                @if($canManageNccPayment)
                                    <td></td>
                                @endif
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator && $rows->hasPages())
                <div class="card-footer bg-body px-3 py-3 border-top border-secondary-subtle d-flex justify-content-center">
                    {{ $rows->links('livewire.admin.users.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>
