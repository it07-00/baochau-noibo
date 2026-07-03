<div>
    <div class="page-header d-flex align-items-start align-items-sm-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h4 class="mb-0">Theo dõi Báo giá</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Theo dõi Báo giá</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 ms-auto flex-wrap justify-content-end align-items-center">
            @can('quotation-tracking.create')
            <button class="btn btn-success btn-sm d-flex align-items-center gap-1" wire:click="create">
                <i class="fa-solid fa-plus-lg"></i> Thêm mới
            </button>
            <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                    wire:click="resetImport"
                    data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-arrow-up"></i> Import Excel
            </button>
            @endcan
            <div class="input-group w-230px" >
                <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm công ty, ngành nghề..." wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label  fw-bold">Nhân viên sale</label>
                    <select class="form-select form-select-sm" wire:model.live="filter_staff">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label  fw-bold">Tình trạng</label>
                    <select class="form-select form-select-sm" wire:model.live="filter_status">
                        <option value="">Tất cả tình trạng</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label  fw-bold">Khoảng thời gian</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control form-control-sm" wire:model.live="date_from">
                        <input type="date" class="form-control form-control-sm" wire:model.live="date_to">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label  fw-bold">Sắp xếp</label>
                    <select class="form-select form-select-sm" wire:model.live="sortDirection">
                        <option value="desc">Từ trên xuống</option>
                        <option value="asc">Từ dưới lên</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end justify-content-end">
                    <button class="btn btn-sm btn-outline-secondary" wire:click="resetFilters">
                        <i class="fa-solid fa-xmark-circle"></i> Xóa lọc
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive overflow-auto" >
            <table class="table table-hover align-middle mb-0 table-sm" style="font-size: 0.85rem; min-width: 1400px;">
                <thead class="bg-light bg-opacity-50" style="--bs-table-bg: #C5EECE; --bs-table-color: #000; background-color: #C5EECE;">
                    <tr class="text-muted fw-bold">
                        <th class="ps-3 w-40px" >STT</th>
                        <th class="w-130px">Sale</th>
                        <th class="w-110px">Số báo giá</th>
                        <th class="text-truncate-220">Công ty / Khách hàng</th>
                        <th class="w-130px">Dịch vụ</th>
                        <th class="w-100px">Ngành nghề</th>
                        <th>Tình hình làm việc</th>
                        <th class="text-center w-130px" >Tình hình</th>
                        <th class="text-end w-110px" >Giá trị gốc</th>
                        <th class="text-end w-100px" >Hoa hồng KH</th>
                        <th class="text-end w-85px" >Thuế HH</th>
                        <th class="text-end fw-bold w-120px" >Giá trị HĐ</th>
                        @can('quotation-tracking.view')
                        <th class="text-center pe-3 w-110px" >#</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $index => $item)
                    <tr class="border-bottom border-light">
                        <td class="ps-3">{{ ($quotations->currentPage()-1) * $quotations->perPage() + $loop->iteration }}</td>
                        <td class=" w-130px" >
                            <div class="fw-semibold text-truncate"  title="{{ $item->staff?->name }}">{{ $item->staff?->name }}</div>
                            @if($item->source)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border mt-1 fs-68" >{{ $item->source }}</span>
                            @endif
                            <div class="text-muted mt-1 fs-75 text-nowrap" >{{ $item->date ? $item->date->format('d/m/Y') : '-' }}</div>
                        </td>
                        <td class="w-110px">
                            @if($item->quotation_number)
                            <span class="fw-semibold text-primary fs-82" >{{ $item->quotation_number }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                            @if($item->quotationDocuments->first())
                            <a href="{{ route('app.quotation-docs.export-pdf', $item->quotationDocuments->first()->id) }}" target="_blank" class="badge bg-success bg-opacity-10 text-success border mt-1 text-decoration-none d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-file-pdf"></i> Word/PDF
                            </a>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-primary text-capitalize fs-85 lh-sm" >{{ $item->company_name }}</div>
                            @if($item->contact_person)
                            <div class=" text-muted mt-1"><i class="fa-solid fa-user-circle me-1"></i>{{ $item->contact_person }}</div>
                            @endif
                            @if($item->province)
                            <span class="badge bg-info bg-opacity-10 text-info mt-1 fs-65" >{{ $item->province }}</span>
                            @endif
                        </td>
                        <td class=" text-wrap mxw-130px" >
                            <div class="line-clamp-2" title="{{ $item->service }}">
                                {{ $item->service ?: '-' }}
                            </div>
                        </td>
                        <td class="">
                            @if($item->industry)
                            <span class="badge bg-light text-dark border px-2 py-1 fs-70" >{{ $item->industry }}</span>
                            @else <span class="text-muted">-</span> @endif
                        </td>
                        <td class="text-wrap  text-truncate-200" >
                            <div class="line-clamp-3" title="{{ $item->work_description }}">
                                {{ $item->work_description ?: '-' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill {{ $this->statusBadgeClass($item->status) }} px-2 py-1 fs-72" >
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="text-end ">{{ $item->original_value ? number_format($item->original_value, 0, ',', '.') : '-' }}</td>
                        <td class="text-end ">{{ $item->commission_value ? number_format($item->commission_value, 0, ',', '.') : '-' }}</td>
                        <td class="text-end ">{{ $item->commission_tax ? number_format($item->commission_tax, 0, ',', '.') : '-' }}</td>
                        <td class="text-end fw-bold text-danger ">{{ $item->total_value ? number_format($item->total_value, 0, ',', '.') : '-' }}</td>
                        @can('quotation-tracking.view')
                        <td class="text-center pe-3">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm p-0 text-primary" wire:click="viewDetail({{ $item->id }})" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye fs-5"></i>
                                </button>
                                @if($item->pdf_path)
                                <a href="{{ \Illuminate\Support\Str::startsWith($item->pdf_path, ['http://', 'https://']) ? $item->pdf_path : Storage::disk(config('filesystems.upload_disk', 'public'))->url($item->pdf_path) }}" target="_blank" class="btn btn-sm p-0 text-danger" title="File PDF báo giá">
                                    <i class="fa-solid fa-file-pdf fs-5"></i>
                                </a>
                                @elseif($item->files_count > 0)
                                <button class="btn btn-sm p-0 text-danger position-relative" wire:click="openFiles({{ $item->id }})" title="{{ $item->files_count }} file PDF">
                                    <i class="fa-solid fa-file-pdf fs-5"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px;">{{ $item->files_count }}</span>
                                </button>
                                @elseif($item->quotationDocuments->first())
                                <a href="{{ route('app.quotation-docs.export-pdf', $item->quotationDocuments->first()->id) }}" target="_blank" class="btn btn-sm p-0 text-danger" title="Xem PDF báo giá tạo tự động">
                                    <i class="fa-solid fa-file-pdf fs-5"></i>
                                </a>
                                @else
                                @can('quotation-tracking.edit')
                                <button class="btn btn-sm p-0 text-secondary" wire:click="openFiles({{ $item->id }})" title="Tải lên PDF">
                                    <i class="fa-solid fa-file-pdf fs-5"></i>
                                </button>
                                @endcan
                                @endif
                                @if($item->quotationDocuments->first())
                                <a href="{{ route('app.quotation-docs.export-pdf', $item->quotationDocuments->first()->id) }}" target="_blank" class="btn btn-sm p-0 text-success" title="Mở báo giá Word/PDF gốc">
                                    <i class="fa-solid fa-file-word fs-5"></i>
                                </a>
                                @endif
                                @can('quotation-tracking.edit')
                                <button class="btn btn-sm p-0 text-success" wire:click="selectContractType({{ $item->id }})" title="Chuyển thành Hợp đồng">
                                    <i class="fa-solid fa-file-circle-plus fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-secondary" wire:click="duplicate({{ $item->id }})" title="Sao chép">
                                    <i class="fa-solid fa-copy fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-warning" wire:click="edit({{ $item->id }})" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-square fs-5"></i>
                                </button>
                                @endcan
                                @can('quotation-tracking.delete')
                                <button class="btn btn-sm p-0 text-danger"
                                        wire:click="delete({{ $item->id }})"
                                        wire:confirm="Xác nhận xóa báo giá này?">
                                    <i class="fa-solid fa-trash fs-5"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-5 text-muted">Không tìm thấy dữ liệu báo giá</td>
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
    <div wire:ignore.self class="modal fade" id="quotationModal" tabindex="-1" aria-hidden="true">
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
                                    <input type="text" class="form-control text-end money-input" wire:model.blur="formData.original_value">
                                    <span class="input-group-text p-1 fs-70" >đ</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Hoa hồng KH</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end money-input" wire:model.blur="formData.commission_value">
                                    <span class="input-group-text p-1 fs-70" >đ</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Thuế HH</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control text-end money-input {{ $this->isCommissionTaxManual() ? '' : 'bg-light' }}"
                                        wire:model.blur="formData.commission_tax"
                                        @readonly(!$this->isCommissionTaxManual())>
                                    <span class="input-group-text p-1 fs-70" >đ</span>
                                </div>
                                @unless($this->isCommissionTaxManual())
                                    <small class="text-muted">Tự tính 20%–30%</small>
                                @endunless
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Giá trị chưa VAT</label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control text-end fw-bold bg-light money-input"
                                        value="{{ number_format((float) ($formData['value_inc_vat'] ?? 0), 0, ',', '.') }}"
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
                                        value="{{ number_format((float) ($formData['total_value'] ?? 0), 0, ',', '.') }}"
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
