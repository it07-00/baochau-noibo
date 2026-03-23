<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Quản lý Doanh số theo tiến độ</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Quản lý Doanh số theo tiến độ</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary d-flex align-items-center gap-2" wire:click="openCreateModal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Thêm mới
            </button>
            <div class="input-group" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Tìm kiếm theo SHD..." wire:model.live.debounce.300ms="search">
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
        <div class="card-header bg-white py-3 border-bottom border-secondary-subtle">
            <h6 class="mb-0 fw-bold">Bộ lọc Doanh số theo tiến độ</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold custom-filter-label">Tháng tính doanh số</label>
                    <input type="month" class="form-control form-control-sm" wire:model.live="filter_month">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button class="btn btn-primary px-4"><i class="bi bi-search me-1"></i> Lọc</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">SỐ HỢP ĐỒNG</th>
                        <th>GIAI ĐOẠN / TIẾN ĐỘ</th>
                        <th class="text-center">THÁNG</th>
                        <th class="text-center">PHẦN TRĂM (%)</th>
                        <th class="text-end">SỐ TIỀN</th>
                        <th class="text-center">TÌNH TRẠNG</th>
                        <th class="text-center pe-4">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $item->contract_number }}</td>
                        <td>{{ $item->milestone_name }}</td>
                        <td class="text-center">{{ $item->sales_month->format('m/Y') }}</td>
                        <td class="text-center fw-bold text-success">{{ number_format($item->percentage, 2) }}%</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($item->amount) }}đ</td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $item->status ?? 'Hoàn thành' }}</span>
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
                        <td colspan="7" class="text-center py-5">
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
    <div class="modal fade" id="progressive-modal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">{{ $isEditing ? 'Cập nhật Doanh số theo tiến độ' : 'Thêm mới Doanh số theo tiến độ' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Số hợp đồng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="contract_number" placeholder="Chọn số hợp đồng">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tháng tính doanh số <span class="text-danger">*</span></label>
                                <input type="month" class="form-control form-control-sm" wire:model="sales_month">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tên giai đoạn / tiến độ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="milestone_name" placeholder="Ví dụ: Đợt 1, Giai đoạn 2...">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phần trăm (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control form-control-sm" wire:model="percentage">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Số tiền (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm" wire:model="amount">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Tình trạng</label>
                                <input type="text" class="form-control form-control-sm" wire:model="status">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Ghi chú</label>
                                <textarea class="form-control form-control-sm" wire:model="notes" rows="3"></textarea>
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
            if(event.detail === 'progressive-modal') {
                var myModal = new bootstrap.Modal(document.getElementById('progressive-modal'));
                myModal.show();
            }
        });
        window.addEventListener('close-modal', event => {
            if(event.detail === 'progressive-modal') {
                var myModalel = document.getElementById('progressive-modal');
                var modal = bootstrap.Modal.getInstance(myModalel);
                if(modal) modal.hide();
            }
        });
    </script>
</div>
