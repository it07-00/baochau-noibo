<div class="border-top mt-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center px-4 py-3 bg-light">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-cash-stack me-1"></i> Lịch thanh toán
            @if ($schedules->count())
                <span class="badge bg-primary ms-1">{{ $schedules->count() }} đợt</span>
            @endif
        </h6>
        @if ($canManage)
            <button class="btn btn-sm btn-primary" wire:click="openForm">
                <i class="bi bi-plus-circle me-1"></i>Thêm đợt
            </button>
        @endif
    </div>

    {{-- Summary bar --}}
    @if ($schedules->count())
        <div class="px-4 py-2 bg-white border-bottom">
            @php
                $percent = $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100) : 0;
            @endphp
            <div class="d-flex justify-content-between small mb-1">
                <span>Đã thanh toán: <strong class="text-success">{{ number_format($totalPaid) }}đ</strong></span>
                <span>Tổng: <strong>{{ number_format($totalAmount) }}đ</strong></span>
                <span>Còn lại: <strong
                        class="text-danger">{{ number_format($totalAmount - $totalPaid) }}đ</strong></span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" style="width: {{ $percent }}%"></div>
            </div>
            <div class="text-end small text-muted mt-1">{{ $percent }}% hoàn thành</div>
        </div>
    @endif

    {{-- Schedules table --}}
    @if ($schedules->count())
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small text-muted">
                        <th class="ps-4" style="width:40px;">#</th>
                        <th>Tên đợt</th>
                        <th class="text-center">Tỉ lệ</th>
                        <th class="text-end">Số tiền</th>
                        <th class="text-center">Hạn TT</th>
                        <th class="text-end">Đã trả</th>
                        <th class="text-center">Trạng thái</th>
                        @if ($canManage)
                            <th class="text-center pe-4">Thao tác</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedules as $s)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">{{ $s->installment_number }}</td>
                            <td>
                                <span class="fw-semibold">{{ $s->installment_name }}</span>
                                @if ($s->notes)
                                    <br><small class="text-muted">{{ $s->notes }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($s->percentage > 0)
                                    <span class="badge bg-light text-dark border">{{ $s->percentage }}%</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end fw-bold text-danger">{{ number_format($s->amount) }}đ</td>
                            <td class="text-center">
                                @if ($s->due_date)
                                    <span
                                        class="{{ $s->status !== 'paid' && $s->due_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                        {{ $s->due_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($s->paid_amount > 0)
                                    <span class="fw-bold text-success">{{ number_format($s->paid_amount) }}đ</span>
                                    @if ($s->paid_date)
                                        <br><small class="text-muted">{{ $s->paid_date->format('d/m/Y') }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">0đ</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $s->status_color }}">{{ $s->status_label }}</span>
                            </td>
                            @if ($canManage)
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if ($s->status !== 'paid')
                                            <button class="btn btn-sm p-0 text-success"
                                                wire:click="markPaid({{ $s->id }})" title="Đã thanh toán"
                                                wire:confirm="Đánh dấu đợt này đã thanh toán đủ?">
                                                <i class="bi bi-check-circle fs-6"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm p-0 text-warning"
                                            wire:click="edit({{ $s->id }})" title="Sửa">
                                            <i class="bi bi-pencil fs-6"></i>
                                        </button>
                                        <button class="btn btn-sm p-0 text-danger"
                                            wire:click="delete({{ $s->id }})"
                                            wire:confirm="Xác nhận xóa đợt thanh toán này?" title="Xóa">
                                            <i class="bi bi-trash fs-6"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4 text-muted small">
            <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
            Chưa có lịch thanh toán nào.
        </div>
    @endif

    {{-- Inline Form --}}
    @if ($showForm)
        <div class="border-top p-4 bg-light">
            <h6 class="fw-bold mb-3">{{ $isEditing ? 'Chỉnh sửa' : 'Thêm' }} đợt thanh toán</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Tên đợt <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control form-control-sm @error('form.installment_name') is-invalid @enderror"
                        wire:model="form.installment_name" placeholder="VD: Đợt 1, Tạm ứng...">
                    @error('form.installment_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Tỉ lệ (%)</label>
                    <input type="number" class="form-control form-control-sm" wire:model="form.percentage"
                        min="0" max="100" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Số tiền (đ) <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control form-control-sm money-input @error('form.amount') is-invalid @enderror"
                        wire:model="form.amount" placeholder="0">
                    @error('form.amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Hạn thanh toán</label>
                    <input type="date" class="form-control form-control-sm" wire:model="form.due_date">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Đã thanh toán (đ)</label>
                    <input type="text" class="form-control form-control-sm money-input" wire:model="form.paid_amount"
                        placeholder="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Ngày thanh toán</label>
                    <input type="date" class="form-control form-control-sm" wire:model="form.paid_date">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Trạng thái</label>
                    <select class="form-select form-select-sm" wire:model="form.status">
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Ghi chú</label>
                    <input type="text" class="form-control form-control-sm" wire:model="form.notes"
                        placeholder="Ghi chú...">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-sm btn-primary" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        <i wire:loading.remove wire:target="save" class="bi bi-check-lg me-1"></i>
                        {{ $isEditing ? 'Cập nhật' : 'Thêm đợt' }}
                    </button>
                    <button class="btn btn-sm btn-secondary" wire:click="$set('showForm', false)">Hủy</button>
                </div>
            </div>
        </div>
    @endif
</div>
