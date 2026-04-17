<div>
    <div class="page-title-box d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-1">Quản lý Yêu cầu chi hoa hồng</h4>
            <p class="text-muted mb-2">Theo dõi, kiểm soát và duyệt yêu cầu chi hoa hồng theo trạng thái xử lý.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Quản lý Yêu cầu chi hoa hồng</li>
                </ol>
            </nav>
        </div>
        <div class="page-title-right d-flex gap-2 ms-auto">
            <a href="{{ route('app.commissions.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i>
                Tạo yêu cầu
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Tổng yêu cầu</p>
                    <h4 class="mb-0">{{ number_format($summary['total']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Chờ chi</p>
                    <h4 class="mb-0 text-warning">{{ number_format($summary['pending']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Đã chi</p>
                    <h4 class="mb-0 text-success">{{ number_format($summary['approved']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Tổng tiền lọc</p>
                    <h4 class="mb-0 text-primary">{{ number_format($summary['amount'], 0, ',', '.') }} đ</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Bộ lọc</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$refresh">
                <i class="bi bi-arrow-clockwise me-1"></i>
                Làm mới
            </button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-lg-3">
                    <label class="form-label">Loại hợp đồng</label>
                    <select wire:model.live="contractTypeFilter" class="form-select">
                        <option value="">Tất cả loại hợp đồng</option>
                        @foreach($contractTypes as $class => $label)
                            <option value="{{ $class }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-2">
                    <label class="form-label">Tình trạng</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Tất cả tình trạng</option>
                        <option value="Chờ chi">Chờ chi</option>
                        <option value="Đã chi">Đã chi</option>
                        <option value="Từ chối">Từ chối</option>
                    </select>
                </div>
                <div class="col-12 col-lg-2">
                    <label class="form-label">Tháng yêu cầu</label>
                    <input type="month" wire:model.live="requestMonthFilter" class="form-control">
                </div>
                <div class="col-12 col-lg-2">
                    <label class="form-label">Người yêu cầu</label>
                    <select wire:model.live="requesterFilter" class="form-select">
                        <option value="">Tất cả người yêu cầu</option>
                        @foreach($requesters as $requester)
                            <option value="{{ $requester->id }}">{{ $requester->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-3">
                    <label class="form-label">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Tìm theo số HĐ BC, khách hàng, người nhận...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0">Danh sách yêu cầu chi hoa hồng</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">{{ number_format($requests->total()) }} kết quả</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="min-width: 45px; width: 45px;">STT</th>
                            <th class="ps-4" style="min-width: 300px;">Hợp đồng / Khách hàng</th>
                            <th style="min-width: 220px;">Người nhận</th>
                            <th class="text-center" style="min-width: 170px;">Loại hợp đồng</th>
                            <th class="text-end" style="min-width: 150px;">Số tiền</th>
                            <th class="text-center" style="min-width: 180px;">Tình trạng</th>
                            <th class="text-center" style="min-width: 120px;">Ngày gửi</th>
                            <th class="text-end pe-4" style="min-width: 260px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td class="text-center text-muted  fw-semibold">{{ ($requests->currentPage() - 1) * $requests->perPage() + $loop->iteration }}</td>
                                <td class="ps-4">
                                    <div class="fw-semibold text-primary mb-1">BC {{ $request->contract->shd_bc ?? 'N/A' }}</div>
                                    @if($request->contract && $request->contract->customer)
                                        <div class="fw-semibold">{{ $request->contract->customer->name }}</div>
                                        <div class=" text-muted text-truncate" style="max-width: 320px;">
                                            {{ $request->contract->customer->address }}
                                        </div>
                                    @else
                                        <div class=" text-muted">{{ $request->referrer_info ?: 'Không có thông tin khách hàng' }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $request->receiver_name }}</div>
                                    <div class=" text-muted">{{ $request->receiver_phone ?: 'Chưa có số điện thoại' }}</div>
                                    <div class=" text-muted text-truncate" style="max-width: 200px;">{{ $request->bank_account ?: 'Chưa có tài khoản ngân hàng' }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-soft-primary text-primary px-2 py-1">{{ $request->contract_type_label }}</span>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($request->amount, 0, ',', '.') }} đ</td>
                                <td class="text-center">
                                    @if($request->status === 'Đã chi')
                                        <span class="badge bg-soft-success text-success px-3 py-2">
                                            Đã chi
                                            <br>
                                            <>{{ $request->processed_at?->format('d/m/Y') }}</>
                                        </span>
                                    @elseif($request->status === 'Từ chối')
                                        @php
                                            $rejectionReason = '';
                                            if (!empty($request->notes) && str_contains($request->notes, 'Lý do từ chối (kế toán):')) {
                                                $rejectionReason = trim(\Illuminate\Support\Str::afterLast($request->notes, 'Lý do từ chối (kế toán):'));
                                            }
                                        @endphp
                                        <span class="badge bg-soft-danger text-danger px-3 py-2">
                                            Từ chối
                                            <br>
                                            <>{{ $request->processed_at?->format('d/m/Y') }}</>
                                        </span>
                                        @if($rejectionReason)
                                            <div class=" text-muted mt-1" style="max-width: 190px;" title="{{ $rejectionReason }}">
                                                Lý do: {{ \Illuminate\Support\Str::limit($rejectionReason, 70) }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge bg-soft-warning text-warning px-3 py-2">Chờ chi</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $request->created_at->format('d/m/Y') }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                        @if($canApprove && $request->status === 'Chờ chi')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    wire:click="approve({{ $request->id }})"
                                                    wire:confirm="Xác nhận duyệt chi yêu cầu này?">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Duyệt
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    wire:click="startReject({{ $request->id }})">
                                                <i class="bi bi-x-circle me-1"></i>
                                                Từ chối
                                            </button>
                                        @endif

                                        @if($canEdit)
                                            <a href="{{ route('app.commissions.edit', $request->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil-square me-1"></i>
                                                Sửa
                                            </a>
                                        @endif

                                        @if($canDelete)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    wire:click="delete({{ $request->id }})"
                                                    wire:confirm="Xác nhận xóa yêu cầu này?">
                                                <i class="bi bi-trash me-1"></i>
                                                Xóa
                                            </button>
                                        @endif

                                        @if(!$canApprove && !$canEdit && !$canDelete)
                                            <span class=" text-muted">Không có thao tác</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Không tìm thấy yêu cầu nào phù hợp bộ lọc.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="rejectReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Từ chối yêu cầu chi hoa hồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="cancelReject"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Lý do từ chối <span class="text-danger">*</span></label>
                    <textarea wire:model.defer="rejectReason"
                              class="form-control @error('rejectReason') is-invalid @enderror"
                              rows="4"
                              placeholder="Nhập lý do từ chối để kế toán lưu vết xử lý..."></textarea>
                    @error('rejectReason')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="cancelReject">Hủy</button>
                    <button type="button" class="btn btn-danger" wire:click="confirmReject">
                        <span wire:loading wire:target="confirmReject" class="spinner-border spinner-border-sm me-2"></span>
                        Xác nhận từ chối
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            const modalEl = document.getElementById('rejectReasonModal');
            if (!modalEl) return;

            const rejectModal = new bootstrap.Modal(modalEl);
            Livewire.on('open-reject-modal', () => rejectModal.show());
            Livewire.on('close-reject-modal', () => rejectModal.hide());
        });
    </script>
    @endpush
</div>
