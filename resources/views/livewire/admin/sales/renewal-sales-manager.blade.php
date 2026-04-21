<div>
    <div class="page-header d-flex align-items-start align-items-sm-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h4 class="mb-0">Quản lý Doanh số tái ký</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Quản lý Doanh số tái ký</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" wire:click="openCreateModal">
                <i class="bi bi-plus-lg"></i> Thêm mới
            </button>
            <div class="input-group" style="width: 230px;">
                <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm theo SHD..." wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-2 px-4 d-flex align-items-center justify-content-between border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-funnel-fill text-primary" style="font-size:13px;"></i>
                <span class="fw-semibold" style="font-size:13px;">Bộ lọc Doanh số tái ký</span>
            </div>
            <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" style="font-size:18px; line-height:1;">−</button>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body px-4 py-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="custom-filter-label fw-semibold d-block mb-1">Tháng tính doanh số</label>
                        <input type="month" class="form-control form-control-sm" wire:model.live="filter_month">
                    </div>
                    <div class="col-md-3">
                        <label class="custom-filter-label fw-semibold d-block mb-1">Tình trạng</label>
                        <select class="form-select form-select-sm" wire:model.live="filter_status">
                            <option value="">Tất cả</option>
                            @foreach($statuses as $st) <option value="{{ $st }}">{{ $st }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto d-flex align-items-end gap-2">
                        <button class="btn btn-primary btn-sm px-3" wire:click="$refresh"><i class="bi bi-search me-1"></i>Lọc</button>
                        <button class="btn btn-outline-secondary btn-sm px-3" wire:click="resetFilters"><i class="bi bi-x-circle me-1"></i>Xóa lọc</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom border-secondary-subtle">
            <h6 class="mb-0 fw-bold">Danh sách Doanh số tái ký</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width:45px;">STT</th>
                        <th class="ps-4">Thông tin hợp đồng</th>
                        <th>Khách hàng</th>
                        <th class="text-center">Tháng tính doanh số</th>
                        <th class="text-end">Giá trị tính doanh số</th>
                        <th class="text-end">Hoa hồng</th>
                        <th class="text-end">Doanh số</th>
                        <th class="text-center">Tình trạng</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td class="text-center text-muted  fw-semibold">{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                        <td class="ps-4">
                            <div class="fw-bold">SHD: {{ $item->contract_number }}</div>
                            <small class="text-muted">Ngày tạo: {{ $item->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            <div class="text-muted ">Cần bổ sung quan hệ khách hàng</div>
                        </td>
                        <td class="text-center">{{ $item->sales_month->format('m/Y') }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($item->sales_value) }}đ</td>
                        <td class="text-end fw-bold">{{ number_format($item->commission) }}đ</td>
                        <td class="text-end fw-bold">{{ number_format($item->sales_amount) }}đ</td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $item->status ?? 'Chờ xử lý' }}</span>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm btn-outline-primary" wire:click="edit({{ $item->id }})"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xác nhận xóa?"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
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
    <div class="modal fade" id="renewal-modal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">{{ $isEditing ? 'Cập nhật Doanh số tái ký' : 'Thêm mới Doanh số tái ký' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label  fw-bold">Số hợp đồng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="contract_number" placeholder="Chọn số hợp đồng">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label  fw-bold">Tháng tính doanh số <span class="text-danger">*</span></label>
                                <input type="month" class="form-control form-control-sm" wire:model="sales_month">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label  fw-bold">Giá trị tính doanh số <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control money-input" wire:model.blur="sales_value" wire:change="calculateSales">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label  fw-bold">Hoa hồng <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control money-input" wire:model="commission">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label  fw-bold">% tính doanh số <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" wire:model.blur="sales_percentage" wire:change="calculateSales">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label  fw-bold">Doanh số</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control bg-light money-input" wire:model="sales_amount" readonly>
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label  fw-bold">Tình trạng <span class="text-danger">*</span></label>
                                <select class="form-select form-control-sm" wire:model="status">
                                    <option value="">Chọn tình trạng</option>
                                    <option value="Hợp đồng mẫu">Hợp đồng mẫu</option>
                                    <option value="Đã ký">Đã ký</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label  fw-bold">Ghi chú</label>
                                <textarea class="form-control form-control-sm" wire:model="notes" rows="3"></textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label  fw-bold">Tệp đính kèm</label>
                                <input type="file" class="form-control form-control-sm" wire:model="file">
                                <small class="text-muted">.doc|.docx|.pdf|.rar|.zip|.ppt|.pptx|.xls|.xlsx|.png|.jpg</small>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Thoát</button>
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
            if(event.detail === 'renewal-modal') {
                var myModal = new bootstrap.Modal(document.getElementById('renewal-modal'));
                myModal.show();
            }
        });
        window.addEventListener('close-modal', event => {
            if(event.detail === 'renewal-modal') {
                var myModalel = document.getElementById('renewal-modal');
                var modal = bootstrap.Modal.getInstance(myModalel);
                if(modal) modal.hide();
            }
        });
    </script>
</div>
