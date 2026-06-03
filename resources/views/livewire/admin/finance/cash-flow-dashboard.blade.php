<div>
    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Năm</label>
                    <select wire:model.live="filterYear" class="form-select">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Kỳ</label>
                    <select wire:model.live="filterPeriodType" class="form-select">
                        <option value="year">Cả năm</option>
                        <option value="quarter">Theo quý</option>
                        <option value="month">Theo tháng</option>
                    </select>
                </div>
                @if($filterPeriodType === 'quarter')
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Quý</label>
                    <select wire:model.live="filterQuarter" class="form-select">
                        <option value="0">-- Chọn quý --</option>
                        @foreach([1,2,3,4] as $q)
                            <option value="{{ $q }}">Quý {{ $q }}</option>
                        @endforeach
                    </select>
                </div>
                @elseif($filterPeriodType === 'month')
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Tháng</label>
                    <select wire:model.live="filterMonth" class="form-select">
                        <option value="0">-- Chọn tháng --</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}">Tháng {{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1">Loại hợp đồng</label>
                    <select wire:model.live="filterContractType" class="form-select">
                        <option value="all">Tất cả loại hợp đồng</option>
                        @foreach($contractTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1">Hạng mục dịch vụ</label>
                    <select wire:model.live="filterServiceCategory" class="form-select">
                        <option value="all">Tất cả hạng mục</option>
                        @foreach($serviceCategoryOptions as $serviceCategory)
                            <option value="{{ $serviceCategory }}">{{ $serviceCategory }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Đơn vị thực hiện</label>
                    <select wire:model.live="filterHandlerType" class="form-select">
                        <option value="all">Tất cả</option>
                        <option value="tdx">TĐX (Trái Đất Xanh)</option>
                        <option value="non_tdx">BC (Bảo Châu)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Tên công ty, số HĐ Bảo Châu, nhà thầu phụ...">
                        @if($search !== '')
                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('search', '')">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif
                    </div>
                </div>
                @can('cash-flow.export')
                <div class="col-md-auto ms-auto">
                    <button wire:click="exportExcel" class="btn btn-success btn-sm" wire:loading.attr="disabled">
                        <span wire:loading wire:target="exportExcel" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>

    {{-- 4 summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1" style="font-size:13px">Tổng doanh số</p>
                    <h5 class="fw-bold text-primary mb-0">{{ number_format($totals['revenue']) }}đ</h5>
                    <small class="text-muted">{{ $totals['count'] }} hợp đồng</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1" style="font-size:13px">Tổng hoa hồng</p>
                    <h5 class="fw-bold text-warning mb-0">{{ number_format($totals['commission']) }}đ</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1" style="font-size:13px">Tổng chi Nhà thầu phụ</p>
                    <h5 class="fw-bold text-danger mb-0">{{ number_format($totals['ncc_payment']) }}đ</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #22c55e !important;">
                <div class="card-body">
                    <p class="text-muted mb-1" style="font-size:13px">Tổng thực nhận</p>
                    <h5 class="fw-bold text-success mb-0">{{ number_format($totals['net_received']) }}đ</h5>
                    <small class="text-muted">= DS - Chi Nhà thầu phụ</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng chi tiết --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">Chi tiết dòng tiền — {{ $periodLabel }}</h6>
            <span class="badge bg-secondary">{{ $totals['count'] }} hợp đồng</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13px">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width:40px">STT</th>
                            <th style="width:230px">Hợp đồng</th>
                            <th>Khách hàng</th>
                            <th class="text-end">Giá trị chưa VAT</th>
                            <th class="text-end">Doanh số</th>
                            <th class="text-end">Hoa hồng</th>
                            <th class="text-end text-danger">Chi Nhà thầu phụ</th>
                            <th class="text-end text-success fw-bold">Thực nhận</th>
                            <th class="text-center" style="width:220px">Tình trạng thanh toán</th>
                            @if($canManageNccPayment)
                                <th class="text-center" style="width:140px">Hành động</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                        <tr>
                            <td class="text-center text-muted">{{ ($rows->currentPage() - 1) * $rows->perPage() + $i + 1 }}</td>
                            <td style="min-width:230px">
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge {{ $row['type_badge_class'] ?? 'bg-light text-dark border' }} align-self-start text-start px-2 py-2" style="font-size:12px; white-space:normal">{{ $row['type'] }}</span>
                                        @if(!empty($row['service_category']))
                                            <small class="text-muted">{{ $row['service_category'] }}</small>
                                        @endif
                                    </div>
                                @if($canEditBaoChauInvoice)
                                    <label class="form-label small text-muted mb-0">Số HĐ Bảo Châu</label>
                                    <input type="text"
                                           class="form-control form-control-sm fw-semibold text-center"
                                           style="font-size: 12px;"
                                           value="{{ $row['shd_bc'] }}"
                                           placeholder="Nhập số HĐ BC"
                                           wire:change="updateBaoChauInvoiceNumber('{{ $row['source_key'] }}', {{ $row['id'] }}, $event.target.value)">
                                    @if($this->baoChauMessageFor($this->stateKey($row['source_key'], $row['id'])))
                                        <small class="{{ $this->baoChauMessageFor($this->stateKey($row['source_key'], $row['id']))['type'] === 'error' ? 'text-danger' : 'text-success' }}">{{ $this->baoChauMessageFor($this->stateKey($row['source_key'], $row['id']))['text'] }}</small>
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
                                                <small class="text-muted d-block">Nhà thầu phụ <span class="badge bg-light text-dark border" style="font-size:10px">TĐX</span></small>
                                            @else
                                                <small class="text-muted d-block">Nhà thầu phụ</small>
                                            @endif
                                            <div class="fw-semibold text-dark">{{ $row['handler'] ?: '—' }}</div>
                                            @if($canEditBaoChauInvoice)
                                                <label class="form-label small text-muted mb-0 mt-1">{{ $this->isTdxRow($row) ? 'Số HĐ TĐX' : 'Số HĐ/HĐ NTP' }}</label>
                                                <input type="text"
                                                       class="form-control form-control-sm fw-semibold text-center"
                                                       style="font-size: 12px;"
                                                       value="{{ $row['shd_cxl'] }}"
                                                       placeholder="{{ $this->isTdxRow($row) ? 'Nhập số HĐ TĐX' : 'Nhập số HĐ NTP' }}"
                                                       wire:change="updateSubcontractorInvoiceNumber('{{ $row['source_key'] }}', {{ $row['id'] }}, $event.target.value)">
                                                @if($this->subcontractorMessageFor($this->stateKey($row['source_key'], $row['id'])))
                                                    <small class="{{ $this->subcontractorMessageFor($this->stateKey($row['source_key'], $row['id']))['type'] === 'error' ? 'text-danger' : 'text-success' }}">{{ $this->subcontractorMessageFor($this->stateKey($row['source_key'], $row['id']))['text'] }}</small>
                                                @endif
                                            @else
                                                <small class="text-muted">{{ $this->isTdxRow($row) ? 'Số HĐ TĐX' : 'Số HĐ NTP' }}: <span class="fw-semibold text-dark">{{ $row['shd_cxl'] ?: '—' }}</span></small>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td style="min-width:280px; max-width:380px">
                                @if(!empty($row['customer_slug']))
                                    <a href="{{ route('app.customers.contracts', ['customer' => $row['customer_slug']]) }}" class="text-decoration-none fw-bold text-primary">
                                        {{ $row['customer'] }}
                                    </a>
                                @else
                                    <span class="fw-bold text-primary">{{ $row['customer'] ?? '—' }}</span>
                                @endif
                                <div class="d-flex flex-wrap gap-3 text-muted mt-1" style="font-size:12px">
                                    <span>NV: {{ $row['staff'] ?? '—' }}</span>
                                    <span>Ngày ký: {{ $row['signed_at'] ?? '—' }}</span>
                                </div>
                                @if($canEditInvoiceDate)
                                    <div class="mt-2">
                                        <label class="form-label small text-muted mb-0">Ngày xuất hóa đơn</label>
                                        <input type="date"
                                               class="form-control form-control-sm"
                                               wire:model.live="invoiceDates.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                               wire:change="updateInvoiceDate('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                    </div>
                                @else
                                    @if(!empty($row['submitted_at']))
                                        <div class="mt-1" style="font-size:12px">
                                            <span class="text-muted">Xuất HĐ: </span>
                                            <span class="fw-semibold">{{ $row['submitted_at'] }}</span>
                                        </div>
                                    @endif
                                @endif
                                @if(!empty($row['contract_note']))
                                    <div class="text-muted mt-2" style="font-size:12px; word-break:break-word;">
                                        <span class="fw-semibold text-dark">Ghi chú HĐ:</span> {{ $row['contract_note'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">{{ $row['value_without_vat'] > 0 ? number_format($row['value_without_vat']) : '—' }}</td>
                            <td class="text-end text-nowrap">{{ $row['revenue'] > 0 ? number_format($row['revenue']) : '—' }}</td>
                            <td class="text-end text-warning text-nowrap">{{ $row['commission'] > 0 ? number_format($row['commission']) : '—' }}</td>
                            <td class="text-end text-danger text-nowrap">
                                <div class="d-inline-flex flex-column align-items-end gap-1">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <span class="fw-semibold text-danger">{{ $row['ncc_payment'] > 0 ? number_format($row['ncc_payment']) : '—' }}</span>
                                        @if(!empty($row['ncc_payment_sheet_url']))
                                            <a href="{{ $row['ncc_payment_sheet_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Mở Google Sheet">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        @endif
                                    </div>
                                    @if(!empty($row['ncc_payment_updated_at']))
                                        <small class="text-muted">Cập nhật: {{ $row['ncc_payment_updated_at'] }}</small>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end fw-bold text-nowrap {{ $row['net_received'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($row['net_received']) }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex flex-column align-items-center gap-1">
                                    @if($canManageNccPayment)
                                        <select class="form-select"
                                                style="width: 100%;"
                                                wire:model.live="paymentStatuses.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                            <option value="unpaid">Chưa thanh toán</option>
                                            <option value="paid">Đã thanh toán</option>
                                        </select>
                                        @if($this->selectedPaymentStatus($this->stateKey($row['source_key'], $row['id']), $row) === 'paid')
                                            <input type="date"
                                                   class="form-control"
                                                   style="width: 100%;"
                                                   wire:model.live="paymentDates.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                   wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                        @endif
                                    @else
                                        <span class="badge {{ $row['ncc_payment_status_badge_class'] }} px-3 py-2" style="font-size:12px;">{{ $row['ncc_payment_status_label'] }}</span>
                                        @if(!empty($row['ncc_payment_paid_at']))
                                            <small class="text-muted">{{ $row['ncc_payment_paid_at'] }}</small>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            @if($canManageNccPayment)
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm px-3 py-2 fw-semibold"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#{{ $this->collapseId($row['source_key'], $row['id']) }}"
                                            aria-expanded="false"
                                            aria-controls="{{ $this->collapseId($row['source_key'], $row['id']) }}">
                                        <i class="bi bi-link-45deg me-1"></i>Sheet
                                    </button>
                                </td>
                            @endif
                        </tr>
                        @if($canManageNccPayment)
                        <tr class="bg-light-subtle">
                            <td colspan="{{ $canManageNccPayment ? 10 : 9 }}" class="px-3 py-3">
                                <div id="{{ $this->collapseId($row['source_key'], $row['id']) }}" class="collapse">
                                    {{-- Nhập tay --}}
                                    <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 mb-3">
                                        <div class="text-muted fw-semibold" style="min-width: 170px; font-size:13px;">Nhập tay số tiền</div>
                                        <div class="input-group" style="max-width: 340px;">
                                            <input type="text"
                                                   class="form-control py-2"
                                                   wire:model.defer="manualNccAmounts.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                   placeholder="VD: 5.000.000"
                                                   oninput="this.value=this.value.replace(/[^\d]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.')">
                                            <button type="button"
                                                    class="btn btn-success px-3 fw-semibold"
                                                    wire:click="updateNccPaymentManual('{{ $row['source_key'] }}', {{ $row['id'] }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="updateNccPaymentManual('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                <span wire:loading wire:target="updateNccPaymentManual('{{ $row['source_key'] }}', {{ $row['id'] }})" class="spinner-border spinner-border-sm me-1"></span>
                                                Lưu
                                            </button>
                                        </div>
                                        @if($row['ncc_payment'] > 0)
                                            <small class="text-muted">Hiện tại: <span class="fw-semibold text-danger">{{ number_format($row['ncc_payment']) }}đ</span></small>
                                        @endif
                                    </div>
                                    <hr class="my-2">
                                    {{-- Hoặc lấy từ Google Sheet --}}
                                    <div class="row g-3 align-items-start">
                                        <div class="col-lg-7">
                                            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 h-100">
                                                <div class="text-muted fw-semibold" style="min-width: 170px; font-size:13px;">Hoặc từ Sheet</div>
                                                <div class="input-group">
                                                    <input type="url"
                                                           class="form-control py-2"
                                                           wire:model.defer="sheetUrls.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                           placeholder="Dán link Google Sheet công khai">
                                                    <button type="button"
                                                            class="btn btn-primary px-3 fw-semibold"
                                                            wire:click="importNccPaymentFromSheet('{{ $row['source_key'] }}', {{ $row['id'] }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="importNccPaymentFromSheet('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                        <span wire:loading wire:target="importNccPaymentFromSheet('{{ $row['source_key'] }}', {{ $row['id'] }})" class="spinner-border spinner-border-sm me-1"></span>
                                                        Cập nhật
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-secondary px-3 fw-semibold"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#{{ $this->collapseId($row['source_key'], $row['id']) }}"
                                                            aria-expanded="true"
                                                            aria-controls="{{ $this->collapseId($row['source_key'], $row['id']) }}">
                                                        Hủy
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-5">
                                            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                                                <div class="text-muted fw-semibold" style="min-width: 170px; font-size:13px;">Tình trạng thanh toán</div>
                                                <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1">
                                                    <select class="form-select py-2"
                                                            style="min-width: 180px;"
                                                            wire:model.live="paymentStatuses.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                            wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                        <option value="unpaid">Chưa thanh toán</option>
                                                        <option value="paid">Đã thanh toán</option>
                                                    </select>
                                                    @if($this->selectedPaymentStatus($this->stateKey($row['source_key'], $row['id']), $row) === 'paid')
                                                        <input type="date"
                                                               class="form-control py-2"
                                                               style="min-width: 170px;"
                                                               wire:model.live="paymentDates.{{ $this->stateKey($row['source_key'], $row['id']) }}"
                                                               wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                    @endif
                                                    <span class="text-success small d-inline-flex align-items-center px-2">
                                                        <i class="bi bi-check2-circle me-1"></i>Tự động lưu
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="{{ $canManageNccPayment ? 10 : 9 }}" class="text-center text-muted py-4">Không có dữ liệu cho kỳ đã chọn.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($totals['count'] > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">Tổng cộng</td>
                            <td class="text-end">{{ number_format($totals['value_without_vat']) }}</td>
                            <td class="text-end text-primary">{{ number_format($totals['revenue']) }}</td>
                            <td class="text-end text-warning">{{ number_format($totals['commission']) }}</td>
                            <td class="text-end text-danger">{{ number_format($totals['ncc_payment']) }}</td>
                            <td class="text-end text-success">{{ number_format($totals['net_received']) }}</td>
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
                <div class="card-footer px-3 border-0 d-flex justify-content-center">
                    {{ $rows->links('livewire.admin.users.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>
