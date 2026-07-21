<div>
    <header class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-start gap-3">
            <span class="d-none d-sm-inline-flex align-items-center justify-content-center rounded-3 text-bg-primary p-3 shadow-sm" aria-hidden="true">
                <i class="fa-solid fa-file-signature fa-lg"></i>
            </span>
            <div>
                <h1 class="h4 fw-bold text-body mb-1">Tạo báo giá</h1>
                <p class="text-secondary-emphasis mb-1">Soạn thảo, xuất file và chuyển báo giá sang danh sách theo dõi.</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb small mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tạo báo giá</li>
                    </ol>
                </nav>
            </div>
        </div>
        <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2" wire:click="create" wire:loading.attr="disabled" wire:target="create">
            <span wire:loading.remove wire:target="create"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
            <span wire:loading wire:target="create" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            <span>Tạo báo giá mới</span>
        </button>
    </header>

    <section class="card border shadow-sm mb-4" aria-labelledby="quotation-document-filter-heading">
        <div class="card-header bg-body border-bottom px-3 px-lg-4 py-3">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-2 text-bg-primary p-2" aria-hidden="true"><i class="fa-solid fa-sliders"></i></span>
                <div>
                    <h2 id="quotation-document-filter-heading" class="h6 fw-bold text-body mb-1">Tìm và lọc báo giá</h2>
                    <p class="small text-secondary-emphasis mb-0">Tra cứu theo khách hàng, số báo giá, nhân viên hoặc ngày tạo.</p>
                </div>
            </div>
        </div>
        <div class="card-body p-3 p-lg-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="quotation-document-search" class="form-label fw-semibold small">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary text-primary"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                        <input id="quotation-document-search" type="search" class="form-control" placeholder="Khách hàng hoặc số báo giá..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="quotation-document-staff" class="form-label fw-semibold small">Nhân viên</label>
                    <select id="quotation-document-staff" class="form-select" wire:model.live="filter_staff">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($staffs as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-8 col-lg-4">
                    <label class="form-label fw-semibold small">Khoảng thời gian</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="quotation-document-date-from" class="visually-hidden">Từ ngày</label>
                            <input id="quotation-document-date-from" type="date" class="form-control" wire:model.live="date_from" title="Từ ngày">
                        </div>
                        <div class="col-6">
                            <label for="quotation-document-date-to" class="visually-hidden">Đến ngày</label>
                            <input id="quotation-document-date-to" type="date" class="form-control" wire:model.live="date_to" title="Đến ngày">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card border shadow-sm overflow-hidden" aria-labelledby="quotation-document-list-heading">
        <div class="card-header bg-body border-bottom px-3 px-lg-4 py-3 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary p-2" aria-hidden="true"><i class="fa-solid fa-table-list"></i></span>
                <div>
                    <h2 id="quotation-document-list-heading" class="h6 fw-bold text-body mb-1">Danh sách báo giá</h2>
                    <p class="small text-secondary-emphasis mb-0">Các tài liệu báo giá theo bộ lọc hiện tại</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-primary rounded-pill px-3 py-2">{{ number_format($documents->total(), 0, ',', '.') }} báo giá</span>
                <div wire:loading.flex wire:target="search,filter_staff,date_from,date_to" class="align-items-center gap-2 small text-primary" role="status">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>Đang cập nhật...</span>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr class="text-nowrap">
                        <th scope="col" class="ps-3 ps-lg-4 py-3 text-center">STT</th>
                        <th scope="col" class="py-3">Báo giá</th>
                        <th scope="col" class="py-3">Khách hàng</th>
                        <th scope="col" class="py-3">Dịch vụ</th>
                        <th scope="col" class="py-3">Người tạo</th>
                        <th scope="col" class="py-3 text-end">Tổng có VAT</th>
                        <th scope="col" class="py-3 pe-3 pe-lg-4 text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @forelse($documents as $item)
                    <tr wire:key="quotation-document-row-{{ $item->id }}">
                        <td class="ps-3 ps-lg-4 py-3 text-center text-secondary-emphasis fw-semibold">{{ ($documents->currentPage() - 1) * $documents->perPage() + $loop->iteration }}</td>
                        <td class="py-3">
                            <button type="button" class="btn btn-link p-0 fw-bold text-start text-decoration-none" wire:click="viewDetail({{ $item->id }})">{{ $item->document_number }}</button>
                            <div class="small text-secondary-emphasis mt-1"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>{{ $item->date->format('d/m/Y') }}</div>
                            @if($item->quotation)
                            <a href="{{ route('app.quotation-tracking.index', ['search' => $item->document_number]) }}" class="badge text-bg-success mt-2 text-decoration-none"><i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>Đã theo dõi</a>
                            @endif
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-body">{{ $item->customer_name }}</div>
                            @if($item->customer_contact)<div class="small text-secondary-emphasis mt-1"><i class="fa-regular fa-address-card me-1" aria-hidden="true"></i>{{ $item->customer_contact }}</div>@endif
                        </td>
                        <td class="py-3">@if($item->service_type)<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">{{ $item->service_type }}</span>@else<span class="text-secondary-emphasis">—</span>@endif</td>
                        <td class="py-3 text-body fw-medium">{{ $item->staff?->name ?: '—' }}</td>
                        <td class="py-3 text-end text-nowrap"><span class="fw-bold text-danger">{{ number_format($item->total, 0, ',', '.') }}</span><span class="small text-secondary-emphasis">đ</span></td>
                        <td class="py-3 pe-3 pe-lg-4 text-end text-nowrap">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="viewDetail({{ $item->id }})"><i class="fa-regular fa-eye me-1" aria-hidden="true"></i>Xem</button>
                                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false"><span class="visually-hidden">Mở thêm thao tác</span></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><a class="dropdown-item" href="{{ route('app.quotation-docs.export-word', $item->id) }}"><i class="fa-regular fa-file-word me-2 text-primary" aria-hidden="true"></i>Xuất Word</a></li>
                                    <li><a class="dropdown-item" href="{{ route('app.quotation-docs.export-pdf', $item->id) }}" target="_blank"><i class="fa-regular fa-file-pdf me-2 text-danger" aria-hidden="true"></i>Xuất PDF</a></li>
                                    <li><button type="button" class="dropdown-item" wire:click="transferToTracking({{ $item->id }})"><i class="fa-solid fa-arrow-right-arrow-left me-2 text-success" aria-hidden="true"></i>{{ $item->quotation ? 'Cập nhật theo dõi' : 'Chuyển sang theo dõi' }}</button></li>
                                    <li><button type="button" class="dropdown-item" wire:click="duplicate({{ $item->id }})"><i class="fa-regular fa-copy me-2 text-secondary" aria-hidden="true"></i>Sao chép</button></li>
                                    <li><button type="button" class="dropdown-item" wire:click="edit({{ $item->id }})"><i class="fa-regular fa-pen-to-square me-2 text-warning" aria-hidden="true"></i>Chỉnh sửa</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><button type="button" class="dropdown-item text-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xác nhận xóa báo giá này?"><i class="fa-regular fa-trash-can me-2" aria-hidden="true"></i>Xóa báo giá</button></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center px-3 py-5"><i class="fa-regular fa-file-lines d-block fs-1 text-secondary mb-3" aria-hidden="true"></i><h3 class="h6 fw-bold mb-1">Chưa có báo giá</h3><p class="text-secondary-emphasis mb-3">Tạo báo giá đầu tiên hoặc thay đổi bộ lọc hiện tại.</p><button type="button" class="btn btn-sm btn-primary" wire:click="create">Tạo báo giá mới</button></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documents->hasPages())<div class="px-3 px-lg-4 py-3 border-top">{{ $documents->links('livewire.admin.users.pagination') }}</div>@endif
    </section>

    <!-- Detail Modal -->
    <div wire:ignore.self class="modal fade" id="qdocDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title fw-bold text-white">Chi tiết Báo giá</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0 overflow-auto">
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
                            <table class="table table-bordered table-sm align-middle mb-0 small">
                                <thead class="table-dark">
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
                            <table class="table table-bordered table-sm align-middle mb-0 small">
                                <thead class="table-dark">
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
            <form wire:submit.prevent="save" class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white">
                        @if($isEditing) Cập nhật Báo giá
                        @else Tạo Báo giá mới
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 overflow-auto">
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
                            <table class="table table-bordered table-sm align-middle mb-0 small">
                                <thead class="table-dark">
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
                            <div class="text-muted fw-bold small">
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
                            <table class="table table-bordered table-sm align-middle mb-0 small">
                                <thead class="table-dark">
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
                                            <input type="text"
                                                   list="detail-indicators-{{ $i }}-{{ md5($rowGroupName.'|'.($formData['price_subcontractor'] ?? '')) }}"
                                                   class="form-control form-control-sm border-0 bg-transparent px-1"
                                                   wire:key="detail-description-{{ $i }}-{{ md5($rowGroupName.'|'.($formData['price_subcontractor'] ?? '')) }}"
                                                   wire:model.live.debounce.250ms="detailItems.{{ $i }}.description"
                                                   placeholder="Chọn hoặc nhập chỉ tiêu...">
                                            <datalist id="detail-indicators-{{ $i }}-{{ md5($rowGroupName.'|'.($formData['price_subcontractor'] ?? '')) }}">
                                                @foreach($rowDetailPriceCatalog as $catalogItem)
                                                    <option value="{{ $catalogItem['description'] }}">
                                                    </option>
                                                @endforeach
                                            </datalist>
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
                            <div class="text-muted fw-bold small">
                                Tổng chỉ tiêu: <span class="text-primary">{{ number_format($matrixTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.78rem; min-width: 1500px;">
                                <thead class="table-dark">
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
