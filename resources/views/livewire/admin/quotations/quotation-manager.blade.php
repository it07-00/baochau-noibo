<div>
    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3 mt-2 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1 text-body" style="letter-spacing: -0.025em;">Theo dõi Báo giá</h2>
            <p class="text-muted mb-0 small" style="max-width: 680px;">
                Quản lý tiến độ báo giá, hoa hồng, giá trị hợp đồng và các tài liệu Word/PDF liên quan.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('quotation-tracking.create')
            <button class="btn btn-primary rounded-8px btn-mobile-touch" wire:click="create">
                <i class="fa-solid fa-plus me-1"></i> Thêm mới
            </button>
            <button class="btn btn-outline-secondary rounded-8px btn-mobile-touch"
                    wire:click="resetImport"
                    data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-arrow-up me-1"></i> Import Excel
            </button>
            @endcan
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-lg-4">
            <div class="d-flex flex-wrap align-items-end gap-3">
                <div class="flex-grow-1" style="min-width: 240px; max-width: 320px;">
                    <label class="form-label fw-semibold small text-body mb-2">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0 text-muted border-light-subtle">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="search" class="form-control border-start-0 ps-0 border-light-subtle"
                               placeholder="Tìm kiếm công ty, ngành nghề..."
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div style="min-width: 180px;">
                    <label class="form-label fw-semibold small text-body mb-2">Nhân viên sale</label>
                    <select class="form-select border-light-subtle" wire:model.live="filter_staff">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 160px;">
                    <label class="form-label fw-semibold small text-body mb-2">Tình trạng</label>
                    <select class="form-select border-light-subtle" wire:model.live="filter_status">
                        <option value="">Tất cả tình trạng</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-grow-1" style="min-width: 280px; max-width: 360px;">
                    <label class="form-label fw-semibold small text-body mb-2">Khoảng thời gian</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control border-light-subtle" wire:model.live="date_from">
                        <input type="date" class="form-control border-light-subtle" wire:model.live="date_to">
                    </div>
                </div>
                <div style="min-width: 130px;">
                    <label class="form-label fw-semibold small text-body mb-2">Sắp xếp</label>
                    <select class="form-select border-light-subtle" wire:model.live="sortDirection">
                        <option value="desc">Mới nhất</option>
                        <option value="asc">Cũ nhất</option>
                    </select>
                </div>
                <div>
                    <button class="btn btn-outline-secondary rounded-8px text-nowrap" style="padding: 0.375rem 1rem;" wire:click="resetFilters">
                        <i class="fa-solid fa-xmark me-1"></i> Xóa lọc
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive overflow-auto">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.925rem; min-width: 1480px;">
                <thead class="bg-body-tertiary">
                    <tr class="text-body-secondary text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.04em;">
                        <th class="ps-3 py-3 w-40px">STT</th>
                        <th class="py-3 w-200px">Sale / Số báo giá</th>
                        <th class="py-3">Công ty, Khách hàng &amp; Dịch vụ</th>
                        <th class="py-3">Tình hình làm việc</th>
                        <th class="py-3 text-center w-140px">Tình hình</th>
                        <th class="py-3 text-end w-130px">Giá trị gốc</th>
                        <th class="py-3 text-end w-100px">Hoa hồng KH</th>
                        <th class="py-3 text-end w-85px">Thuế HH</th>
                        <th class="py-3 text-end fw-bold w-120px">Giá trị HĐ</th>
                        @can('quotation-tracking.view')
                        <th class="py-3 text-center pe-3 w-220px">Hành động</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $index => $item)
                    <tr class="border-bottom border-light">
                        <td class="ps-3 py-3 text-body-secondary">{{ ($quotations->currentPage()-1) * $quotations->perPage() + $loop->iteration }}</td>
                        {{-- Cột gộp: Sale + Số báo giá --}}
                        <td class="w-200px py-3">
                            <div class="fw-semibold text-truncate" title="{{ $item->staff?->name }}">{{ $item->staff?->name }}</div>
                            <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                @if($item->source)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1">{{ $item->source }}</span>
                                @endif
                                <span class="text-muted small text-nowrap">{{ $item->date ? $item->date->format('d/m/Y') : '-' }}</span>
                            </div>
                            @if($item->quotation_number)
                            <div class="mt-1">
                                <span class="fw-semibold text-primary">{{ $item->quotation_number }}</span>
                                @if($item->quotationDocuments->first())
                                <a href="{{ route('app.quotation-docs.export-pdf', $item->quotationDocuments->first()->id) }}" target="_blank" class="badge bg-success bg-opacity-10 text-success border ms-1 text-decoration-none align-items-center gap-1">
                                    <i class="fa-solid fa-file-pdf"></i> PDF
                                </a>
                                @endif
                            </div>
                            @endif
                        </td>
                        {{-- Cột gộp: Công ty/Khách hàng + Dịch vụ --}}
                        <td class="py-3">
                            <div class="fw-semibold text-primary text-capitalize lh-sm">{{ $item->company_name }}</div>
                            @if($item->contact_person)
                            <div class="text-muted mt-1 small"><i class="fa-solid fa-user-circle me-1"></i>{{ $item->contact_person }}</div>
                            @endif
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @if($item->province)
                                <span class="badge px-2 py-1" style="font-size: 0.72rem; background: rgba(29, 78, 216, 0.1); color: #1e40af; border: 1px solid rgba(29, 78, 216, 0.2);">{{ $item->province }}</span>
                                @endif
                                @if($item->industry)
                                <span class="badge bg-light text-dark border px-2 py-1" title="Ngành nghề: {{ $item->industry }}">
                                    <i class="fa-solid fa-briefcase me-1 text-muted"></i>{{ $item->industry }}
                                </span>
                                @endif
                                @if($item->service)
                                <span class="badge px-2 py-1" title="Dịch vụ: {{ $item->service }}" style="font-size: 0.72rem; background: rgba(194, 65, 12, 0.1); color: #9a3412; border: 1px solid rgba(194, 65, 12, 0.2);">
                                    <i class="fa-solid fa-gear me-1"></i>{{ \Illuminate\Support\Str::limit($item->service, 30) }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="text-wrap text-truncate-200 py-3 lh-base">
                            <div class="line-clamp-3" title="{{ $item->work_description }}">
                                {{ $item->work_description ?: '-' }}
                            </div>
                        </td>
                        <td class="text-center py-3">
                            @can('quotation-tracking.edit')
                            <select 
                                wire:change="updateStatus({{ $item->id }}, $event.target.value)"
                                class="form-select rounded-pill fw-semibold text-center border-0 py-2 px-3 small {{ $this->statusBadgeClass($item->status) }}"
                                style="min-width: 140px; width: auto; display: inline-block; cursor: pointer; -webkit-appearance: none; -moz-appearance: none; appearance: none; text-align-last: center;"
                                title="Nhấp để cập nhật nhanh tình hình"
                            >
                                @foreach($statuses as $st)
                                    <option value="{{ $st }}" class="text-dark bg-white" {{ $item->status === $st ? 'selected' : '' }}>
                                        {{ $st }}
                                    </option>
                                @endforeach
                            </select>
                            @else
                            <span class="badge rounded-pill {{ $this->statusBadgeClass($item->status) }} px-3 py-2" >
                                {{ $item->status }}
                            </span>
                            @endcan
                        </td>
                        <td class="text-end py-3 text-nowrap fw-bold text-body-emphasis" style="font-variant-numeric: tabular-nums; font-size: 1rem;">
                            {{ $item->original_value ? number_format($item->original_value, 0, ',', '.') : '-' }}@if($item->original_value)<span class="ms-1 fw-normal text-body-secondary small">₫</span>@endif
                        </td>
                        <td class="text-end py-3 text-nowrap fw-bold text-body-emphasis" style="font-variant-numeric: tabular-nums; font-size: 1rem;">
                            {{ $item->commission_value ? number_format($item->commission_value, 0, ',', '.') : '-' }}@if($item->commission_value)<span class="ms-1 fw-normal text-body-secondary small">₫</span>@endif
                        </td>
                        <td class="text-end py-3 text-nowrap fw-bold text-body-emphasis" style="font-variant-numeric: tabular-nums; font-size: 1rem;">
                            {{ $item->commission_tax ? number_format($item->commission_tax, 0, ',', '.') : '-' }}@if($item->commission_tax)<span class="ms-1 fw-normal text-body-secondary small">₫</span>@endif
                        </td>
                        <td class="text-end py-3 text-nowrap fw-bold text-danger" style="font-variant-numeric: tabular-nums; font-size: 1.05rem;">
                            {{ $item->total_value ? number_format($item->total_value, 0, ',', '.') : '-' }}@if($item->total_value)<span class="ms-1 fw-semibold small">₫</span>@endif
                        </td>
                        @can('quotation-tracking.view')
                        <td class="text-center pe-3 py-3">
                            <div class="gap-1" style="display: inline-grid; grid-template-columns: repeat(6, 26px);">
                                <!-- 1. View -->
                                <button class="btn btn-sm border-0 bg-transparent rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" wire:click="viewDetail({{ $item->id }})" title="Xem chi tiết" aria-label="Xem chi tiết">
                                    <i class="bi bi-eye-fill text-primary" style="font-size: 0.9rem;"></i>
                                </button>

                                <!-- 2. PDF Document -->
                                @if($item->pdf_path)
                                    <a href="{{ \Illuminate\Support\Str::startsWith($item->pdf_path, ['http://', 'https://']) ? $item->pdf_path : Storage::disk(config('filesystems.upload_disk', 'public'))->url($item->pdf_path) }}" target="_blank" class="btn btn-sm border-0 bg-transparent rounded-circle p-0 d-inline-flex align-items-center justify-content-center text-danger" style="width: 26px; height: 26px;" title="File PDF báo giá" aria-label="Mở file PDF báo giá">
                                        <i class="bi bi-file-earmark-pdf-fill" style="font-size: 0.9rem;"></i>
                                    </a>
                                @elseif($item->files_count > 0)
                                    <button class="btn btn-sm border-0 bg-transparent rounded-circle p-0 d-inline-flex align-items-center justify-content-center text-danger position-relative" style="width: 26px; height: 26px;" wire:click="openFiles({{ $item->id }})" title="{{ $item->files_count }} file PDF" aria-label="Xem {{ $item->files_count }} file PDF">
                                        <i class="bi bi-file-earmark-pdf-fill" style="font-size: 0.9rem;"></i>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px; padding: 2px 4px;">{{ $item->files_count }}</span>
                                    </button>
                                @elseif($item->quotationDocuments->first())
                                    <a href="{{ route('app.quotation-docs.export-pdf', $item->quotationDocuments->first()->id) }}" target="_blank" class="btn btn-sm border-0 bg-transparent rounded-circle p-0 d-inline-flex align-items-center justify-content-center text-danger" style="width: 26px; height: 26px;" title="Xem PDF báo giá tạo tự động" aria-label="Xem PDF báo giá tạo tự động">
                                        <i class="bi bi-file-earmark-pdf-fill" style="font-size: 0.9rem;"></i>
                                    </a>
                                @else
                                    @can('quotation-tracking.edit')
                                        <button class="btn btn-sm border-0 bg-transparent rounded-circle p-0 d-inline-flex align-items-center justify-content-center text-muted" style="width: 26px; height: 26px;" wire:click="openFiles({{ $item->id }})" title="Tải lên PDF" aria-label="Tải lên PDF">
                                            <i class="bi bi-cloud-arrow-up-fill" style="font-size: 0.9rem;"></i>
                                        </button>
                                    @endcan
                                @endif

                                <!-- 3. Word Document -->
                                @if($item->quotationDocuments->first())
                                    <a href="{{ route('app.quotation-docs.export-pdf', $item->quotationDocuments->first()->id) }}" target="_blank" class="btn btn-sm border-0 bg-transparent rounded-circle p-0 d-inline-flex align-items-center justify-content-center text-success" style="width: 26px; height: 26px;" title="Mở báo giá Word/PDF gốc" aria-label="Mở báo giá Word hoặc PDF gốc">
                                        <i class="bi bi-file-earmark-word-fill" style="font-size: 0.9rem;"></i>
                                    </a>
                                @endif

                                <!-- 4. Convert to Contract -->
                                @can('quotation-tracking.edit')
                                    <button class="btn btn-sm border-0 bg-transparent rounded-circle p-0 d-inline-flex align-items-center justify-content-center text-success" style="width: 26px; height: 26px;" wire:click="selectContractType({{ $item->id }})" title="Chuyển thành Hợp đồng" aria-label="Chuyển thành hợp đồng">
                                        <i class="bi bi-file-earmark-arrow-down-fill" style="font-size: 0.9rem;"></i>
                                    </button>
                                @endcan

                                <!-- 5. Duplicate -->
                                @can('quotation-tracking.edit')
                                    <button class="btn btn-sm border-0 bg-transparent rounded-circle p-0 d-inline-flex align-items-center justify-content-center text-secondary" style="width: 26px; height: 26px;" wire:click="duplicate({{ $item->id }})" title="Sao chép" aria-label="Sao chép báo giá">
                                        <i class="bi bi-copy" style="font-size: 0.9rem;"></i>
                                    </button>
                                @endcan

                                <!-- 6. Edit -->
                                @can('quotation-tracking.edit')
                                    <button class="btn btn-sm border-0 bg-transparent rounded-circle p-0 d-inline-flex align-items-center justify-content-center text-warning" style="width: 26px; height: 26px;" wire:click="edit({{ $item->id }})" title="Chỉnh sửa" aria-label="Chỉnh sửa báo giá">
                                        <i class="bi bi-pencil-square" style="font-size: 0.9rem;"></i>
                                    </button>
                                @endcan

                                <!-- 7. Delete -->
                                @can('quotation-tracking.delete')
                                    <button class="btn btn-sm border-0 bg-transparent rounded-circle p-0 d-inline-flex align-items-center justify-content-center text-danger" style="width: 26px; height: 26px;"
                                            wire:click="delete({{ $item->id }})"
                                            wire:confirm="Xác nhận xóa báo giá này?"
                                            title="Xóa" aria-label="Xóa báo giá">
                                        <i class="bi bi-trash3-fill" style="font-size: 0.9rem;"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-5 text-muted">Không tìm thấy dữ liệu báo giá</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
        <div class="px-3 py-3 border-top">
            {{ $quotations->links('livewire.admin.users.pagination') }}
        </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title fw-bold text-white">Thông tin Báo giá Chi tiết</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if($selectedQuotation)
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 w-30pct" >Nhân viên sale</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->staff?->name }} ({{ $selectedQuotation->date?->format('d/m/Y') }})</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Số báo giá</th>
                                    <td class="px-4 py-3 fw-semibold text-primary">{{ $selectedQuotation->quotation_number ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Nguồn</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->source ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Công ty / Khách hàng</th>
                                    <td class="px-4 py-3 fw-bold text-primary text-capitalize">{{ $selectedQuotation->company_name }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Địa chỉ XHĐ</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->address ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Địa chỉ làm việc</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->work_address ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Tỉnh thành</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->province ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Dịch vụ</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->service ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Ngành nghề</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->industry ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Khách hàng</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->contact_person ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Tình hình làm việc</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->work_description ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Tình hình</th>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-primary px-3 py-2">{{ $selectedQuotation->status }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Ghi chú</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->notes ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Giá trị gốc</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->original_value, 0, ',', '.') }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Hoa hồng KH</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->commission_value, 0, ',', '.') }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Thuế HH</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->commission_tax, 0, ',', '.') }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Giá trị HĐ (có VAT)</th>
                                    <td class="px-4 py-3 fw-bold text-danger fs-5">{{ number_format($selectedQuotation->total_value, 0, ',', '.') }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3"><i class="fa-solid fa-file-pdf text-danger me-1"></i>FILE PDF BÁO GIÁ</th>
                                    <td class="px-4 py-3">
                                        @if($selectedQuotation->quotationDocuments->first())
                                            <a href="{{ route('app.quotation-docs.export-pdf', $selectedQuotation->quotationDocuments->first()->id) }}" target="_blank" class="d-inline-flex align-items-center gap-2 text-success text-decoration-none small mb-2">
                                                <i class="fa-solid fa-file-word"></i>
                                                <span>Báo giá Word/PDF gốc: {{ $selectedQuotation->quotationDocuments->first()->document_number }}</span>
                                            </a>
                                        @endif
                                        @if($selectedQuotation->files->isNotEmpty())
                                            <div class="d-flex flex-column gap-1">
                                                @foreach($selectedQuotation->files as $f)
                                                <a href="{{ Storage::disk(config('filesystems.upload_disk', 'public'))->url($f->path) }}" target="_blank" class="d-flex align-items-center gap-2 text-danger text-decoration-none small">
                                                    <i class="fa-solid fa-file-pdf"></i>
                                                    <span class="text-truncate">{{ $f->original_name }}</span>
                                                </a>
                                                @endforeach
                                            </div>
                                        @elseif(! $selectedQuotation->quotationDocuments->first())
                                            <span class="text-muted">Chưa có tài liệu</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    <div wire:ignore.self class="modal fade" id="quotationModal" tabindex="-1" aria-hidden="true"
         x-data="{
             original_value: @entangle('formData.original_value'),
             commission_value: @entangle('formData.commission_value'),
             commission_tax: @entangle('formData.commission_tax'),
             value_inc_vat: 0,
             total_value: 0,

             parseMoney(val) {
                 if (val === null || val === undefined) return 0;
                 let cleaned = String(val).replace(/\D/g, '');
                 return cleaned !== '' ? parseInt(cleaned, 10) : 0;
             },

             formatMoney(val) {
                 return String(val).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
             },

             isCommissionTaxManual() {
                 return this.parseMoney(this.commission_value) > 5000000;
             },

             recalculate() {
                 let orig = this.parseMoney(this.original_value);
                 let comm = this.parseMoney(this.commission_value);
                 let tax = 0;

                 if (this.isCommissionTaxManual()) {
                     tax = this.parseMoney(this.commission_tax);
                 } else {
                     if (comm <= 1000000) {
                         tax = Math.round(comm * 0.20);
                     } else {
                         tax = Math.round(comm * 0.30);
                     }
                     this.commission_tax = tax;
                 }

                 this.value_inc_vat = orig + comm + tax;
                 this.total_value = Math.round(this.value_inc_vat * 1.08);
             }
         }"
         x-init="
             $watch('original_value', () => recalculate());
             $watch('commission_value', () => recalculate());
             $watch('commission_tax', () => recalculate());
             recalculate();
         ">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white">
                        @if($isEditing) Cập nhật Báo giá
                        @elseif($isDuplicating) Sao chép Báo giá
                        @else Thêm Báo giá mới
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ngày báo giá <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model.defer="formData.date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ngày dự kiến ký HĐ</label>
                                <input type="date" class="form-control @error('formData.expected_signing_date') is-invalid @enderror" wire:model.defer="formData.expected_signing_date">
                                @error('formData.expected_signing_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Số báo giá</label>
                                <input type="text" class="form-control @error('formData.quotation_number') is-invalid @enderror" wire:model.defer="formData.quotation_number" placeholder="VD: BG2026-001">
                                @error('formData.quotation_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nhân viên sale <span class="text-danger">*</span></label>
                                <select class="form-select @error('formData.staff_id') is-invalid @enderror" wire:model.defer="formData.staff_id">
                                    <option value="">Chọn nhân viên</option>
                                    @foreach($staffs as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Công ty <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('formData.company_name') is-invalid @enderror" wire:model.defer="formData.company_name" placeholder="Tên công ty niêm yết">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Nguồn</label>
                                <select class="form-select" wire:model.defer="formData.source">
                                    <option value="">-- Chọn nguồn --</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source }}">{{ $source }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tỉnh thành</label>
                                <select class="form-select" wire:model.defer="formData.province">
                                    <option value="">-- Chọn tỉnh/TP --</option>
                                    @foreach(\App\Support\VietnamProvinces::list() as $p)
                                        <option value="{{ $p }}">{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Khách hàng</label>
                                <input type="text" class="form-control" wire:model.defer="formData.contact_person">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Địa chỉ xuất hóa đơn (XHĐ)</label>
                                <input type="text" class="form-control" wire:model.defer="formData.address">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Địa chỉ làm việc</label>
                                <input type="text" class="form-control" wire:model.defer="formData.work_address">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Dịch vụ</label>
                                <input type="text" class="form-control" wire:model.defer="formData.service" placeholder="VD: Xử lý chất thải...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ngành nghề</label>
                                <input type="text" class="form-control" wire:model.defer="formData.industry">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Tình hình <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model.defer="formData.status">
                                    @foreach($statuses as $st)
                                        <option value="{{ $st }}">{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Tình hình làm việc / Nội dung BG</label>
                                <textarea class="form-control" rows="3" wire:model.defer="formData.work_description"></textarea>
                            </div>

                            <hr class="my-1">
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Giá trị gốc</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end money-input" 
                                           x-model="original_value"
                                           wire:model.blur="formData.original_value">
                                    <span class="input-group-text p-1 fs-70" >đ</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Hoa hồng KH</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end money-input" 
                                           x-model="commission_value"
                                           wire:model.blur="formData.commission_value">
                                    <span class="input-group-text p-1 fs-70" >đ</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Thuế HH</label>
                                <div class="input-group">
                                    <!-- Readonly Input (Auto-calculated) -->
                                    <template x-if="!isCommissionTaxManual()">
                                        <input type="text"
                                            class="form-control text-end money-input bg-light"
                                            x-bind:value="formatMoney(commission_tax)"
                                            readonly>
                                    </template>
                                    <!-- Editable Input (Manual) -->
                                    <template x-if="isCommissionTaxManual()">
                                        <input type="text"
                                            class="form-control text-end money-input"
                                            x-model="commission_tax"
                                            wire:model.blur="formData.commission_tax">
                                    </template>
                                    <span class="input-group-text p-1 fs-70" >đ</span>
                                </div>
                                <template x-if="!isCommissionTaxManual()">
                                    <small class="text-muted">Tự tính 20%–30%</small>
                                </template>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Giá trị chưa VAT</label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control text-end fw-bold bg-light money-input"
                                        x-bind:value="formatMoney(value_inc_vat)"
                                        readonly>
                                    <span class="input-group-text p-1 fs-70" >đ</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Giá trị HĐ (có VAT)</label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control text-end fw-bold text-danger bg-light money-input"
                                        x-bind:value="formatMoney(total_value)"
                                        readonly>
                                    <span class="input-group-text p-1 fs-70" >đ</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Ghi chú thêm</label>
                                <textarea class="form-control" rows="2" wire:model.defer="formData.notes"></textarea>
                            </div>

                            <div class="col-12">
                                <hr class="my-1">
                                <label class="form-label fw-bold"><i class="fa-solid fa-file-pdf text-danger me-1"></i>FILE PDF BÁO GIÁ</label>

                                @if(count($editingFiles) > 0)
                                    <div class="d-flex flex-column gap-1 mb-2">
                                        @foreach($editingFiles as $ef)
                                        <div class="d-flex align-items-center gap-2 border rounded px-2 py-1">
                                            <i class="fa-solid fa-file-pdf text-danger"></i>
                                            <a href="{{ $ef['url'] }}" target="_blank" class="text-truncate flex-grow-1 small text-danger">{{ $ef['name'] }}</a>
                                            @can('quotation-tracking.edit')
                                            <button type="button" class="btn btn-outline-danger py-0 px-2" wire:click="deleteFile({{ $ef['id'] }})" wire:confirm="Xóa file này?">
                                                <i class="fa-solid fa-trash fs-5"></i>
                                            </button>
                                            @endcan
                                        </div>
                                        @endforeach
                                    </div>
                                @endif

                                @canany(['quotation-tracking.create', 'quotation-tracking.edit'])
                                <input type="file" class="form-control" wire:model="pdfFiles" accept=".pdf" multiple>
                                <div wire:loading wire:target="pdfFiles" class="text-primary mt-1 small">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Đang tải...
                                </div>
                                <div class="form-text">Chỉ chấp nhận file PDF, tối đa 50MB mỗi file. Có thể chọn nhiều file cùng lúc.</div>
                                @error('pdfFiles.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @endcanany
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

    <!-- Files Modal -->
    <div wire:ignore.self class="modal fade" id="filesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-3">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-file-pdf text-danger me-2"></i>FILE PDF BÁO GIÁ</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    @if(count($editingFiles) > 0)
                        <div class="d-flex flex-column gap-2 mb-3">
                            @foreach($editingFiles as $ef)
                            <div class="d-flex align-items-center gap-2 border rounded px-3 py-2">
                                <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                                <a href="{{ $ef['url'] }}" target="_blank" class="text-truncate flex-grow-1 small text-danger fw-semibold text-decoration-none">{{ $ef['name'] }}</a>
                                @can('quotation-tracking.edit')
                                <button type="button" class="btn btn-outline-danger py-0 px-2" wire:click="deleteFile({{ $ef['id'] }})" wire:confirm="Xóa file này?">
                                    <i class="fa-solid fa-trash fs-5"></i>
                                </button>
                                @endcan
                            </div>
                            @endforeach
                        </div>
                    @elseif(count($pdfFiles) === 0)
                        <p class="text-muted small mb-3">Chưa có file nào.</p>
                    @endif

                    @can('quotation-tracking.edit')
                    @if(count($pdfFiles) > 0)
                        <div class="d-flex flex-column gap-1 mb-3">
                            <p class="small fw-semibold text-secondary mb-1">Sắp lưu ({{ count($pdfFiles) }} file):</p>
                            @foreach($pdfFiles as $pf)
                            <div class="d-flex align-items-center gap-2 border border-primary rounded px-3 py-1 bg-light">
                                <i class="fa-solid fa-file-pdf text-primary"></i>
                                <span class="small text-truncate flex-grow-1">{{ $pf->getClientOriginalName() }}</span>
                            </div>
                            @endforeach
                        </div>
                    @endif

                    <label class="form-label fw-semibold small">Thêm file PDF</label>
                    <input type="file" class="form-control" wire:model="pdfFiles" accept=".pdf" multiple>
                    <div wire:loading wire:target="pdfFiles" class="text-primary mt-1 small">
                        <span class="spinner-border spinner-border-sm me-1"></span> Đang tải...
                    </div>
                    <div class="form-text">Tối đa 50MB mỗi file. Có thể chọn nhiều file cùng lúc.</div>
                    @error('pdfFiles.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    @endcan
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    @can('quotation-tracking.edit')
                    <button type="button" class="btn btn-primary" wire:click="saveFiles" wire:loading.attr="disabled" wire:target="saveFiles">
                        <span wire:loading wire:target="saveFiles" class="spinner-border spinner-border-sm me-2"></span>
                        Lưu file
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Convert Modal -->
    <div wire:ignore.self class="modal fade" id="convertModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-success py-3">
                    <h5 class="modal-title fw-bold text-white">Chọn loại Hợp đồng muốn tạo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="mb-4">Hệ thống sẽ chuyển dữ liệu từ báo giá này sang trang tạo hợp đồng mới.</p>
                    <div class="d-grid gap-3">
                        <button class="btn btn-outline-primary py-3 fw-bold" wire:click="convertTo('waste')">
                            <i class="fa-solid fa-trash3 me-2"></i> Chất thải
                        </button>
                        <button class="btn btn-outline-primary py-3 fw-bold" wire:click="convertTo('consulting')">
                            <i class="fa-solid fa-comment-dots me-2"></i> Hồ sơ môi trường
                        </button>
                        <button class="btn btn-outline-primary py-3 fw-bold" wire:click="convertTo('project')">
                            <i class="fa-solid fa-building me-2"></i> Ứng phó sự cố
                        </button>
                        <button class="btn btn-outline-primary py-3 fw-bold" wire:click="convertTo('commercial')">
                            <i class="fa-solid fa-cart-shopping me-2"></i> Nghiên cứu và chuyển đổi công nghệ
                        </button>
                        <button class="btn btn-outline-success py-3 fw-bold" wire:click="convertTo('sustainability')">
                            <i class="fa-solid fa-tree me-2"></i> Phát triển bền vững
                        </button>
                        <button class="btn btn-outline-warning py-3 fw-bold" wire:click="convertTo('energy')">
                            <i class="fa-solid fa-bolt me-2"></i> Giảm phát thải, tiết kiệm năng lượng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div wire:ignore.self class="modal fade" id="importModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-file-arrow-up me-2"></i>Import Báo giá từ Excel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetImport"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Errors --}}
                    @if($importErrors)
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            @foreach($importErrors as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Step 1: Upload --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">1. Chọn file Excel (.xlsx / .xls / .csv)</label>
                        <input type="file" class="form-control" wire:model="importFile" accept=".xlsx,.xls,.csv">
                        <div wire:loading wire:target="importFile" class="text-primary mt-1 ">
                            <span class="spinner-border spinner-border-sm me-1"></span> Đang đọc file...
                        </div>
                        <div class="form-text">Hàng đầu tiên của file sẽ được dùng làm tên cột. Hệ thống tự động nhận diện tên cột tiếng Việt phổ biến.</div>
                    </div>

                    {{-- Step 2: Column mapping --}}
                    @if(count($importHeaders) > 0)
                    <div class="mb-4">
                        <label class="form-label fw-bold">2. Kiểm tra & điều chỉnh mapping cột</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light" style="--bs-table-bg: #C5EECE; --bs-table-color: #000; background-color: #C5EECE;">
                                    <tr>
                                        <th class="w-50">Tên cột trong file</th>
                                        <th>Tương ứng với trường dữ liệu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($importColumnMap as $colIdx => $fieldKey)
                                    <tr>
                                        <td class="fw-semibold">{{ $importHeaders[array_search($colIdx, array_keys($importColumnMap))] ?? $colIdx }}</td>
                                        <td>
                                            <select class="form-select form-select-sm" wire:model.live="importColumnMap.{{ $colIdx }}">
                                                @foreach($availableFields as $val => $label)
                                                    <option value="{{ $val }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Step 3: Preview --}}
                    @if(count($importPreview) > 0)
                    <div class="mb-3">
                        <label class="form-label fw-bold">3. Xem trước dữ liệu (5 dòng đầu)</label>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-striped align-middle mb-0 fs-78" >
                                <thead class="table-dark" style="--bs-table-bg: #C5EECE; --bs-table-color: #000; background-color: #C5EECE;">
                                    <tr>
                                        @foreach($importColumnMap as $colIdx => $fieldKey)
                                        <th>{{ $availableFields[$fieldKey] ?? $importHeaders[array_search($colIdx, array_keys($importColumnMap))] ?? $colIdx }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($importPreview as $row)
                                    <tr>
                                        @foreach($importColumnMap as $colIdx => $fieldKey)
                                        <td>{{ $row[$colIdx] ?? '' }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
                <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetImport">Hủy</button>
                    @if(count($importHeaders) > 0)
                    <button type="button" class="btn btn-primary px-4 fw-bold" wire:click="runImport">
                        <span wire:loading wire:target="runImport" class="spinner-border spinner-border-sm me-2"></span>
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Thực hiện Import
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            let formModal = new bootstrap.Modal(document.getElementById('quotationModal'));
            let detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
            let convertModal = new bootstrap.Modal(document.getElementById('convertModal'));
            let importModal = new bootstrap.Modal(document.getElementById('importModal'));
            let filesModal = new bootstrap.Modal(document.getElementById('filesModal'));

            Livewire.on('open-quotation-modal', () => formModal.show());
            Livewire.on('close-quotation-modal', () => formModal.hide());
            Livewire.on('open-detail-modal', () => detailModal.show());
            Livewire.on('open-convert-modal', () => convertModal.show());
            Livewire.on('close-import-modal', () => importModal.hide());
            Livewire.on('open-files-modal', () => filesModal.show());
            Livewire.on('close-files-modal', () => filesModal.hide());
        });
    </script>
    @endpush
</div>
