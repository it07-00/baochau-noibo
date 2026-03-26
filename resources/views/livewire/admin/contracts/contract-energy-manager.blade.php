<div>

    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Giảm phát thải & Hiệu quả năng lượng</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">HĐ Năng lượng</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Thêm Hợp Đồng
            </button>
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Tìm kiếm theo SHD hoặc Tên KH" wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="mb-0 fw-bold">Bộ lọc - Giảm phát thải & Hiệu quả NL</h6>
            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#filterBodyEnergy">−</button>
        </div>
        <div class="collapse show" id="filterBodyEnergy">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Ngày ký hợp đồng</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.signed_from">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.signed_to">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Ngày hợp đồng về</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.submitted_from">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.submitted_to">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Tỉnh thành</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.province">
                            <option value="">Chọn tỉnh thành</option>
                            @foreach($provinces as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-3 pb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="en_offset" wire:model.live="filter.is_offset">
                            <label class="form-check-label small" for="en_offset">Có bù trừ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="en_roomfund" wire:model.live="filter.has_room_fund">
                            <label class="form-check-label small" for="en_roomfund">Có quỹ phòng</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="en_overdue" wire:model.live="filter.is_overdue">
                            <label class="form-check-label small" for="en_overdue">Trễ hạn</label>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Phòng ban</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.department_id">
                            <option value="">Chọn phòng ban</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Nguồn thông tin</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.info_source">
                            <option value="">Chọn Nguồn thông...</option>
                            <option value="MỚI">MỚI</option>
                            <option value="TÁI KÝ">TÁI KÝ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Phương thức thanh toán</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.payment_method">
                            <option value="">Chọn phương thức...</option>
                            <option value="Sau ký">Sau ký</option>
                            <option value="Trước ký">Trước ký</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Tình trạng</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.status">
                            <option value="">Chọn tình trạng</option>
                            @foreach($all_statuses as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold custom-filter-label">Loại dịch vụ</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.loai_dich_vu">
                            <option value="">Chọn loại dịch vụ</option>
                            @foreach($loai_dich_vu_options as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 d-flex gap-2 mt-2">
                        <button class="btn btn-info text-white px-4 btn-filter" wire:click="$refresh">
                            <i class="bi bi-search me-1"></i>Lọc
                        </button>
                        <button class="btn btn-secondary px-4 btn-filter" wire:click="resetFilters">
                            <i class="bi bi-x-circle me-1"></i>Xóa lọc
                        </button>
                        <button class="btn btn-success px-4 btn-filter">
                            <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Danh sách HĐ Giảm phát thải & Hiệu quả NL</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-xs">
                <thead class="bg-light bg-opacity-50">
                    <tr class="small text-muted text-uppercase fw-bold">
                        <th class="ps-4">Thông tin hợp đồng</th>
                        <th>Khách hàng</th>
                        <th>Loại dịch vụ</th>
                        <th class="text-center">Giá trị hợp đồng</th>
                        <th class="text-center">Hoa hồng</th>
                        <th class="text-center">Doanh số</th>
                        <th class="text-center">Tình trạng</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docs as $doc)
                    <tr class="border-bottom border-light">
                        <td class="ps-4 py-4">
                            <div class="d-flex flex-column">
                                <span class="small">SHD BC: <span class="fw-bold">{{ $doc->shd_ad }}</span></span>
                                <span class="small">Ngày ký: <span class="fw-bold">{{ $doc->signed_at ? $doc->signed_at->format('d/m/Y') : '-' }}</span></span>
                                <span class="small">NVCS: <span class="fw-bold">{{ $doc->staff?->name }}</span></span>
                            </div>
                        </td>
                        <td class="py-4">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-uppercase text-primary">{{ $doc->customer?->name }}</span>
                                <span class="small">{{ $doc->customer?->representative }} - {{ $doc->customer?->phone }}</span>
                                <span class="small text-muted">{{ $doc->customer?->address }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle small">{{ $doc->loai_dich_vu ?: '-' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->value) }}đ</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->commission) }}đ</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->revenue) }}đ</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <span class="small fw-bold">{{ $doc->status ?: 'Đang thực hiện' }}</span>
                                <span class="small text-muted">{{ $doc->submitted_at ? $doc->submitted_at->format('d/m/Y') : '-' }}</span>
                            </div>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm p-0 text-primary" wire:click="viewDetail({{ $doc->id }})" title="Xem chi tiết">
                                    <i class="bi bi-eye fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-warning" wire:click="edit({{ $doc->id }})" title="Chỉnh sửa">
                                    <i class="bi bi-pencil fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-danger" wire:click="delete({{ $doc->id }})" onclick="return confirm('Xóa hợp đồng này?')" title="Xóa">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">Không tìm thấy hợp đồng nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($docs->hasPages())
        <div class="px-4 py-3 border-top">
            {{ $docs->links() }}
        </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div wire:ignore.self class="modal fade" id="detailModalEnergy" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title fw-bold modal-title-custom">Chi tiết HĐ Giảm phát thải & Hiệu quả NL</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if($selectedDoc)
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light w-30">SHD BC</th>
                                <td class="fw-bold">{{ $selectedDoc->shd_ad }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Khách hàng</th>
                                <td class="text-uppercase fw-bold text-primary">{{ $selectedDoc->customer?->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Người đại diện</th>
                                <td>{{ $selectedDoc->customer?->representative }} — {{ $selectedDoc->customer?->phone }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Địa chỉ</th>
                                <td>{{ $selectedDoc->customer?->address }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">NVCS</th>
                                <td>{{ $selectedDoc->staff?->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Phòng ban</th>
                                <td>{{ $selectedDoc->department?->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Ngày ký HĐ</th>
                                <td>{{ $selectedDoc->signed_at?->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Ngày HĐ về</th>
                                <td>{{ $selectedDoc->submitted_at?->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Tỉnh thành</th>
                                <td>{{ $selectedDoc->province }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Phương thức thanh toán</th>
                                <td>{{ $selectedDoc->payment_method }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Nguồn thông tin</th>
                                <td>{{ $selectedDoc->info_source }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Loại dịch vụ</th>
                                <td><span class="badge bg-warning text-dark">{{ $selectedDoc->loai_dich_vu ?: '-' }}</span></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Giá trị hợp đồng</th>
                                <td class="text-danger fw-bold">{{ number_format($selectedDoc->value) }}đ</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Hoa hồng</th>
                                <td class="text-danger fw-bold">{{ number_format($selectedDoc->commission) }}đ</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Doanh số</th>
                                <td class="text-danger fw-bold">{{ number_format($selectedDoc->revenue) }}đ</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Tình trạng</th>
                                <td><span class="badge bg-success">{{ $selectedDoc->status ?: 'Đang thực hiện' }}</span></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Bù trừ / Quỹ phòng</th>
                                <td>
                                    @if($selectedDoc->is_offset) <span class="badge bg-warning text-dark me-1">Có bù trừ</span> @endif
                                    @if($selectedDoc->has_room_fund) <span class="badge bg-info text-white me-1">Có quỹ phòng</span> @endif
                                    @if($selectedDoc->is_overdue) <span class="badge bg-danger">Trễ hạn</span> @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Ghi chú</th>
                                <td>{{ $selectedDoc->notes }}</td>
                            </tr>
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    <div wire:ignore.self class="modal fade" id="formModalEnergy" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold modal-title-custom text-white">
                        {{ $isEditing ? 'Chỉnh sửa' : 'Thêm' }} HĐ Giảm phát thải & Hiệu quả NL
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SHD BC</label>
                            <input type="text" class="form-control" wire:model="formData.shd_ad">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Khách hàng <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="formData.customer_id">
                                <option value="">-- Chọn khách hàng --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('formData.customer_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NVCS <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="formData.staff_id">
                                <option value="">-- Chọn nhân viên --</option>
                                @foreach($staffs as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @error('formData.staff_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phòng ban</label>
                            <select class="form-select" wire:model="formData.department_id">
                                <option value="">-- Chọn phòng ban --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày ký HĐ</label>
                            <input type="date" class="form-control" wire:model="formData.signed_at">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày HĐ về</label>
                            <input type="date" class="form-control" wire:model="formData.submitted_at">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Giá trị HĐ <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" wire:model="formData.value">
                            @error('formData.value') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Hoa hồng</label>
                            <input type="number" class="form-control" wire:model="formData.commission">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Doanh số</label>
                            <input type="number" class="form-control" wire:model="formData.revenue">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tỉnh thành</label>
                            <input type="text" class="form-control" wire:model="formData.province" list="province-list-energy" autocomplete="off">
                            <datalist id="province-list-energy">
                                @foreach($provinces as $p)
                                    <option value="{{ $p }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Loại dịch vụ</label>
                            <select class="form-select" wire:model="formData.loai_dich_vu">
                                <option value="">-- Chọn loại dịch vụ --</option>
                                @foreach($loai_dich_vu_options as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nguồn thông tin</label>
                            <select class="form-select" wire:model="formData.info_source">
                                <option value="MỚI">MỚI</option>
                                <option value="TÁI KÝ">TÁI KÝ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">PT thanh toán</label>
                            <select class="form-select" wire:model="formData.payment_method">
                                <option value="Sau ký">Sau ký</option>
                                <option value="Trước ký">Trước ký</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tình trạng</label>
                            <select class="form-select" wire:model="formData.status">
                                <option value="ĐANG THỰC HIỆN">ĐANG THỰC HIỆN</option>
                                <option value="HOÀN THÀNH">HOÀN THÀNH</option>
                                <option value="ĐÃ HỦY">ĐÃ HỦY</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Tình trạng tái ký</label>
                            <input type="text" class="form-control" wire:model="formData.renewal_status">
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="form_en_offset" wire:model="formData.is_offset">
                                    <label class="form-check-label" for="form_en_offset">Có bù trừ</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="form_en_roomfund" wire:model="formData.has_room_fund">
                                    <label class="form-check-label" for="form_en_roomfund">Có quỹ phòng</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="form_en_overdue" wire:model="formData.is_overdue">
                                    <label class="form-check-label" for="form_en_overdue">Trễ hạn</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Ghi chú</label>
                            <textarea class="form-control" rows="3" wire:model="formData.notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        Lưu hợp đồng
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.addEventListener('openDetailModal', () => {
            new bootstrap.Modal(document.getElementById('detailModalEnergy')).show();
        });
        Livewire.on('openFormModal', () => {
            new bootstrap.Modal(document.getElementById('formModalEnergy')).show();
        });
        Livewire.on('closeFormModal', () => {
            bootstrap.Modal.getInstance(document.getElementById('formModalEnergy'))?.hide();
        });
    </script>
    @endpush
</div>
