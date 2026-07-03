<div>
    <div class="page-header d-flex align-items-start align-items-sm-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h4 class="mb-0">Tạo Báo giá</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Tạo Báo giá</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 ms-auto flex-wrap justify-content-end align-items-center">
            <button class="btn btn-success btn-sm d-flex align-items-center gap-1" wire:click="create">
                <i class="fa-solid fa-plus-lg"></i> Tạo mới
            </button>
            <div class="input-group w-230px">
                <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm..." wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nhân viên</label>
                    <select class="form-select form-select-sm" wire:model.live="filter_staff">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Khoảng thời gian</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control form-control-sm" wire:model.live="date_from">
                        <input type="date" class="form-control form-control-sm" wire:model.live="date_to">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive overflow-auto">
            <table class="table table-hover align-middle mb-0 table-sm" style="font-size: 0.85rem;">
                <thead class="bg-light bg-opacity-50" style="--bs-table-bg: #C5EECE; --bs-table-color: #000; background-color: #C5EECE;">
                    <tr class="text-muted fw-bold">
                        <th class="ps-3 w-40px">STT</th>
                        <th class="w-120px">Số báo giá</th>
                        <th class="w-100px">Ngày</th>
                        <th>Khách hàng</th>
                        <th class="w-150px">Loại dịch vụ</th>
                        <th class="w-160px">Mẫu</th>
                        <th class="w-100px">NV tạo</th>
                        <th class="text-end w-130px">Tổng (có VAT)</th>
                        <th class="text-center pe-3 w-130px">#</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $index => $item)
                    <tr class="border-bottom border-light">
                        <td class="ps-3">{{ ($documents->currentPage()-1) * $documents->perPage() + $loop->iteration }}</td>
                        <td>
                            <span class="fw-semibold text-primary fs-82">{{ $item->document_number }}</span>
                            @if($item->quotation)
                            <a href="{{ route('app.quotation-tracking.index', ['search' => $item->document_number]) }}" class="badge bg-success bg-opacity-10 text-success border mt-1 text-decoration-none d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-check-circle"></i> Đã theo dõi
                            </a>
                            @endif
                        </td>
                        <td class="text-nowrap fs-82">{{ $item->date->format('d/m/Y') }}</td>
                        <td>
                            <div class="fw-bold text-primary text-capitalize fs-85 lh-sm">{{ $item->customer_name }}</div>
                            @if($item->customer_contact)
                            <div class="text-muted mt-1 fs-75"><i class="fa-solid fa-user-circle me-1"></i>{{ $item->customer_contact }}</div>
                            @endif
                        </td>
                        <td>
                            @if($item->service_type)
                            <span class="badge bg-info bg-opacity-10 text-info border px-2 py-1 fs-70">{{ $item->service_type }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 fs-70">
                                {{ $this->selectedTemplateLabel($item->template_key ?? null) }}
                            </span>
                        </td>
                        <td>
                            <div class="text-truncate fs-82" title="{{ $item->staff?->name }}">{{ $item->staff?->name }}</div>
                        </td>
                        <td class="text-end fw-bold text-danger">{{ number_format($item->total, 0, ',', '.') }}đ</td>
                        <td class="text-center pe-3">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm p-0 text-primary" wire:click="viewDetail({{ $item->id }})" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye fs-5"></i>
                                </button>
                                <a href="{{ route('app.quotation-docs.export-word', $item->id) }}" class="btn btn-sm p-0 text-primary" title="Xuất Word">
                                    <i class="fa-solid fa-file-word fs-5"></i>
                                </a>
                                <a href="{{ route('app.quotation-docs.export-pdf', $item->id) }}" target="_blank" class="btn btn-sm p-0 text-danger" title="Xuất PDF">
                                    <i class="fa-solid fa-file-pdf fs-5"></i>
                                </a>
                                <button class="btn btn-sm p-0 text-success"
                                        wire:click="transferToTracking({{ $item->id }})"
                                        title="{{ $item->quotation ? 'Cập nhật bảng theo dõi' : 'Chuyển sang bảng theo dõi' }}">
                                    <i class="bi {{ $item->quotation ? 'bi-arrow-repeat' : 'bi-arrow-right-circle' }} fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-secondary" wire:click="duplicate({{ $item->id }})" title="Sao chép">
                                    <i class="fa-solid fa-copy fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-warning" wire:click="edit({{ $item->id }})" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-square fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-danger"
                                        wire:click="delete({{ $item->id }})"
                                        wire:confirm="Xác nhận xóa báo giá này?">
                                    <i class="fa-solid fa-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-file-text fs-1 d-block mb-2 opacity-25"></i>
                            Chưa có báo giá nào. Nhấn <strong>"Tạo mới"</strong> để bắt đầu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documents->hasPages())
        <div class="px-3 py-3 border-top">
            {{ $documents->links('livewire.admin.users.pagination') }}
        </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div wire:ignore.self class="modal fade" id="qdocDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title fw-bold text-white">Chi tiết Báo giá</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if($selectedDoc)
                    <div class="p-4">
                        <!-- Header Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="fw-bold text-muted w-40pct">Số báo giá:</td><td class="fw-bold text-primary">{{ $selectedDoc->document_number }}</td></tr>
                                    <tr><td class="fw-bold text-muted">Ngày:</td><td>{{ $selectedDoc->date->format('d/m/Y') }}</td></tr>
                                    @if($selectedDoc->valid_until)
                                    <tr><td class="fw-bold text-muted">Hiệu lực đến:</td><td>{{ $selectedDoc->valid_until->format('d/m/Y') }}</td></tr>
                                    @endif
                                    <tr><td class="fw-bold text-muted">Nhân viên:</td><td>{{ $selectedDoc->staff?->name }}</td></tr>
                                    <tr><td class="fw-bold text-muted">Mẫu:</td><td><span class="badge bg-secondary">{{ $this->selectedTemplateLabel($selectedDoc->template_key ?? null) }}</span></td></tr>
                                    @if($selectedDoc->service_type)
                                    <tr><td class="fw-bold text-muted">Loại dịch vụ:</td><td><span class="badge bg-info">{{ $selectedDoc->service_type }}</span></td></tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="fw-bold text-muted w-40pct">Khách hàng:</td><td class="fw-bold text-primary">{{ $selectedDoc->customer_name }}</td></tr>
                                    @if($selectedDoc->customer_address)<tr><td class="fw-bold text-muted">Địa chỉ:</td><td>{{ $selectedDoc->customer_address }}</td></tr>@endif
                                    @if($selectedDoc->customer_contact)<tr><td class="fw-bold text-muted">Người liên hệ:</td><td>{{ $selectedDoc->customer_contact }}</td></tr>@endif
                                    @if($selectedDoc->customer_phone)<tr><td class="fw-bold text-muted">Điện thoại:</td><td>{{ $selectedDoc->customer_phone }}</td></tr>@endif
                                    @if($selectedDoc->customer_tax_code)<tr><td class="fw-bold text-muted">MST:</td><td>{{ $selectedDoc->customer_tax_code }}</td></tr>@endif
                                    @if($selectedDoc->work_location)<tr><td class="fw-bold text-muted">Địa điểm TH:</td><td>{{ $selectedDoc->work_location }}</td></tr>@endif
                                </table>
                            </div>
                        </div>

                        <!-- Table 01: Summary -->
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-table text-primary me-1"></i> Bảng 01. Tổng hợp dự toán chi phí thực hiện</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-primary" style="--bs-table-bg: #C5EECE; --bs-table-color: #000; background-color: #C5EECE;">
                                    <tr class="text-center fw-bold">
                                        <th class="w-40px">STT</th>
                                        <th>Nội dung dịch vụ</th>
                                        <th class="w-80px">ĐVT</th>
                                        <th class="w-70px">SL</th>
                                        <th class="w-130px text-end">Đơn giá</th>
                                        <th class="w-140px text-end">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($this->summaryItemsForDetail($selectedDoc) as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-center">{{ $item->unit }}</td>
                                        <td class="text-center">{{ $item->quantity == (int)$item->quantity ? (int)$item->quantity : $item->quantity }}</td>
                                        <td class="text-end">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold">{{ number_format($item->amount, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-2">Không có hạng mục tổng hợp</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="5" class="text-end">Tổng cộng (chưa VAT):</td>
                                        <td class="text-end">{{ number_format($selectedDoc->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @if($selectedDoc->discount > 0)
                                    <tr class="fw-bold">
                                        <td colspan="5" class="text-end">Chiết khấu:</td>
                                        <td class="text-end text-success">-{{ number_format($selectedDoc->discount, 0, ',', '.') }}</td>
                                    </tr>
                                    @endif
                                    <tr class="fw-bold">
                                        <td colspan="5" class="text-end">Thuế VAT ({{ $selectedDoc->vat_rate }}%):</td>
                                        <td class="text-end">{{ number_format($selectedDoc->vat_amount, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="fw-bold text-danger fs-5">
                                        <td colspan="5" class="text-end">TỔNG THANH TOÁN (có VAT):</td>
                                        <td class="text-end">{{ number_format($selectedDoc->total, 0, ',', '.') }}đ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Table 02: Details Grouped -->
                        @if($this->groupedDetailItemsForDetail($selectedDoc)->isNotEmpty())
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-list-check text-success me-1"></i> Bảng 02. Chi tiết thực hiện</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-info" style="--bs-table-bg: #C5EECE; --bs-table-color: #000; background-color: #C5EECE;">
                                    <tr class="text-center fw-bold">
                                        <th class="w-40px">STT</th>
                                        <th>Chỉ tiêu / Nội dung công việc</th>
                                        <th class="w-80px">ĐVT</th>
                                        <th class="w-130px text-end">Chi phí (đơn giá)</th>
                                        <th class="w-70px">SL</th>
                                        <th class="w-140px text-end">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->groupedDetailItemsForDetail($selectedDoc) as $groupName => $groupItems)
                                        <tr class="table-light">
                                            <td colspan="6" class="fw-bold text-dark ps-2">{{ $groupName }}</td>
                                        </tr>
                                        @foreach($groupItems as $item)
                                        <tr>
                                            <td class="text-center text-muted">{{ $this->detailRowIndexForGroup($selectedDoc, (string) $groupName, $loop->index) }}</td>
                                            <td>{{ $item->description }}</td>
                                            <td class="text-center">{{ $item->unit }}</td>
                                            <td class="text-end">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                            <td class="text-center">{{ $item->quantity == (int)$item->quantity ? (int)$item->quantity : $item->quantity }}</td>
                                            <td class="text-end fw-bold">{{ number_format($item->amount, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold table-warning">
                                        <td colspan="5" class="text-end">Tổng cộng chi phí chi tiết (Bảng 02):</td>
                                        <td class="text-end">{{ number_format($this->groupedDetailTotalAmount($selectedDoc), 0, ',', '.') }}đ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @endif

                        @if($selectedDoc->notes)
                        <div class="mt-3">
                            <strong>Ghi chú:</strong>
                            <p class="mb-0 text-muted">{!! nl2br(e($selectedDoc->notes)) !!}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="modal-footer bg-light px-4 py-3">
                        <a href="{{ route('app.quotation-docs.export-word', $selectedDoc->id) }}" class="btn btn-primary">
                            <i class="fa-solid fa-file-word me-1"></i> Xuất Word
                        </a>
                        <a href="{{ route('app.quotation-docs.export-pdf', $selectedDoc->id) }}" target="_blank" class="btn btn-danger">
                            <i class="fa-solid fa-file-pdf me-1"></i> Xuất PDF
                        </a>
                        <button type="button" class="btn btn-success" wire:click="transferToTracking({{ $selectedDoc->id }})">
                            <i class="bi {{ $selectedDoc->quotation ? 'bi-arrow-repeat' : 'bi-arrow-right-circle' }} me-1"></i>
                            {{ $selectedDoc->quotation ? 'Cập nhật theo dõi' : 'Chuyển sang theo dõi' }}
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    <div wire:ignore.self class="modal fade" id="qdocFormModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white">
                        @if($isEditing) Cập nhật Báo giá
                        @else Tạo Báo giá mới
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
                        <!-- Datalist for autocomplete group names -->
                        <datalist id="group-names">
                            @foreach($groupOptions as $g)
                                <option value="{{ $g }}"></option>
                            @endforeach
                        </datalist>

                        <!-- Thông tin chung -->
                        <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-circle-info me-1"></i> Thông tin báo giá</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Số báo giá <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('formData.document_number') is-invalid @enderror" wire:model="formData.document_number">
                                @error('formData.document_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Ngày BG <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model="formData.date">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Hiệu lực đến</label>
                                <input type="date" class="form-control" wire:model="formData.valid_until">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mẫu báo giá & dịch vụ <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select" wire:model.live="formData.template_key">
                                        @foreach($templatePresets as $preset)
                                            <option value="{{ $preset['key'] }}">{{ $preset['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button"
                                            class="btn btn-outline-primary d-flex align-items-center gap-1"
                                            wire:click="applySelectedTemplatePreset"
                                            wire:confirm="Áp dụng mẫu sẽ đặt lại dòng tổng hợp và xóa các dòng chi tiết hiện tại. Tiếp tục?"
                                            title="Áp dụng lại mẫu mặc định">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i> Áp dụng mẫu
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tên dịch vụ hiển thị</label>
                                <input type="text" class="form-control" wire:model.live.debounce.500ms="formData.service_type" placeholder="Tên dịch vụ hiển thị trên báo giá...">
                            </div>
                            @if($priceSubcontractorOptions !== [])
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Nhà thầu phụ</label>
                                <select class="form-select" wire:model.live="formData.price_subcontractor">
                                    @foreach($priceSubcontractorOptions as $subcontractorKey => $subcontractorLabel)
                                        <option value="{{ $subcontractorKey }}">{{ $subcontractorLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <!-- Khách hàng -->
                        <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-building me-1"></i> Thông tin khách hàng</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Chọn khách hàng cũ</label>
                                <select class="form-select" wire:model.live="selectedCustomerId">
                                    <option value="">-- Nhập khách mới --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tên công ty / Khách hàng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('formData.customer_name') is-invalid @enderror" wire:model="formData.customer_name" placeholder="VD: Công ty TNHH Điện Lạnh Bách Khoa">
                                @error('formData.customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Địa chỉ</label>
                                <input type="text" class="form-control" wire:model="formData.customer_address">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Người nhận / Liên hệ</label>
                                <input type="text" class="form-control" wire:model="formData.customer_contact" placeholder="VD: Anh Phi">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Điện thoại</label>
                                <input type="text" class="form-control" wire:model="formData.customer_phone">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" wire:model="formData.customer_email">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">MST</label>
                                <input type="text" class="form-control" wire:model="formData.customer_tax_code">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Địa điểm TH</label>
                                <input type="text" class="form-control" wire:model="formData.work_location" placeholder="Nơi thực hiện">
                            </div>
                        </div>

                        <!-- Bảng 01. Tổng hợp dự toán chi phí thực hiện -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-primary mb-0"><i class="fa-solid fa-table me-1"></i> Bảng 01. Tổng hợp dự toán chi phí thực hiện</h6>
                            @if(count($detailItems) > 0)
                            <button type="button" class="btn btn-xs btn-outline-success d-flex align-items-center gap-1" wire:click="syncDetailToSummary">
                                <i class="fa-solid fa-arrows-rotate"></i> Đồng bộ tổng tiền Bảng 02 sang Bảng 01
                            </button>
                            @endif
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.82rem;">
                                <thead class="table-primary" style="--bs-table-bg: #C5EECE; --bs-table-color: #000; background-color: #C5EECE;">
                                    <tr class="text-center fw-bold">
                                        <th class="w-40px">STT</th>
                                        <th>Nội dung dịch vụ <span class="text-danger">*</span></th>
                                        <th class="w-80px">ĐVT</th>
                                        <th class="w-70px">SL</th>
                                        <th class="w-130px">Đơn giá</th>
                                        <th class="w-130px">Thành tiền</th>
                                        <th class="w-60px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summaryItems as $i => $item)
                                    <tr>
                                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm border-0 bg-transparent px-1"
                                                   wire:model.blur="summaryItems.{{ $i }}.description" placeholder="Nhập nội dung dịch vụ tổng hợp...">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm border-0 bg-transparent text-center px-1"
                                                   wire:model.blur="summaryItems.{{ $i }}.unit" placeholder="Hồ sơ">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm border-0 bg-transparent text-center px-1"
                                                   wire:model.live.debounce.500ms="summaryItems.{{ $i }}.quantity">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm border-0 bg-transparent text-end money-input px-1"
                                                   wire:model.blur="summaryItems.{{ $i }}.unit_price">
                                        </td>
                                        <td class="text-end fw-bold text-nowrap px-2">
                                            {{ number_format((float)($item['amount'] ?? 0), 0, ',', '.') }}đ
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                @if($i > 0)
                                                <button type="button" class="btn btn-sm p-0 text-muted" wire:click="moveSummaryItemUp({{ $i }})" title="Lên"><i class="fa-solid fa-chevron-up"></i></button>
                                                @endif
                                                @if($i < count($summaryItems) - 1)
                                                <button type="button" class="btn btn-sm p-0 text-muted" wire:click="moveSummaryItemDown({{ $i }})" title="Xuống"><i class="fa-solid fa-chevron-down"></i></button>
                                                @endif
                                                @if(count($summaryItems) > 1)
                                                <button type="button" class="btn btn-sm p-0 text-danger" wire:click="removeSummaryItem({{ $i }})" title="Xóa"><i class="fa-solid fa-xmark-lg"></i></button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addSummaryItem">
                                                <i class="fa-solid fa-plus-lg me-1"></i> Thêm hạng mục tổng hợp
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Bảng chi tiết thực hiện -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-success mb-0">
                                <i class="fa-solid fa-list-check me-1"></i>
                                Bảng 02. Chi tiết thực hiện (Đo đạc chỉ tiêu chi tiết)
                            </h6>
                            @if(count($detailItems) > 0)
                            <div class="text-muted fw-bold" style="font-size: 0.85rem;">
                                Tổng chi phí chi tiết Bảng 02:
                                <span class="text-success">{{ number_format($detailTotal, 0, ',', '.') }}đ</span>
                                @if($detailTotal !== (int)($formData['subtotal'] ?? 0))
                                <span class="badge bg-warning text-dark ms-2"><i class="fa-solid fa-triangle-exclamation-fill"></i> Chưa khớp Bảng 01</span>
                                @else
                                <span class="badge bg-success text-white ms-2"><i class="fa-solid fa-circle-check-fill"></i> Khớp Bảng 01</span>
                                @endif
                            </div>
                            @endif
                        </div>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.82rem;">
                                <thead class="table-success text-dark" style="--bs-table-bg: #C5EECE; --bs-table-color: #000; background-color: #C5EECE;">
                                    <tr class="text-center fw-bold">
                                        <th class="w-40px">STT</th>
                                        <th class="w-250px">Tên nhóm / Phân loại <span class="text-danger">*</span></th>
                                        <th>Chỉ tiêu / Nội dung chi tiết <span class="text-danger">*</span></th>
                                        <th class="w-80px">ĐVT</th>
                                        <th class="w-60px">SL</th>
                                        <th class="w-70px">Tần suất</th>
                                        <th class="w-120px">Đơn giá</th>
                                        <th class="w-125px">Thành tiền</th>
                                        <th class="w-60px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($detailItems as $i => $item)
                                    @php($rowGroupName = (string) ($item['group_name'] ?? ''))
                                    @php($rowDetailPriceCatalog = $this->detailPriceCatalogForGroup($rowGroupName))
                                    @php($groupOptionsKey = md5(($formData['template_key'] ?? '').'|'.($formData['price_subcontractor'] ?? '').'|'.implode('|', $groupOptions)))
                                    <tr wire:key="detail-row-{{ $i }}-{{ md5($rowGroupName.'|'.($formData['price_subcontractor'] ?? '')) }}">
                                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <select class="form-select form-select-sm border-0 bg-transparent px-1 fw-bold"
                                                    wire:key="detail-group-{{ $i }}-{{ $groupOptionsKey }}"
                                                    wire:model.live="detailItems.{{ $i }}.group_name">
                                                @foreach($groupOptions as $groupOption)
                                                    <option value="{{ $groupOption }}">{{ $groupOption }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            @if($rowDetailPriceCatalog !== [])
                                            <select class="form-select form-select-sm border-0 bg-transparent px-1"
                                                    wire:key="detail-description-{{ $i }}-{{ md5($rowGroupName.'|'.($formData['price_subcontractor'] ?? '')) }}"
                                                    wire:model.live="detailItems.{{ $i }}.description">
                                                <option value="">-- Chọn chỉ tiêu --</option>
                                                @foreach($rowDetailPriceCatalog as $catalogItem)
                                                    <option value="{{ $catalogItem['description'] }}">
                                                        {{ $catalogItem['description'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @else
                                            <input type="text" class="form-control form-control-sm border-0 bg-transparent px-1"
                                                   wire:model.live.debounce.250ms="detailItems.{{ $i }}.description"
                                                   placeholder="Nhập chỉ tiêu...">
                                            @endif
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm border-0 bg-transparent text-center px-1"
                                                   wire:model.blur="detailItems.{{ $i }}.unit" placeholder="Mẫu">
                                        </td>
                                        <td>
                                            <input type="number" step="1" min="0" inputmode="numeric" class="form-control form-control-sm border-0 bg-transparent text-center px-1"
                                                   wire:model.live.debounce.500ms="detailItems.{{ $i }}.quantity">
                                        </td>
                                        <td>
                                            <input type="number" step="1" min="1" inputmode="numeric" class="form-control form-control-sm border-0 bg-transparent text-center px-1"
                                                   wire:model.live.debounce.500ms="detailItems.{{ $i }}.frequency">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm border-0 bg-transparent text-end money-input px-1"
                                                   wire:model.blur="detailItems.{{ $i }}.unit_price">
                                        </td>
                                        <td class="text-end fw-bold text-nowrap px-2">
                                            {{ number_format((float)($item['amount'] ?? 0), 0, ',', '.') }}đ
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                @if($i > 0)
                                                <button type="button" class="btn btn-sm p-0 text-muted" wire:click="moveDetailItemUp({{ $i }})" title="Lên"><i class="fa-solid fa-chevron-up"></i></button>
                                                @endif
                                                @if($i < count($detailItems) - 1)
                                                <button type="button" class="btn btn-sm p-0 text-muted" wire:click="moveDetailItemDown({{ $i }})" title="Xuống"><i class="fa-solid fa-chevron-down"></i></button>
                                                @endif
                                                <button type="button" class="btn btn-sm p-0 text-danger" wire:click="removeDetailItem({{ $i }})" title="Xóa"><i class="fa-solid fa-xmark-lg"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            Chưa có chỉ tiêu chi tiết nào. Bấm <strong>"Thêm chỉ tiêu chi tiết"</strong>, sau đó gõ/chọn chỉ tiêu ngay trong ô <strong>Chỉ tiêu / Nội dung chi tiết</strong>.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="9">
                                            <button type="button" class="btn btn-sm btn-outline-success" wire:click="addDetailItem">
                                                <i class="fa-solid fa-plus-lg me-1"></i> Thêm chỉ tiêu
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if(($formData['template_key'] ?? '') === 'plld')
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-primary mb-0"><i class="fa-solid fa-table-cells me-1"></i> Ma trận chức danh phân loại lao động</h6>
                            <div class="text-muted fw-bold" style="font-size: 0.85rem;">
                                Tổng chỉ tiêu: <span class="text-primary">{{ number_format($matrixTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.78rem; min-width: 1500px;">
                                <thead class="table-primary" style="--bs-table-bg: #C5EECE; --bs-table-color: #000; background-color: #C5EECE;">
                                    <tr class="text-center fw-bold">
                                        <th class="w-40px">STT</th>
                                        <th style="min-width: 220px;">Chức danh công việc</th>
                                        <th class="w-90px">Số NLĐ</th>
                                        <th class="w-90px">Số đánh giá</th>
                                        @foreach($this->plldMetricColumns() as $label)
                                            <th class="w-80px">{{ $label }}</th>
                                        @endforeach
                                        <th class="w-90px">Tổng</th>
                                        <th class="w-60px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($matrixRows as $i => $row)
                                    <tr>
                                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm border-0 bg-transparent px-1"
                                                   wire:model.blur="matrixRows.{{ $i }}.job_title" placeholder="VD: CN vận hành máy">
                                        </td>
                                        <td>
                                            <input type="number" min="0" class="form-control form-control-sm border-0 bg-transparent text-center px-1"
                                                   wire:model.live.debounce.500ms="matrixRows.{{ $i }}.employee_count">
                                        </td>
                                        <td>
                                            <input type="number" min="0" class="form-control form-control-sm border-0 bg-transparent text-center px-1"
                                                   wire:model.live.debounce.500ms="matrixRows.{{ $i }}.assessment_count">
                                        </td>
                                        @foreach($this->plldMetricColumns() as $key => $label)
                                        <td>
                                            <input type="number" min="0" class="form-control form-control-sm border-0 bg-transparent text-center px-1"
                                                   wire:model.live.debounce.500ms="matrixRows.{{ $i }}.{{ $key }}">
                                        </td>
                                        @endforeach
                                        <td class="text-center fw-bold">{{ number_format((int)($row['total'] ?? 0), 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                @if($i > 0)
                                                <button type="button" class="btn btn-sm p-0 text-muted" wire:click="moveMatrixRowUp({{ $i }})" title="Lên"><i class="fa-solid fa-chevron-up"></i></button>
                                                @endif
                                                @if($i < count($matrixRows) - 1)
                                                <button type="button" class="btn btn-sm p-0 text-muted" wire:click="moveMatrixRowDown({{ $i }})" title="Xuống"><i class="fa-solid fa-chevron-down"></i></button>
                                                @endif
                                                <button type="button" class="btn btn-sm p-0 text-danger" wire:click="removeMatrixRow({{ $i }})" title="Xóa"><i class="fa-solid fa-xmark-lg"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="{{ 6 + count($this->plldMetricColumns()) }}" class="text-center py-4 text-muted">
                                            Chưa có dòng chức danh nào. Bấm <strong>"Thêm chức danh"</strong> để lập ma trận PLLĐ.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="{{ 6 + count($this->plldMetricColumns()) }}">
                                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addMatrixRow">
                                                <i class="fa-solid fa-plus-lg me-1"></i> Thêm chức danh
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @endif

                        <!-- Tổng cộng -->
                        <div class="row justify-content-end mb-4">
                            <div class="col-md-5">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold">Tổng cộng Bảng 01 (chưa VAT):</td>
                                        <td class="text-end fw-bold">{{ number_format((float)($formData['subtotal'] ?? 0), 0, ',', '.') }}đ</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Chiết khấu:</td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control text-end money-input" wire:model.blur="formData.discount">
                                                <span class="input-group-text p-1 fs-70">đ</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">
                                            Thuế VAT:
                                            <div class="d-inline-block ms-1" style="width: 55px;">
                                                <input type="number" min="0" max="100" class="form-control form-control-sm text-center p-1 d-inline-block"
                                                       wire:model.live.debounce.500ms="vatRate" style="width: 50px; display: inline-block;">
                                            </div>
                                            %
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format((float)($formData['vat_amount'] ?? 0), 0, ',', '.') }}đ</td>
                                    </tr>
                                    <tr class="border-top border-2">
                                        <td class="fw-bold text-danger fs-5">TỔNG THANH TOÁN (Bảng 01 + VAT):</td>
                                        <td class="text-end fw-bold text-danger fs-5">{{ number_format((float)($formData['total'] ?? 0), 0, ',', '.') }}đ</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Ghi chú & Điều khoản -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ghi chú</label>
                                <textarea class="form-control" rows="4" wire:model="formData.notes" placeholder="Ghi chú thêm cho báo giá..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Điều khoản</label>
                                <textarea class="form-control" rows="4" wire:model="formData.terms" placeholder="Điều khoản thanh toán, thời gian thực hiện..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-2"></span>
                            Lưu báo giá
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            let formModal = new bootstrap.Modal(document.getElementById('qdocFormModal'));
            let detailModal = new bootstrap.Modal(document.getElementById('qdocDetailModal'));

            Livewire.on('open-qdoc-modal', () => { detailModal.hide(); formModal.show(); });
            Livewire.on('close-qdoc-modal', () => formModal.hide());
            Livewire.on('open-qdoc-detail-modal', () => { formModal.hide(); detailModal.show(); });
            Livewire.on('close-qdoc-detail-modal', () => detailModal.hide());
        });
    </script>
    @endpush
</div>
