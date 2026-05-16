<div>
    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Năm</label>
                    <select wire:model.live="filterYear" class="form-select form-select-sm">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Kỳ</label>
                    <select wire:model.live="filterPeriodType" class="form-select form-select-sm">
                        <option value="year">Cả năm</option>
                        <option value="quarter">Theo quý</option>
                        <option value="month">Theo tháng</option>
                    </select>
                </div>
                @if($filterPeriodType === 'quarter')
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Quý</label>
                    <select wire:model.live="filterQuarter" class="form-select form-select-sm">
                        <option value="0">-- Chọn quý --</option>
                        @foreach([1,2,3,4] as $q)
                            <option value="{{ $q }}">Quý {{ $q }}</option>
                        @endforeach
                    </select>
                </div>
                @elseif($filterPeriodType === 'month')
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1">Tháng</label>
                    <select wire:model.live="filterMonth" class="form-select form-select-sm">
                        <option value="0">-- Chọn tháng --</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}">Tháng {{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1">Loại hợp đồng</label>
                    <select wire:model.live="filterContractType" class="form-select form-select-sm">
                        <option value="all">Tất cả loại hợp đồng</option>
                        @foreach($contractTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1">Hạng mục dịch vụ</label>
                    <select wire:model.live="filterServiceCategory" class="form-select form-select-sm">
                        <option value="all">Tất cả hạng mục</option>
                        @foreach($serviceCategoryOptions as $serviceCategory)
                            <option value="{{ $serviceCategory }}">{{ $serviceCategory }}</option>
                        @endforeach
                    </select>
                </div>
                @if($canManageNccPayment)
                <div class="col-md-auto">
                    <button wire:click="importAllNccPaymentsFromSheets" class="btn btn-primary fw-semibold px-3 py-2" wire:loading.attr="disabled" wire:target="importAllNccPaymentsFromSheets">
                        <span wire:loading wire:target="importAllNccPaymentsFromSheets" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-arrow-repeat me-2"></i> Đồng bộ
                    </button>
                </div>
                @endif
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
                    <p class="text-muted mb-1" style="font-size:13px">Tổng chi Nhà Cung Cấp</p>
                    <h5 class="fw-bold text-danger mb-0">{{ number_format($totals['ncc_payment']) }}đ</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #22c55e !important;">
                <div class="card-body">
                    <p class="text-muted mb-1" style="font-size:13px">Tổng thực nhận</p>
                    <h5 class="fw-bold text-success mb-0">{{ number_format($totals['net_received']) }}đ</h5>
                    <small class="text-muted">= DS - Chi Nhà Cung Cấp</small>
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
                            <th style="width:190px">Hợp đồng</th>
                            <th style="width:210px">Hạng mục dịch vụ</th>
                            <th>Khách hàng</th>
                            <th class="text-end">Giá trị chưa VAT</th>
                            <th class="text-end">Doanh số</th>
                            <th class="text-end">Hoa hồng</th>
                            <th class="text-end text-danger">Chi Nhà Cung Cấp</th>
                            <th class="text-end text-success fw-bold">Thực nhận</th>
                            <th class="text-center" style="width:190px">Tình trạng thanh toán</th>
                            <th class="text-center" style="width:140px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                        @php($sheetStateKey = $row['source_key'] . '_' . $row['id'])
                        @php($activeSheetUrl = $sheetUrls[$sheetStateKey] ?? $row['ncc_payment_sheet_url'])
                        @php($sheetCollapseId = 'sheetEditor_' . $sheetStateKey)
                        <tr>
                            <td class="text-center text-muted">{{ ($rows->currentPage() - 1) * $rows->perPage() + $i + 1 }}</td>
                            <td style="min-width:190px">
                                @php($baoChauMessage = $baoChauInvoiceMessages[$sheetStateKey] ?? null)
                                <div class="d-flex flex-column gap-2">
                                    <span class="badge bg-light text-dark border align-self-start text-start px-2 py-2" style="font-size:12px; white-space:normal">{{ $row['type'] }}</span>
                                @if($canEditBaoChauInvoice)
                                    <input type="text"
                                           class="form-control form-control-sm fw-semibold text-center"
                                           value="{{ $row['shd_bc'] }}"
                                           placeholder="Nhập số HĐ BC"
                                           wire:change="updateBaoChauInvoiceNumber('{{ $row['source_key'] }}', {{ $row['id'] }}, $event.target.value)">
                                    @if($baoChauMessage)
                                        <small class="{{ $baoChauMessage['type'] === 'error' ? 'text-danger' : 'text-success' }}">{{ $baoChauMessage['text'] }}</small>
                                    @endif
                                @else
                                        <span class="fw-semibold">{{ $row['shd_bc'] ?: '—' }}</span>
                                @endif
                                </div>
                            </td>
                            <td style="min-width:210px">
                                <span class="text-muted">{{ $row['service_category'] ?: '—' }}</span>
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
                                        <select class="form-select form-select-sm"
                                                style="min-width: 170px;"
                                                wire:model.live="paymentStatuses.{{ $sheetStateKey }}"
                                                wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                            <option value="unpaid">Chưa thanh toán</option>
                                            <option value="paid">Đã thanh toán</option>
                                        </select>
                                        @if(($paymentStatuses[$sheetStateKey] ?? $row['ncc_payment_status']) === 'paid')
                                            <input type="date"
                                                   class="form-control form-control-sm"
                                                   style="min-width: 170px;"
                                                   wire:model.live="paymentDates.{{ $sheetStateKey }}"
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
                            <td class="text-center">
                                @if($canManageNccPayment)
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm px-3 py-2 fw-semibold"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#{{ $sheetCollapseId }}"
                                            aria-expanded="false"
                                            aria-controls="{{ $sheetCollapseId }}">
                                        <i class="bi bi-link-45deg me-1"></i>Sheet
                                    </button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @if($canManageNccPayment)
                        <tr class="bg-light-subtle">
                            <td colspan="11" class="px-3 py-3">
                                <div id="{{ $sheetCollapseId }}" class="collapse">
                                    <div class="row g-3 align-items-start">
                                        <div class="col-lg-7">
                                            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 h-100">
                                                <div class="text-muted fw-semibold" style="min-width: 170px; font-size:13px;">Link Google Sheet</div>
                                                <div class="input-group">
                                                    <input type="url"
                                                           class="form-control py-2"
                                                           wire:model.defer="sheetUrls.{{ $sheetStateKey }}"
                                                           placeholder="Dan link Google Sheet cong khai">
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
                                                            data-bs-target="#{{ $sheetCollapseId }}"
                                                            aria-expanded="true"
                                                            aria-controls="{{ $sheetCollapseId }}">
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
                                                            wire:model.live="paymentStatuses.{{ $sheetStateKey }}"
                                                            wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                        <option value="unpaid">Chưa thanh toán</option>
                                                        <option value="paid">Đã thanh toán</option>
                                                    </select>
                                                    @if(($paymentStatuses[$sheetStateKey] ?? $row['ncc_payment_status']) === 'paid')
                                                        <input type="date"
                                                               class="form-control py-2"
                                                               style="min-width: 170px;"
                                                               wire:model.live="paymentDates.{{ $sheetStateKey }}"
                                                               wire:change="updateNccPaymentStatus('{{ $row['source_key'] }}', {{ $row['id'] }})">
                                                    @endif
                                                    <span class="text-success small d-inline-flex align-items-center px-2">
                                                        <i class="bi bi-check2-circle me-1"></i>Tự động lưu
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 text-muted mt-2" style="font-size:13px;">
                                        <span>Lấy số ở dòng chứa "Tổng Cộng".</span>
                                        @if(($paymentStatuses[$sheetStateKey] ?? $row['ncc_payment_status']) === 'paid')
                                            <span>Ngày thanh toán sẽ hiển thị dưới badge trạng thái.</span>
                                        @endif
                                        @if(!empty($activeSheetUrl))
                                            <span>Đã lưu link sheet cho dòng này.</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">Không có dữ liệu cho kỳ đã chọn.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($totals['count'] > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Tổng cộng</td>
                            <td class="text-end">{{ number_format($totals['value_without_vat']) }}</td>
                            <td class="text-end text-primary">{{ number_format($totals['revenue']) }}</td>
                            <td class="text-end text-warning">{{ number_format($totals['commission']) }}</td>
                            <td class="text-end text-danger">{{ number_format($totals['ncc_payment']) }}</td>
                            <td class="text-end text-success">{{ number_format($totals['net_received']) }}</td>
                            <td></td>
                            <td></td>
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
