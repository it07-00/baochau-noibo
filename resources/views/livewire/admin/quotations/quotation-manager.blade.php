<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Theo dõi Báo giá</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Theo dõi Báo giá</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success d-flex align-items-center gap-2" wire:click="create">
                <i class="bi bi-plus-lg"></i> Thêm mới
            </button>
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Tìm kiếm công ty, ngành nghề..." wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Nhân viên sale</label>
                    <select class="form-select form-select-sm" wire:model.live="filter_staff">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Tình trạng</label>
                    <select class="form-select form-select-sm" wire:model.live="filter_status">
                        <option value="">Tất cả tình trạng</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Khoảng thời gian</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control form-control-sm" wire:model.live="date_from">
                        <input type="date" class="form-control form-control-sm" wire:model.live="date_to">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end justify-content-end">
                    <button class="btn btn-sm btn-outline-secondary" wire:click="$refresh">
                        <i class="bi bi-arrow-clockwise"></i> Làm mới
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-sm" style="font-size: 0.85rem;">
                <thead class="bg-light bg-opacity-50">
                    <tr class="text-muted fw-bold">
                        <th class="ps-3" style="width: 50px;">STT</th>
                        <th style="width: 100px;">Sale</th>
                        <th style="width: 100px;">Ngày</th>
                        <th style="width: 400px;">Thông tin khách hàng / Đối tác</th>
                        <th>Nội dung công việc</th>
                        <th class="text-center" style="width: 150px;">Tình trạng</th>
                        <th class="text-end" style="width: 120px;">Giá chưa VAT</th>
                        <th class="text-end" style="width: 120px;">Giá có VAT</th>
                        <th class="text-end" style="width: 100px;">Tiền thuế</th>
                        <th class="text-end" style="width: 120px;">Tiền hoa hồng</th>
                        <th class="text-end fw-bold" style="width: 130px;">Tổng tiền</th>
                        <th class="text-center pe-3" style="width: 80px;">#</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $index => $item)
                    <tr class="border-bottom border-light">
                        <td class="ps-3">{{ ($quotations->currentPage()-1) * $quotations->perPage() + $loop->iteration }}</td>
                        <td>{{ $item->staff?->name }}</td>
                        <td>{{ $item->date ? $item->date->format('d/m/Y') : '-' }}</td>
                        <td>
                            <div class="fw-bold text-primary mb-1 text-capitalize" style="font-size: 0.9rem; line-height: 1.3;">
                                {{ $item->company_name }}
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                @if($item->industry)
                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.7rem; font-weight: 500;">
                                    <i class="bi bi-tag-fill me-1 text-muted"></i>{{ $item->industry }}
                                </span>
                                @endif
                                @if($item->contact_person)
                                <span class="small text-muted fw-medium">
                                    <i class="bi bi-person-circle me-1"></i>{{ $item->contact_person }}
                                </span>
                                @endif
                            </div>
                            @if($item->address)
                            <div class="small text-muted text-wrap opacity-75" 
                                 style="line-height: 1.2; font-size: 0.8rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                                 title="{{ $item->address }}">
                                <i class="bi bi-geo-alt-fill me-1"></i>{{ $item->address }}
                            </div>
                            @endif
                        </td>
                        <td class="text-wrap" style="max-width: 300px;">
                            <div style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $item->work_description }}">
                                {{ $item->work_description }}
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
                            <span class="badge rounded-pill {{ $colorClass }} px-3 py-2" style="font-size: 0.75rem;">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="text-end">{{ number_format($item->original_value) }}</td>
                        <td class="text-end">{{ number_format($item->value_inc_vat) }}</td>
                        <td class="text-end">{{ number_format($item->commission_tax) }}</td>
                        <td class="text-end">{{ number_format($item->commission_value) }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($item->total_value) }}</td>
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
                                        onclick="confirm('Xác nhận xóa báo giá này?') || event.stopImmediatePropagation()"
                                        wire:click="delete({{ $item->id }})">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">Không tìm thấy dữ liệu báo giá</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
        <div class="px-3 py-3 border-top">
            {{ $quotations->links() }}
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
                                    <th class="bg-light fw-bold px-4 py-3" style="width: 30%;">Công ty / Khách hàng</th>
                                    <td class="px-4 py-3 fw-bold text-primary text-capitalize">{{ $selectedQuotation->company_name }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Địa chỉ</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->address }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Ngành nghề</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->industry }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Người liên hệ</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->contact_person }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Tình hình làm việc</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->work_description }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Tình trạng</th>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-primary px-3 py-2">{{ $selectedQuotation->status }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Ghi chú</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->notes }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Giá chưa VAT</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->original_value) }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Giá có VAT</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->value_inc_vat) }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Tiền thuế</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->commission_tax) }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Tiền hoa hồng</th>
                                    <td class="px-4 py-3 fw-bold text-danger">{{ number_format($selectedQuotation->commission_value) }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3 text-danger">Tổng tiền</th>
                                    <td class="px-4 py-3 fw-bold text-danger fs-5">{{ number_format($selectedQuotation->total_value) }}đ</td>
                                </tr>
                                <tr>
                                    <th class="bg-light fw-bold px-4 py-3">Nhân viên sale</th>
                                    <td class="px-4 py-3">{{ $selectedQuotation->staff?->name }} ({{ $selectedQuotation->date?->format('d/m/Y') }})</td>
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

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Địa chỉ xuất hóa đơn</label>
                                <input type="text" class="form-control" wire:model.defer="formData.address">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ngành nghề</label>
                                <input type="text" class="form-control" wire:model.defer="formData.industry">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Khách hàng (Người liên hệ)</label>
                                <input type="text" class="form-control" wire:model.defer="formData.contact_person">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Tình hình làm việc / Nội dung BG</label>
                                <textarea class="form-control" rows="3" wire:model.defer="formData.work_description"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tình trạng <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model.defer="formData.status" style="height: auto; min-height: 45px;">
                                    @foreach($statuses as $st)
                                        <option value="{{ $st }}">{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Giá chưa VAT</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end money-input" wire:model.live.debounce.500ms="formData.original_value">
                                    <span class="input-group-text p-1" style="font-size: 0.7rem;">đ</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Tiền thuế</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end money-input" wire:model.live.debounce.500ms="formData.commission_tax">
                                    <span class="input-group-text p-1" style="font-size: 0.7rem;">đ</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Giá có VAT</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end money-input" wire:model.live.debounce.500ms="formData.value_inc_vat">
                                    <span class="input-group-text p-1" style="font-size: 0.7rem;">đ</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Tiền hoa hồng</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end money-input" wire:model.live.debounce.500ms="formData.commission_value">
                                    <span class="input-group-text p-1" style="font-size: 0.7rem;">đ</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tổng tiền</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end fw-bold text-danger bg-light money-input" wire:model="formData.total_value" readonly>
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
                            <i class="bi bi-trash3 me-2"></i> Hợp đồng Chất thải
                        </button>
                        <button class="btn btn-outline-primary py-3 fw-bold" wire:click="convertTo('consulting')">
                            <i class="bi bi-chat-dots me-2"></i> Hợp đồng Tư vấn
                        </button>
                        <button class="btn btn-outline-primary py-3 fw-bold" wire:click="convertTo('project')">
                            <i class="bi bi-building me-2"></i> Hợp đồng Dự án
                        </button>
                        <button class="btn btn-outline-primary py-3 fw-bold" wire:click="convertTo('commercial')">
                            <i class="bi bi-cart3 me-2"></i> Hợp đồng Thương mại
                        </button>
                        <button class="btn btn-outline-success py-3 fw-bold" wire:click="convertTo('sustainability')">
                            <i class="bi bi-tree me-2"></i> HĐ Phát triển bền vững
                        </button>
                        <button class="btn btn-outline-warning py-3 fw-bold" wire:click="convertTo('energy')">
                            <i class="bi bi-lightning me-2"></i> HĐ Năng lượng
                        </button>
                    </div>
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

            Livewire.on('open-quotation-modal', () => formModal.show());
            Livewire.on('close-quotation-modal', () => formModal.hide());
            Livewire.on('open-detail-modal', () => detailModal.show());
            Livewire.on('open-convert-modal', () => convertModal.show());
        });
    </script>
    @endpush
</div>
