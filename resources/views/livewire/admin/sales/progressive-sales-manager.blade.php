<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Doanh số theo tiến độ</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Doanh số theo tiến độ</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-3 py-2" style="font-size: 0.8rem;">
                <i class="bi bi-arrow-repeat me-1"></i> Đồng bộ từ lịch thanh toán hợp đồng
            </span>
            <div class="input-group" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Tìm SHĐ, giai đoạn..." wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label  fw-bold">Tháng hạn thanh toán</label>
                    <input type="month" class="form-control form-control-sm" wire:model.live="filter_month">
                </div>
                <div class="col-md-3">
                    <label class="form-label  fw-bold">Tình trạng</label>
                    <select class="form-select form-select-sm" wire:model.live="filter_status">
                        <option value="">Tất cả</option>
                        @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-sm btn-outline-secondary" wire:click="$refresh"><i class="bi bi-arrow-clockwise me-1"></i>Làm mới</button>
                </div>
                <div class="col-md-3 text-end">
                    <div class=" text-muted">Tổng giá trị lọc được</div>
                    <div class="fw-bold text-danger fs-5">{{ number_format($total) }}đ</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-sm" style="font-size: 0.875rem;">
                <thead class="bg-light">
                    <tr class="text-muted fw-bold">
                        <th class="ps-3" style="width: 40px;">STT</th>
                        <th>Số HĐ</th>
                        <th>Giai đoạn</th>
                        <th class="text-center" style="width: 110px;">Hạn TT</th>
                        <th class="text-center" style="width: 80px;">%</th>
                        <th class="text-end" style="width: 140px;">Giá trị</th>
                        <th class="text-end" style="width: 140px;">Đã thanh toán</th>
                        <th class="text-center" style="width: 140px;">Tình trạng</th>
                        <th class="text-center pe-3" style="width: 80px;">#</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td class="ps-3">{{ ($items->currentPage()-1) * $items->perPage() + $loop->iteration }}</td>
                        <td class="fw-bold text-primary">{{ $item->contract_number ?: '-' }}</td>
                        <td>{{ $item->installment_name }}</td>
                        <td class="text-center">{{ $item->due_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center text-success fw-semibold">{{ $item->percentage > 0 ? number_format($item->percentage, 1).'%' : '-' }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($item->amount) }}đ</td>
                        <td class="text-end text-success">{{ $item->paid_amount > 0 ? number_format($item->paid_amount).'đ' : '-' }}</td>
                        <td class="text-center">
                            <span class="badge rounded-pill bg-{{ $item->status_color }} bg-opacity-10 text-{{ $item->status_color }} border border-{{ $item->status_color }}-subtle px-2 py-1" style="font-size: 0.72rem;">{{ $item->status_label }}</span>
                        </td>
                        <td class="text-center pe-3">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-sm p-0 text-warning" wire:click="edit({{ $item->id }})"><i class="bi bi-pencil-square fs-5"></i></button>
                                <button class="btn btn-sm p-0 text-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xác nhận xóa?"><i class="bi bi-trash fs-5"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">Không tìm thấy dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-3 py-3 border-top">
            {{ $items->links('livewire.admin.users.pagination') }}
        </div>
        @endif
    </div>

    <!-- Modal chỉnh sửa lịch thanh toán -->
    <div class="modal fade" id="progressive-modal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">Cập nhật lịch thanh toán</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label  fw-bold">Tên giai đoạn <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model="installment_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label  fw-bold">Hạn thanh toán</label>
                                <input type="date" class="form-control form-control-sm" wire:model="due_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label  fw-bold">Phần trăm (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" wire:model="percentage">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label  fw-bold">Giá trị (VNĐ)</label>
                                <input type="text" class="form-control form-control-sm money-input" wire:model="amount">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label  fw-bold">Đã thanh toán (VNĐ)</label>
                                <input type="text" class="form-control form-control-sm money-input" wire:model="paid_amount">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label  fw-bold">Ngày thanh toán thực tế</label>
                                <input type="date" class="form-control form-control-sm" wire:model="paid_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label  fw-bold">Tình trạng <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" wire:model="status">
                                    <option value="">--</option>
                                    @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label  fw-bold">Ghi chú</label>
                                <textarea class="form-control form-control-sm" wire:model="notes" rows="2"></textarea>
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

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            const el = document.getElementById('progressive-modal');
            const modal = new bootstrap.Modal(el);
            Livewire.on('open-modal',  ([id]) => { if (id === 'progressive-modal') modal.show(); });
            Livewire.on('close-modal', ([id]) => { if (id === 'progressive-modal') modal.hide(); });
        });
    </script>
    @endpush
</div>
