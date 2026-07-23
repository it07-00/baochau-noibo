<div>
    <header class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mt-2 mb-4">
        <div class="d-flex align-items-start gap-3">
            <span class="d-none d-sm-inline-flex align-items-center justify-content-center rounded-3 text-bg-primary p-3 shadow-sm" aria-hidden="true">
                <i class="fa-solid fa-file-invoice-dollar fa-lg"></i>
            </span>
            <div>
                <h1 class="h4 fw-bold mb-1 text-body">Theo dõi báo giá</h1>
                <p class="text-secondary-emphasis mb-0">Theo dõi khách hàng, tiến độ, giá trị và tài liệu của từng báo giá.</p>
            </div>
        </div>
        @can('quotation-tracking.create')
        <div class="d-flex flex-column flex-sm-row gap-2">
            <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center gap-2" wire:click="resetImport" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i>
                <span>Import Excel</span>
            </button>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2" wire:click="create" wire:loading.attr="disabled" wire:target="create">
                <span wire:loading.remove wire:target="create"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
                <span wire:loading wire:target="create" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                <span>Thêm báo giá</span>
            </button>
        </div>
        @endcan
    </header>

    <section class="card border shadow-sm mb-4" aria-labelledby="quotation-filter-heading">
        <div class="card-header bg-body border-bottom py-3 px-3 px-lg-4">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-2 text-bg-primary p-2" aria-hidden="true">
                        <i class="fa-solid fa-sliders"></i>
                    </span>
                    <div>
                        <h2 id="quotation-filter-heading" class="h6 fw-bold text-body mb-1">Bộ lọc báo giá</h2>
                        <p class="small text-secondary-emphasis mb-0">Tìm nhanh báo giá theo khách hàng, nhân viên và thời gian.</p>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap" wire:click="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters">
                    <i class="fa-solid fa-rotate-left me-1" aria-hidden="true"></i>Đặt lại
                </button>
            </div>
        </div>
        <div class="card-body p-3 p-lg-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-xl-3">
                    <label for="quotation-search" class="form-label fw-semibold small">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary text-primary"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                        <input id="quotation-search" type="search" class="form-control" placeholder="Công ty, số báo giá, dịch vụ..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label for="quotation-staff" class="form-label fw-semibold small">Nhân viên sale</label>
                    <select id="quotation-staff" class="form-select" wire:model.live="filter_staff">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label for="quotation-status" class="form-label fw-semibold small">Tình hình</label>
                    <select id="quotation-status" class="form-select" wire:model.live="filter_status">
                        <option value="">Tất cả tình hình</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-8 col-xl-3">
                    <label class="form-label fw-semibold small">Khoảng thời gian</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="quotation-date-from" class="visually-hidden">Từ ngày</label>
                            <input id="quotation-date-from" type="date" class="form-control" wire:model.live="date_from" title="Từ ngày">
                        </div>
                        <div class="col-6">
                            <label for="quotation-date-to" class="visually-hidden">Đến ngày</label>
                            <input id="quotation-date-to" type="date" class="form-control" wire:model.live="date_to" title="Đến ngày">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <label for="quotation-sort" class="form-label fw-semibold small">Sắp xếp</label>
                    <select id="quotation-sort" class="form-select" wire:model.live="sortDirection">
                        <option value="desc">Mới nhất trước</option>
                        <option value="asc">Cũ nhất trước</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <section class="card border shadow-sm overflow-hidden" aria-labelledby="quotation-list-heading">
        <div class="card-header bg-body border-bottom px-3 px-lg-4 py-3 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary p-2" aria-hidden="true">
                    <i class="fa-solid fa-table-list"></i>
                </span>
                <div>
                    <h2 id="quotation-list-heading" class="h6 fw-bold text-body mb-1">Danh sách báo giá</h2>
                    <p class="small text-secondary-emphasis mb-0">Kết quả theo bộ lọc hiện tại</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-primary rounded-pill px-3 py-2">{{ number_format($quotations->total(), 0, ',', '.') }} báo giá</span>
                <div wire:loading.flex wire:target="search,filter_staff,filter_status,date_from,date_to,sortDirection,resetFilters" class="align-items-center gap-2 small text-primary" role="status">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <span>Đang cập nhật...</span>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr class="text-nowrap">
                        <th scope="col" class="ps-3 ps-lg-4 py-3 text-center">STT</th>
                        <th scope="col" class="py-3">Báo giá</th>
                        <th scope="col" class="py-3">Khách hàng &amp; dịch vụ</th>
                        <th scope="col" class="py-3">Tiến độ làm việc</th>
                        <th scope="col" class="py-3 text-center">Tình hình</th>
                        <th scope="col" class="py-3 text-end">Giá trị HĐ</th>
                        @can('quotation-tracking.view')
                        <th scope="col" class="py-3 pe-3 pe-lg-4 text-end">Thao tác</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @forelse($quotations as $item)
                    <tr wire:key="quotation-row-{{ $item->id }}">
                        <td class="ps-3 ps-lg-4 py-3 text-center text-secondary-emphasis fw-semibold">{{ ($quotations->currentPage() - 1) * $quotations->perPage() + $loop->iteration }}</td>
                        <td class="py-3">
                            <div class="fw-semibold text-body text-nowrap">{{ $item->quotation_number ?: 'Chưa có số' }}</div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1 small text-secondary-emphasis fw-medium">
                                <span class="text-nowrap"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>{{ $item->date?->format('d/m/Y') ?: '-' }}</span>
                                <span class="text-nowrap"><i class="fa-regular fa-user me-1" aria-hidden="true"></i>{{ $item->staff?->name ?: '-' }}</span>
                                @if($item->source)
                                <span class="badge text-bg-light border fw-normal">{{ $item->source }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3">
                            <button type="button" class="btn btn-link p-0 fw-semibold text-start text-decoration-none" wire:click="viewDetail({{ $item->id }})">
                                {{ $item->company_name }}
                            </button>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @if($item->service)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fw-normal">{{ \Illuminate\Support\Str::limit($item->service, 38) }}</span>
                                @endif
                                @if($item->contact_person)
                                <span class="small text-secondary-emphasis fw-medium"><i class="fa-regular fa-address-card me-1" aria-hidden="true"></i>{{ $item->contact_person }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 text-body fw-medium">
                            <span title="{{ $item->work_description }}">{{ \Illuminate\Support\Str::limit($item->work_description ?: 'Chưa cập nhật', 70) }}</span>
                        </td>
                        <td class="py-3 text-center">
                            @can('quotation-tracking.edit')
                            <div class="dropdown d-inline-block" wire:key="status-dropdown-{{ $item->id }}">
                                <button id="quotation-status-btn-{{ $item->id }}"
                                        type="button"
                                        class="btn btn-sm dropdown-toggle fw-bold text-nowrap {{ $this->statusButtonClass($item->status) }}"
                                        data-bs-toggle="dropdown"
                                        data-bs-boundary="viewport"
                                        aria-expanded="false">
                                    {{ $item->status }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="quotation-status-btn-{{ $item->id }}">
                                    @foreach($statuses as $st)
                                    <li wire:key="status-opt-{{ $item->id }}-{{ $loop->index }}">
                                        <button type="button"
                                                class="dropdown-item d-flex align-items-center justify-content-between gap-3 {{ $item->status === $st ? 'active' : '' }}"
                                                wire:click="updateStatus({{ $item->id }}, '{{ addslashes($st) }}')">
                                            <span>{{ $st }}</span>
                                            @if($item->status === $st)<i class="fa-solid fa-check" aria-hidden="true"></i>@endif
                                        </button>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @else
                            <span class="badge rounded-pill {{ $this->statusBadgeClass($item->status) }} border px-3 py-2">{{ $item->status }}</span>
                            @endcan
                        </td>
                        <td class="py-3 text-end text-nowrap">
                            <span class="fw-bold text-body">{{ $item->total_value ? number_format($item->total_value, 0, ',', '.') : '-' }}</span>
                            @if($item->total_value)<span class="small text-secondary-emphasis">đ</span>@endif
                        </td>
                        @can('quotation-tracking.view')
                        <td class="py-3 pe-3 pe-lg-4 text-end text-nowrap">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="viewDetail({{ $item->id }})" wire:loading.attr="disabled" wire:target="viewDetail({{ $item->id }})">
                                    <i class="fa-regular fa-eye me-1" aria-hidden="true"></i>Xem
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                    <span class="visually-hidden">Mở thêm thao tác</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    @if($item->quotationDocuments->first())
                                    <li><a class="dropdown-item" href="{{ route('app.quotation-docs.export-pdf', $item->quotationDocuments->first()->id) }}" target="_blank"><i class="fa-regular fa-file-pdf me-2 text-danger" aria-hidden="true"></i>Xuất PDF báo giá</a></li>
                                    <li><a class="dropdown-item" href="{{ route('app.quotation-docs.export-word', $item->quotationDocuments->first()->id) }}" target="_blank"><i class="fa-regular fa-file-word me-2 text-primary" aria-hidden="true"></i>Xuất Word báo giá</a></li>
                                    @endif
                                    @if($item->pdf_path)
                                    <li><a class="dropdown-item" href="{{ \Illuminate\Support\Str::startsWith($item->pdf_path, ['http://', 'https://']) ? $item->pdf_path : Storage::disk(config('filesystems.upload_disk', 'public'))->url($item->pdf_path) }}" target="_blank"><i class="fa-regular fa-file-pdf me-2 text-danger" aria-hidden="true"></i>Mở file PDF</a></li>
                                    @endif
                                    <li><button type="button" class="dropdown-item" wire:click="openFiles({{ $item->id }})"><i class="fa-solid fa-paperclip me-2 text-body-secondary" aria-hidden="true"></i>File đính kèm @if($item->files_count)({{ $item->files_count }})@endif</button></li>
                                    @can('quotation-tracking.create')
                                    <li><button type="button" class="dropdown-item" wire:click="duplicate({{ $item->id }})"><i class="fa-regular fa-copy me-2 text-warning" aria-hidden="true"></i>Sao chép báo giá</button></li>
                                    @endcan
                                    @can('quotation-tracking.edit')
                                    <li><button type="button" class="dropdown-item" wire:click="edit({{ $item->id }})"><i class="fa-regular fa-pen-to-square me-2 text-primary" aria-hidden="true"></i>Chỉnh sửa</button></li>
                                    <li><button type="button" class="dropdown-item" wire:click="selectContractType({{ $item->id }})"><i class="fa-solid fa-arrow-right-arrow-left me-2 text-success" aria-hidden="true"></i>Chuyển thành hợp đồng</button></li>
                                    @endcan
                                    @can('quotation-tracking.delete')
                                    <li><hr class="dropdown-divider"></li>
                                    <li><button type="button" class="dropdown-item text-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xác nhận xóa báo giá này?"><i class="fa-regular fa-trash-can me-2" aria-hidden="true"></i>Xóa báo giá</button></li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center px-3 py-5">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-body-tertiary text-body-secondary p-3 mb-3" aria-hidden="true"><i class="fa-solid fa-magnifying-glass fa-lg"></i></div>
                            <h3 class="h6 fw-bold mb-1">Không tìm thấy báo giá</h3>
                            <p class="text-body-secondary mb-3">Thử thay đổi từ khóa hoặc đặt lại bộ lọc để xem toàn bộ dữ liệu.</p>
                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="resetFilters">Đặt lại bộ lọc</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
        <div class="px-3 px-lg-4 py-3 border-top">{{ $quotations->links('livewire.admin.users.pagination') }}</div>
        @endif
    </section>

    <!-- Detail Modal -->
    <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title fw-bold text-white">Thông tin Báo giá Chi tiết</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-0 overflow-auto">
                    @if($selectedQuotation)
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Nhân viên sale</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->staff?->name }} ({{ $selectedQuotation->date?->format('d/m/Y') }})</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Số báo giá</th>
                                    <td class="px-4 py-3 fw-semibold text-primary">{{ $selectedQuotation->quotation_number ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Nguồn</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->source ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Công ty / Khách hàng</th>
                                    <td class="px-4 py-3 fw-bold text-primary text-capitalize">{{ $selectedQuotation->company_name }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Địa chỉ XHĐ</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->address ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Địa chỉ làm việc</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->work_address ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Tỉnh thành</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->province ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Dịch vụ</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->service ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Ngành nghề</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->industry ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Khách hàng</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->contact_person ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Tình hình làm việc</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->work_description ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Tình hình</th>
                                    <td class="px-4 py-3">
                                        <span class="badge rounded-pill {{ $this->statusBadgeClass($selectedQuotation->status) }} border px-3 py-2">{{ $selectedQuotation->status }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3">Ghi chú</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->notes ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3 text-danger">Giá trị gốc</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->original_value, 0, ',', '.') }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3 text-danger">Hoa hồng KH</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->commission_value, 0, ',', '.') }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3 text-danger">Thuế HH</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->commission_tax, 0, ',', '.') }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3 text-danger">Giá trị HĐ (có VAT)</th>
                                    <td class="px-4 py-3 fw-bold text-danger fs-5">{{ number_format($selectedQuotation->total_value, 0, ',', '.') }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary fw-bold px-4 py-3"><i class="fa-solid fa-file-pdf text-danger me-1"></i>FILE BÁO GIÁ</th>
                                    <td class="px-4 py-3">
                                        @if($selectedQuotation->quotationDocuments->first())
                                             <div class="d-flex flex-wrap gap-2 mb-2">
                                                 <a href="{{ route('app.quotation-docs.export-pdf', $selectedQuotation->quotationDocuments->first()->id) }}" target="_blank" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                                                     <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                                                     <span>Xuất PDF: {{ $selectedQuotation->quotationDocuments->first()->document_number }}</span>
                                                 </a>
                                                 <a href="{{ route('app.quotation-docs.export-word', $selectedQuotation->quotationDocuments->first()->id) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                                     <i class="fa-solid fa-file-word" aria-hidden="true"></i>
                                                     <span>Xuất Word</span>
                                                 </a>
                                             </div>
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
              commission_tax_manual: @entangle('commissionTaxManual').live,
              commission_tax_rate: @entangle('commissionTaxRate').live,
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

             parsePercentage(val) {
                 let percentage = Number(String(val ?? 0).replace(',', '.'));
                 return Number.isFinite(percentage) ? Math.min(Math.max(percentage, 0), 100) : 0;
             },

              isCommissionTaxManual() {
                  return this.commission_tax_manual || this.parseMoney(this.commission_value) > 5000000;
              },

             recalculate() {
                 let orig = this.parseMoney(this.original_value);
                  let comm = this.parseMoney(this.commission_value);
                  let tax = 0;

                  if (comm > 5000000) {
                      this.commission_tax_manual = true;
                  }

                  if (this.isCommissionTaxManual()) {
                     tax = Math.round(comm * this.parsePercentage(this.commission_tax_rate) / 100);
                     this.commission_tax = tax;
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
              $watch('commission_tax_manual', () => recalculate());
              $watch('commission_tax_rate', () => recalculate());
              recalculate();
         ">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form wire:submit.prevent="save" class="modal-content overflow-hidden border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-invoice fs-5"></i>
                        <span>
                            @if($isEditing) Cập nhật Báo giá
                            @elseif($isDuplicating) Sao chép Báo giá
                            @else Tạo Báo giá mới
                            @endif
                        </span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-body-tertiary overflow-auto">

                        <div class="d-flex flex-column gap-4">

                            {{-- SECTION 1: THÔNG TIN BÁO GIÁ & SALE --}}
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header bg-body border-bottom py-3 px-3 d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center p-2">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold text-dark">1. Thông tin chung &amp; Sale phụ trách</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small">Ngày báo giá <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" wire:model.defer="formData.date">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small">Ngày dự kiến ký HĐ</label>
                                            <input type="date" class="form-control @error('formData.expected_signing_date') is-invalid @enderror" wire:model.defer="formData.expected_signing_date">
                                            @error('formData.expected_signing_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold small">Số báo giá</label>
                                            <input type="text" class="form-control @error('formData.quotation_number') is-invalid @enderror" wire:model.defer="formData.quotation_number" placeholder="VD: BG2026-001">
                                            @error('formData.quotation_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold small">Nguồn</label>
                                            <select class="form-select" wire:model.defer="formData.source">
                                                <option value="">-- Chọn nguồn --</option>
                                                @foreach($sources as $source)
                                                    <option value="{{ $source }}">{{ $source }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold small">Nhân viên sale <span class="text-danger">*</span></label>
                                            <select class="form-select @error('formData.staff_id') is-invalid @enderror" wire:model.defer="formData.staff_id">
                                                <option value="">Chọn nhân viên</option>
                                                @foreach($staffs as $s)
                                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 2: THÔNG TIN KHÁCH HÀNG & CÔNG TY --}}
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header bg-body border-bottom py-3 px-3 d-flex align-items-center gap-2">
                                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center p-2">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold text-dark">2. Thông tin Khách hàng &amp; Công ty</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">Tên công ty <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('formData.company_name') is-invalid @enderror" wire:model.defer="formData.company_name" placeholder="Tên công ty niêm yết">
                                            @error('formData.company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small">Tỉnh thành</label>
                                            <select class="form-select" wire:model.defer="formData.province">
                                                <option value="">-- Chọn tỉnh/TP --</option>
                                                @foreach(\App\Support\VietnamProvinces::list() as $p)
                                                    <option value="{{ $p }}">{{ $p }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small">Người đại diện / Khách hàng</label>
                                            <input type="text" class="form-control" wire:model.defer="formData.contact_person" placeholder="Họ tên người liên hệ">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold small">Ngành nghề</label>
                                            <input type="text" class="form-control" wire:model.defer="formData.industry" placeholder="Lĩnh vực HĐ">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Địa chỉ xuất hóa đơn (XHĐ)</label>
                                            <input type="text" class="form-control" wire:model.defer="formData.address" placeholder="Địa chỉ theo ĐKKD">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Địa chỉ làm việc / Thực địa</label>
                                            <input type="text" class="form-control" wire:model.defer="formData.work_address" placeholder="Địa chỉ thi công / cung cấp dịch vụ">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 3: DỊCH VỤ & NỘI DUNG BÁO GIÁ --}}
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header bg-body border-bottom py-3 px-3 d-flex align-items-center gap-2">
                                    <div class="bg-warning bg-opacity-10 text-warning-emphasis rounded-circle d-inline-flex align-items-center justify-content-center p-2">
                                        <i class="fa-solid fa-list-check"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold text-dark">3. Dịch vụ &amp; Tình hình thực hiện</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Dịch vụ cung cấp</label>
                                            <input type="text" class="form-control" wire:model.defer="formData.service" placeholder="VD: Báo cáo giám sát môi trường, Xử lý chất thải...">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Tình hình / Trạng thái báo giá <span class="text-danger">*</span></label>
                                            <select class="form-select fw-semibold" wire:model.defer="formData.status">
                                                @foreach($statuses as $st)
                                                    <option value="{{ $st }}">{{ $st }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Tình hình làm việc / Nội dung chi tiết báo giá</label>
                                            <textarea class="form-control" rows="3" wire:model.defer="formData.work_description" placeholder="Mô tả tiến độ trao đổi, yêu cầu cụ thể của báo giá..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 4: GIÁ TRỊ TÀI CHÍNH & HOA HỒNG --}}
                            <div class="card border-0 shadow-sm rounded-3 bg-success bg-opacity-10 border-success-subtle">
                                <div class="card-header bg-body border-bottom py-3 px-3 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center p-2">
                                            <i class="fa-solid fa-calculator"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-success-emphasis">4. Giá trị tài chính &amp; Hoa hồng (Tự động tính toán)</h6>
                                    </div>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-2 py-1 rounded-pill">Tự động tính</span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold text-dark small mb-1">Giá trị gốc</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control text-end fw-semibold"
                                                       x-model="original_value"
                                                       wire:model.blur="formData.original_value">
                                                <span class="input-group-text bg-body-tertiary text-body-secondary px-2">đ</span>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label fw-bold text-dark small mb-1">Hoa hồng KH</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control text-end fw-semibold"
                                                       x-model="commission_value"
                                                       wire:model.blur="formData.commission_value">
                                                <span class="input-group-text bg-body-tertiary text-body-secondary px-2">đ</span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                                                <label class="form-label fw-bold text-dark small mb-0">Thuế HH</label>
                                                <div class="form-check form-check-inline me-0 mb-0">
                                                    <input
                                                        id="commission-tax-manual"
                                                        type="checkbox"
                                                        class="form-check-input"
                                                        x-model="commission_tax_manual"
                                                        x-bind:disabled="parseMoney(commission_value) > 5000000">
                                                    <label class="form-check-label small text-nowrap" for="commission-tax-manual">
                                                        Tự nhập
                                                    </label>
                                                </div>
                                            </div>
                                            {{-- Tự động tính --}}
                                            <div class="input-group" x-show="!isCommissionTaxManual()">
                                                <input type="text"
                                                       class="form-control text-end bg-body-tertiary"
                                                       x-bind:value="formatMoney(commission_tax)"
                                                       readonly>
                                                <span class="input-group-text bg-body-tertiary text-body-secondary px-2">đ</span>
                                            </div>
                                            <small class="text-body-secondary d-block mt-1" x-show="!isCommissionTaxManual()">Tự tính 20%–30%</small>

                                            {{-- Tự nhập % --}}
                                            <div class="input-group" x-show="isCommissionTaxManual()">
                                                <input type="number"
                                                       class="form-control text-end border-light-subtle"
                                                       min="0"
                                                       max="100"
                                                       step="0.01"
                                                       x-model.number="commission_tax_rate">
                                                <span class="input-group-text bg-body-tertiary text-body-secondary px-2">%</span>
                                            </div>
                                            <small class="text-body-secondary d-block mt-1" x-show="isCommissionTaxManual()">
                                                Thuế: <span x-text="formatMoney(commission_tax)"></span>đ
                                            </small>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label fw-bold text-dark small mb-1">Giá trị chưa VAT</label>
                                            <div class="input-group">
                                                <input
                                                    type="text"
                                                    class="form-control text-end fw-bold bg-body text-primary"
                                                    x-bind:value="formatMoney(value_inc_vat)"
                                                    readonly>
                                                <span class="input-group-text bg-body-tertiary text-body-secondary px-2">đ</span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-danger small mb-1">Giá trị HĐ (có VAT)</label>
                                            <div class="input-group shadow-sm">
                                                <input
                                                    type="text"
                                                    class="form-control text-end fw-bold text-danger bg-body border-danger-subtle"
                                                    x-bind:value="formatMoney(total_value)"
                                                    readonly>
                                                <span class="input-group-text bg-danger bg-opacity-10 text-danger border-danger-subtle px-2 fw-bold">đ</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 5: FILE ĐÍNH KÈM & GHI CHÚ --}}
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header bg-body border-bottom py-3 px-3 d-flex align-items-center gap-2">
                                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-inline-flex align-items-center justify-content-center p-2">
                                        <i class="fa-solid fa-paperclip"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold text-dark">5. File PDF đính kèm &amp; Ghi chú thêm</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small d-flex align-items-center gap-1">
                                                <i class="fa-solid fa-file-pdf text-danger"></i>
                                                <span>File PDF Báo giá</span>
                                            </label>

                                            @if(count($editingFiles) > 0)
                                                <div class="d-flex flex-column gap-2 mb-2">
                                                    @foreach($editingFiles as $ef)
                                                    <div class="d-flex align-items-center justify-content-between border rounded-2 px-3 py-2 bg-body-tertiary">
                                                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                            <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                                                            <a href="{{ $ef['url'] }}" target="_blank" class="text-truncate small text-danger fw-semibold text-decoration-none">{{ $ef['name'] }}</a>
                                                        </div>
                                                        @can('quotation-tracking.edit')
                                                        <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 d-inline-flex align-items-center justify-content-center gap-1" wire:click="deleteFile({{ $ef['id'] }})" wire:confirm="Xóa file này?">
                                                            <i class="fa-solid fa-trash"></i>
                                                            <span>Xóa</span>
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
                                            <div class="form-text">Chấp nhận file PDF, tối đa 50MB mỗi file. Có thể chọn nhiều file cùng lúc.</div>
                                            @error('pdfFiles.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            @endcanany
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Ghi chú bổ sung</label>
                                            <textarea class="form-control" rows="2" wire:model.defer="formData.notes" placeholder="Ghi chú nội bộ hoặc điều khoản đặc biệt..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer bg-body border-top px-4 py-3">
                        <button type="button" class="btn btn-light border rounded-2 px-4 fw-semibold" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-primary rounded-2 px-4 fw-bold d-inline-flex align-items-center justify-content-center gap-2" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Lưu báo giá</span>
                        </button>
                    </div>
            </form>
        </div>
    </div>

    <!-- Files Modal -->
    <div wire:ignore.self class="modal fade" id="filesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-3">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-file-pdf text-danger me-2"></i>FILE PDF BÁO GIÁ</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
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
                            <div class="d-flex align-items-center gap-2 border border-primary rounded px-3 py-1 bg-primary bg-opacity-10">
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
                <div class="modal-footer bg-body-tertiary px-4 py-3">
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetImport" aria-label="Đóng"></button>
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
                                <thead class="table-light">
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
                            <table class="table table-sm table-striped align-middle mb-0">
                                <thead class="table-light">
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
                <div class="modal-footer bg-body-tertiary px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetImport">Hủy</button>
                    @if(count($importHeaders) > 0)
                    <button type="button" class="btn btn-primary px-4 fw-bold" wire:click="runImport" wire:loading.attr="disabled" wire:target="runImport">
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
