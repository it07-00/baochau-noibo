<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Quản lý Doanh số báo giá</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Quản lý Doanh số báo giá</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary d-flex align-items-center gap-2" wire:click="openCreateModal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Thêm mới
            </button>
            <div class="input-group" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Tìm kiếm..." wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="mb-0 fw-bold">Bộ lọc Doanh số báo giá</h6>
            <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
            </button>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Ngày báo giá</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-sm" wire:model.live="date_from">
                            <input type="date" class="form-control form-control-sm" wire:model.live="date_to">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Ngày liên hệ lại</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-sm" wire:model.live="follow_up_from">
                            <input type="date" class="form-control form-control-sm" wire:model.live="follow_up_to">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Dịch vụ</label>
                        <select class="form-select form-control-sm" wire:model.live="filter_service">
                            <option value="">Chọn dịch vụ</option>
                            @foreach($services as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Tỉnh thành</label>
                        <select class="form-select form-control-sm" wire:model.live="filter_province">
                            <option value="">Chọn tỉnh thành</option>
                            @foreach($provinces as $p) <option value="{{ $p }}">{{ $p }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold custom-filter-label">Phòng ban</label>
                        <select class="form-select form-control-sm" wire:model.live="filter_department">
                            <option value="">Chọn phòng ban</option>
                            @foreach($departments as $d) <option value="{{ $d->id }}">{{ $d->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold custom-filter-label">Nguồn thông tin</label>
                        <select class="form-select form-control-sm" wire:model.live="filter_source">
                            <option value="">Chọn nguồn thông tin</option>
                            @foreach($sources as $src) <option value="{{ $src }}">{{ $src }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn btn-primary px-4"><i class="bi bi-search me-1"></i> Lọc</button>
                        <button class="btn btn-success px-4"><i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Danh sách Doanh số báo giá</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">THÔNG TIN BÁO GIÁ</th>
                        <th>KHÁCH HÀNG</th>
                        <th class="text-center">SỐ BÁO GIÁ</th>
                        <th class="text-end">GIÁ TRỊ HỢP ĐỒNG</th>
                        <th class="text-end">HOA HỒNG</th>
                        <th class="text-end">DOANH SỐ</th>
                        <th>TÌNH TRẠNG</th>
                        <th class="text-center pe-4">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">Ngày báo giá: {{ $item->quotation_date?->format('d/m/Y') }}</div>
                            <small class="text-muted">NVBG: {{ $item->staff?->name }}</small>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $item->company_name }}</div>
                            <small class="text-muted">{{ $item->customer_name }} - {{ $item->customer_phone }}</small><br>
                            <small class="text-muted">{{ $item->address }}, {{ $item->province }}</small>
                        </td>
                        <td class="text-center"><span class="badge bg-light text-primary border border-primary">{{ $item->quotation_number }}</span></td>
                        <td class="text-end fw-bold text-danger">{{ number_format($item->value_ext_vat) }}đ</td>
                        <td class="text-end fw-bold">{{ number_format($item->commission) }}đ</td>
                        <td class="text-end fw-bold">{{ number_format($item->sales_amount) }}đ</td>
                        <td>
                            <small class="d-block">{{ $item->status }}</small>
                            <small class="text-muted">{{ $item->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm btn-outline-primary" wire:click="edit({{ $item->id }})"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirm('Xác nhận xóa?') || event.stopImmediatePropagation()" wire:click="delete({{ $item->id }})"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <img src="{{ asset('assets/images/no-data.png') }}" alt="" width="100" class="mb-3 opacity-50">
                            <p class="text-muted">Không tìm thấy dữ liệu</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $items->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="quotation-modal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">{{ $isEditing ? 'Cập nhật Doanh số báo giá' : 'Thêm mới Doanh số báo giá' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Số báo giá <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="quotation_number">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nhân viên báo giá <span class="text-danger">*</span></label>
                                <select class="form-select form-control-sm" wire:model="staff_id">
                                    <option value="">Chọn nhân viên</option>
                                    @foreach($staffs as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tháng tính doanh số <span class="text-danger">*</span></label>
                                <input type="month" class="form-control form-control-sm" wire:model="sales_month">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Dịch vụ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="service" placeholder="Chọn dịch vụ">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nguồn thông tin <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="info_source" placeholder="Chọn nguồn thông tin">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Ngày báo giá <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" wire:model="quotation_date">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Ngày liên hệ lại</label>
                                <input type="date" class="form-control form-control-sm" wire:model="follow_up_date">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Giá trị báo giá (chưa VAT) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" wire:model.blur="value_ext_vat" wire:change="calculateSales">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Hoa hồng <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" wire:model="commission">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">% tính doanh số <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" wire:model.blur="sales_percentage" wire:change="calculateSales">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Doanh số</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control bg-light" wire:model="sales_amount" readonly>
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tên công ty <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="company_name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Địa chỉ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="address">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Tỉnh/Thành</label>
                                <input type="text" class="form-control form-control-sm" wire:model="province" placeholder="Chọn tỉnh thành">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Nội dung</label>
                                <input type="text" class="form-control form-control-sm" wire:model="content" placeholder="Nội dung">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tên khách hàng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="customer_name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="customer_phone">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" class="form-control form-control-sm" wire:model="customer_email">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tổng người làm việc</label>
                                <input type="number" class="form-control form-control-sm" wire:model="total_workers">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tình trạng thực hiện <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="status" placeholder="Chọn tình trạng">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Ghi chú</label>
                                <input type="text" class="form-control form-control-sm" wire:model="notes" placeholder="Ghi chú">
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary px-4">Lưu lại</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts for Modal -->
    <script>
        window.addEventListener('open-modal', event => {
            var myModal = new bootstrap.Modal(document.getElementById(event.detail));
            myModal.show();
        });
        window.addEventListener('close-modal', event => {
            var myModalel = document.getElementById(event.detail);
            var modal = bootstrap.Modal.getInstance(myModalel);
            modal.hide();
        });
    </script>
</div>
