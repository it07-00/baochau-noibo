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
            <button class="btn btn-success btn-sm d-flex align-items-center gap-1" wire:click="create">
                <i class="bi bi-plus-lg"></i> Thêm mới
            </button>
            <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                    wire:click="resetImport"
                    data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-arrow-up"></i> Import Excel
            </button>
            <div class="input-group" style="width: 230px;">
                <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm công ty, ngành nghề..." wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
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
                        <i class="bi bi-x-circle"></i> Xóa lọc
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table table-hover align-middle mb-0 table-sm" style="font-size: 0.85rem; min-width: 1400px;">
                <thead class="bg-light bg-opacity-50">
                    <tr class="text-muted fw-bold">
                        <th class="ps-3" style="width: 40px;">STT</th>
                        <th style="width: 130px;">Sale</th>
                        <th style="width: 220px;">Công ty / Khách hàng</th>
                        <th style="width: 130px;">Dịch vụ</th>
                        <th style="width: 100px;">Ngành nghề</th>
                        <th>Tình hình làm việc</th>
                        <th class="text-center" style="width: 130px;">Tình hình</th>
                        <th class="text-end" style="width: 110px;">Giá trị gốc</th>
                        <th class="text-end" style="width: 100px;">Hoa hồng KH</th>
                        <th class="text-end" style="width: 85px;">Thuế HH</th>
                        <th class="text-end fw-bold" style="width: 120px;">Giá trị HĐ</th>
                        <th class="text-center pe-3" style="width: 80px;">#</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $index => $item)
                    <tr class="border-bottom border-light">
                        <td class="ps-3">{{ ($quotations->currentPage()-1) * $quotations->perPage() + $loop->iteration }}</td>
                        <td class="" style="width: 130px;">
                            <div class="fw-semibold" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $item->staff?->name }}">{{ $item->staff?->name }}</div>
                            @if($item->source)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border mt-1" style="font-size: 0.68rem;">{{ $item->source }}</span>
                            @endif
                            <div class="text-muted mt-1" style="font-size: 0.75rem; white-space: nowrap;">{{ $item->date ? $item->date->format('d/m/Y') : '-' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-primary text-capitalize" style="font-size: 0.85rem; line-height: 1.3;">{{ $item->company_name }}</div>
                            @if($item->contact_person)
                            <div class=" text-muted mt-1"><i class="bi bi-person-circle me-1"></i>{{ $item->contact_person }}</div>
                            @endif
                            @if($item->province)
                            <span class="badge bg-info bg-opacity-10 text-info mt-1" style="font-size: 0.65rem;">{{ $item->province }}</span>
                            @endif
                        </td>
                        <td class=" text-wrap" style="max-width: 130px;">
                            <div style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $item->service }}">
                                {{ $item->service ?: '-' }}
                            </div>
                        </td>
                        <td class="">
                            @if($item->industry)
                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.7rem;">{{ $item->industry }}</span>
                            @else <span class="text-muted">-</span> @endif
                        </td>
                        <td class="text-wrap " style="max-width: 200px;">
                            <div style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $item->work_description }}">
                                {{ $item->work_description ?: '-' }}
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $colorClass = match($item->status) {
                                    'hẹn báo giá thời gian sau' => 'bg-info bg-opacity-10 text-info',
                                    'Đang theo dõi' => 'bg-success bg-opacity-10 text-success',
                                    'Rớt báo giá' => 'bg-dark bg-opacity-10 text-dark',
                                    'Ký hợp đồng' => 'bg-danger bg-opacity-10 text-danger',
                                    'Tham khảo' => 'bg-warning bg-opacity-10 text-warning',
                                    default => 'bg-secondary bg-opacity-10 text-secondary'
                                };
                            @endphp
                            <span class="badge rounded-pill {{ $colorClass }} px-2 py-1" style="font-size: 0.72rem;">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="text-end ">{{ $item->original_value ? number_format($item->original_value) : '-' }}</td>
                        <td class="text-end ">{{ $item->commission_value ? number_format($item->commission_value) : '-' }}</td>
                        <td class="text-end ">{{ $item->commission_tax ? number_format($item->commission_tax) : '-' }}</td>
                        <td class="text-end fw-bold text-danger ">{{ $item->total_value ? number_format($item->total_value) : '-' }}</td>
                        <td class="text-center pe-3">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm p-0 text-success" wire:click="selectContractType({{ $item->id }})" title="Chuyển thành Hợp đồng">
                                    <i class="bi bi-file-earmark-plus fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-primary" wire:click="viewDetail({{ $item->id }})">
                                    <i class="bi bi-eye fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-warning" wire:click="edit({{ $item->id }})">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-danger"
                                        wire:click="delete({{ $item->id }})"
                                        wire:confirm="Xác nhận xóa báo giá này?">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
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
                                    <th class="bg-light fw-bold px-4 py-3" style="width: 30%;">Nhân viên sale</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->staff?->name }} ({{ $selectedQuotation->date?->format('d/m/Y') }})</td>
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
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->original_value) }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Hoa hồng KH</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->commission_value) }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Thuế HH</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->commission_tax) }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Giá trị HĐ (có VAT)</th>
                                    <td class="px-4 py-3 fw-bold text-danger fs-5">{{ number_format($selectedQuotation->total_value) }}đ</td>
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
                    <h5 class="modal-title fw-bold text-white">{{ $isEditing ? 'Cập nhật Báo giá' : 'Thêm Báo giá mới' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ngày <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model.defer="formData.date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Nhân viên sale <span class="text-danger">*</span></label>
                                <select class="form-select @error('formData.staff_id') is-invalid @enderror" wire:model.defer="formData.staff_id">
                                    <option value="">Chọn nhân viên</option>
                                    @foreach($staffs as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Công ty <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('formData.company_name') is-invalid @enderror" wire:model.defer="formData.company_name" placeholder="Tên công ty niêm yết">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Nguồn</label>
                                <input type="text" class="form-control" wire:model.defer="formData.source" placeholder="VD: Referral, Zalo...">
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
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Địa chỉ xuất hóa đơn (XHĐ)</label>
                                <input type="text" class="form-control" wire:model.defer="formData.address">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Địa chỉ làm việc</label>
                                <input type="text" class="form-control" wire:model.defer="formData.work_address">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Dịch vụ</label>
                                <input type="text" class="form-control" wire:model.defer="formData.service" placeholder="VD: Xử lý chất thải...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Ngành nghề</label>
                                <input type="text" class="form-control" wire:model.defer="formData.industry">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Khách hàng</label>
                                <input type="text" class="form-control" wire:model.defer="formData.contact_person">
                            </div>
                            <div class="col-md-4">
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
                                    <input type="text" class="form-control text-end money-input" wire:model.live.debounce.500ms="formData.original_value">
                                    <span class="input-group-text p-1" style="font-size: 0.7rem;">đ</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Hoa hồng KH</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end money-input" wire:model.live.debounce.500ms="formData.commission_value">
                                    <span class="input-group-text p-1" style="font-size: 0.7rem;">đ</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Thuế HH</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end money-input" wire:model.live.debounce.500ms="formData.commission_tax">
                                    <span class="input-group-text p-1" style="font-size: 0.7rem;">đ</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Giá trị chưa VAT</label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control text-end fw-bold bg-light money-input"
                                        value="{{ number_format((float) ($formData['value_inc_vat'] ?? 0), 0, ',', '.') }}"
                                        readonly>
                                    <span class="input-group-text p-1" style="font-size: 0.7rem;">đ</span>
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
                                    <span class="input-group-text p-1" style="font-size: 0.7rem;">đ</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Ghi chú thêm</label>
                                <textarea class="form-control" rows="2" wire:model.defer="formData.notes"></textarea>
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
                            <i class="bi bi-trash3 me-2"></i> Chất thải & Tiếng ồn
                        </button>
                        <button class="btn btn-outline-primary py-3 fw-bold" wire:click="convertTo('consulting')">
                            <i class="bi bi-chat-dots me-2"></i> Hồ sơ môi trường
                        </button>
                        <button class="btn btn-outline-primary py-3 fw-bold" wire:click="convertTo('project')">
                            <i class="bi bi-building me-2"></i> Kỹ thuật & Ứng phó SC
                        </button>
                        <button class="btn btn-outline-primary py-3 fw-bold" wire:click="convertTo('commercial')">
                            <i class="bi bi-cart3 me-2"></i> NC & CĐ Công nghệ
                        </button>
                        <button class="btn btn-outline-success py-3 fw-bold" wire:click="convertTo('sustainability')">
                            <i class="bi bi-tree me-2"></i> TV & BC PTBV
                        </button>
                        <button class="btn btn-outline-warning py-3 fw-bold" wire:click="convertTo('energy')">
                            <i class="bi bi-lightning me-2"></i> Phát thải & Năng lượng
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
                    <h5 class="modal-title fw-bold text-white"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Báo giá từ Excel</h5>
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
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50%">Tên cột trong file</th>
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
                            <table class="table table-sm table-striped align-middle mb-0" style="font-size: 0.78rem;">
                                <thead class="table-dark">
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
                        <i class="bi bi-cloud-upload me-1"></i> Thực hiện Import
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

            Livewire.on('open-quotation-modal', () => formModal.show());
            Livewire.on('close-quotation-modal', () => formModal.hide());
            Livewire.on('open-detail-modal', () => detailModal.show());
            Livewire.on('open-convert-modal', () => convertModal.show());
            Livewire.on('close-import-modal', () => importModal.hide());
        });
    </script>
    @endpush
</div>
