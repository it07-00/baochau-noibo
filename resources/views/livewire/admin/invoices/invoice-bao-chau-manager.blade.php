<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Hóa đơn Bảo Châu</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Hóa đơn Bảo Châu</li>
                </ol>
            </nav>
        </div>
        @can('invoices.create')
        <button wire:click="openCreate" class="btn btn-primary d-flex align-items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Thêm hóa đơn
        </button>
        @endcan
    </div>

    {{-- Tóm tắt --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class=" text-muted">Tổng hóa đơn</div>
                    <div class="fw-bold fs-5 text-primary">{{ $summary->cnt ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class=" text-muted">Tổng giá trị</div>
                    <div class="fw-bold text-dark">{{ number_format($summary->total ?? 0, 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class=" text-muted">Đã thu</div>
                    <div class="fw-bold text-success">{{ number_format($summary->paid ?? 0, 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class=" text-muted">Còn phải thu</div>
                    <div class="fw-bold text-warning">{{ number_format($summary->outstanding ?? 0, 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Năm</label>
                    <select wire:model.live="year" class="form-select form-select-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Tháng</label>
                    <select wire:model.live="filter_month" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}">Tháng {{ $m }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1 ">Trạng thái</label>
                    <select wire:model.live="filter_status" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1 ">Khách hàng</label>
                    <select wire:model.live="filter_customer" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng danh sách --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Số HĐ</th>
                            <th>Khách hàng</th>
                            <th>Hợp đồng CXL</th>
                            <th>Ngày lập</th>
                            <th>Hạn TT</th>
                            <th class="text-end">Tiền trước VAT</th>
                            <th class="text-end">VAT</th>
                            <th class="text-end">Tổng cộng</th>
                            <th class="text-end">Đã thu</th>
                            <th>Trạng thái</th>
                            @canany(['invoices.edit', 'invoices.delete'])
                            <th class="w-80px"></th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="fw-semibold ">{{ $item->invoice_number ?: '—' }}</td>
                            <td>{{ $item->customer?->name ?? '—' }}</td>
                            <td class=" text-muted">{{ $item->contractWaste?->shd_bc ?? '—' }}</td>
                            <td class=" text-muted">{{ $item->issue_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class=" {{ $item->due_date && $item->due_date->isPast() && $item->status !== 'paid' ? 'text-danger fw-semibold' : 'text-muted' }}">
                                {{ $item->due_date?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="text-end ">{{ number_format($item->amount, 0, ',', '.') }}</td>
                            <td class="text-end  text-muted">{{ number_format($item->vat_amount, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($item->total_amount, 0, ',', '.') }} đ</td>
                            <td class="text-end  text-success">{{ $item->paid_amount > 0 ? number_format($item->paid_amount, 0, ',', '.') . ' đ' : '—' }}</td>
                            <td>
                                <span class="badge bg-soft-{{ $item->status_color }} text-{{ $item->status_color }} ">
                                    {{ $item->status_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('invoices.edit')
                                <button wire:click="openEdit({{ $item->id }})" class="btn btn-sm btn-outline-primary py-0 px-2">Sửa</button>
                                @endcan
                                @can('invoices.delete')
                                <button wire:click="confirmDelete({{ $item->id }})" class="btn btn-sm btn-outline-danger py-0 px-2">Xóa</button>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="11" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Modal tạo / chỉnh sửa --}}
    @if($showModal)
    <div class="modal show d-block overlay-bg-sm" tabindex="-1" >
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingId ? 'Chỉnh sửa hóa đơn' : 'Thêm hóa đơn mới' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold ">Khách hàng <span class="text-danger">*</span></label>
                            <select wire:model="form.customer_id" class="form-select form-select-sm @error('form.customer_id') is-invalid @enderror">
                                <option value="">— Chọn khách hàng —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('form.customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold ">Hợp đồng chất thải</label>
                            <select wire:model="form.contract_waste_id" class="form-select form-select-sm">
                                <option value="">— Không liên kết —</option>
                                @foreach($contractWastes as $cw)
                                    <option value="{{ $cw->id }}">{{ $cw->shd_bc ?: 'HĐ #'.$cw->id }} — {{ $cw->customer?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold ">Số hóa đơn</label>
                            <input wire:model="form.invoice_number" type="text" class="form-control form-control-sm" placeholder="Nhập số HĐ VAT">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold ">Ngày lập</label>
                            <input wire:model="form.issue_date" type="date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold ">Hạn thanh toán</label>
                            <input wire:model="form.due_date" type="date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold ">Tiền trước VAT (đ) <span class="text-danger">*</span></label>
                            <input wire:model.live="form.amount" type="number" class="form-control form-control-sm @error('form.amount') is-invalid @enderror" placeholder="0" min="0">
                            @error('form.amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold ">VAT %</label>
                            <input wire:model.live="form.vat_percent" type="number" class="form-control form-control-sm" value="10" min="0" max="100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold ">Tiền VAT</label>
                            <input wire:model="form.vat_amount" type="number" class="form-control form-control-sm bg-light" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold ">Tổng cộng</label>
                            <input wire:model="form.total_amount" type="number" class="form-control form-control-sm bg-light fw-bold" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold ">Trạng thái</label>
                            <select wire:model="form.status" class="form-select form-select-sm">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold ">Đã thu (đ)</label>
                            <input wire:model="form.paid_amount" type="number" class="form-control form-control-sm" placeholder="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold ">Ngày thanh toán</label>
                            <input wire:model="form.paid_at" type="date" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold ">Mô tả dịch vụ</label>
                            <input wire:model="form.service_description" type="text" class="form-control form-control-sm" placeholder="Dịch vụ thu gom, xử lý chất thải...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold ">Ghi chú</label>
                            <textarea wire:model="form.notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('showModal',false)">Hủy</button>
                    <button type="button" class="btn btn-primary btn-sm" wire:click="save">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        Lưu hóa đơn
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal xác nhận xóa --}}
    @if($showDeleteModal)
    <div class="modal show d-block overlay-bg-sm" tabindex="-1" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" wire:click="$set('showDeleteModal',false)"></button>
                </div>
                <div class="modal-body">Bạn có chắc chắn muốn xóa hóa đơn này không?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" wire:click="$set('showDeleteModal',false)">Hủy</button>
                    <button class="btn btn-danger btn-sm" wire:click="delete">Xóa</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
