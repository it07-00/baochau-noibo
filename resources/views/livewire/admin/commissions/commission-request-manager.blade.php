<div class="commission-page">
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
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                Tạo yêu cầu
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4" aria-label="Tổng quan yêu cầu hoa hồng">
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 border-start border-4 border-primary shadow-sm h-100 position-relative overflow-hidden">
                <div class="card-body p-3 p-xl-4">
                    <span class="position-absolute top-0 end-0 m-3 d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary fs-5 lh-1" style="width: 40px; height: 40px;"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                    <p class="text-muted mb-1">Tổng yêu cầu</p>
                    <h4 class="mb-0">{{ number_format($summary['total']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 border-start border-4 border-secondary shadow-sm h-100 position-relative overflow-hidden">
                <div class="card-body p-3 p-xl-4">
                    <span class="position-absolute top-0 end-0 m-3 d-inline-flex align-items-center justify-content-center rounded-3 bg-secondary-subtle text-secondary fs-5 lh-1" style="width: 40px; height: 40px;"><i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i></span>
                    <p class="text-muted mb-1">Dự chi</p>
                    <h4 class="mb-0 text-secondary">{{ number_format($summary['estimated']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 border-start border-4 border-warning shadow-sm h-100 position-relative overflow-hidden">
                <div class="card-body p-3 p-xl-4">
                    <span class="position-absolute top-0 end-0 m-3 d-inline-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning fs-5 lh-1" style="width: 40px; height: 40px;"><i class="fa-solid fa-clock" aria-hidden="true"></i></span>
                    <p class="text-muted mb-1">Chờ chi</p>
                    <h4 class="mb-0 text-warning">{{ number_format($summary['pending']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 border-start border-4 border-success shadow-sm h-100 position-relative overflow-hidden">
                <div class="card-body p-3 p-xl-4">
                    <span class="position-absolute top-0 end-0 m-3 d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success fs-5 lh-1" style="width: 40px; height: 40px;"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                    <p class="text-muted mb-1">Đã chi</p>
                    <h4 class="mb-0 text-success">{{ number_format($summary['paid']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 border-start border-4 border-danger shadow-sm h-100 position-relative overflow-hidden">
                <div class="card-body p-3 p-xl-4">
                    <span class="position-absolute top-0 end-0 m-3 d-inline-flex align-items-center justify-content-center rounded-3 bg-danger-subtle text-danger fs-5 lh-1" style="width: 40px; height: 40px;"><i class="fa-solid fa-money-bill-transfer" aria-hidden="true"></i></span>
                    <p class="text-muted mb-1">Tổng đã chi</p>
                    <h4 class="mb-0 text-danger">{{ number_format($summary['total_payout'], 0, ',', '.') }} đ</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 border-start border-4 border-info shadow-sm h-100 position-relative overflow-hidden">
                <div class="card-body p-3 p-xl-4">
                    <span class="position-absolute top-0 end-0 m-3 d-inline-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info fs-5 lh-1" style="width: 40px; height: 40px;"><i class="fa-solid fa-filter-circle-dollar" aria-hidden="true"></i></span>
                    <p class="text-muted mb-1">Tổng tiền lọc</p>
                    <h4 class="mb-0 text-primary">{{ number_format($summary['amount'], 0, ',', '.') }} đ</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Bộ lọc</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$refresh" wire:loading.attr="disabled" aria-label="Làm mới danh sách yêu cầu hoa hồng">
                <i class="fa-solid fa-rotate-right me-1"></i>
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
                        <option value="Dự chi">Dự chi</option>
                        <option value="Đã duyệt">Đã duyệt (Chờ chi)</option>
                        <option value="Đã chi">Đã chi</option>
                        <option value="Từ chối">Từ chối</option>
                    </select>
                </div>
                <div class="col-12 col-lg-2">
                    <label class="form-label">Tháng yêu cầu</label>
                    <input type="month" wire:model.live="requestMonthFilter" class="form-control">
                </div>
                @if($canFilterByRequester)
                    <div class="col-12 col-lg-2">
                        <label class="form-label">Người yêu cầu</label>
                        <select wire:model.live="requesterFilter" class="form-select">
                            <option value="">Tất cả người yêu cầu</option>
                            @foreach($requesters as $requester)
                                <option value="{{ $requester->id }}">{{ $requester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-12 @if($canFilterByRequester) col-lg-3 @else col-lg-5 @endif">
                    <label class="form-label">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
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
                            <th class="text-center mnw-45px w-45px" >STT</th>
                            <th class="ps-4 mnw-300px" >Hợp đồng / Khách hàng</th>
                            <th class="mnw-220px">Người nhận</th>
                            <th class="text-center mnw-170px">Loại hợp đồng</th>
                            <th class="text-end mnw-150px" >Số tiền</th>
                            <th class="text-center mnw-180px" >Tình trạng</th>
                            <th class="text-center mnw-120px" >Ngày gửi</th>
                            @canany(['commissions.edit', 'commissions.delete', 'commissions.create'])
                            <th class="text-end pe-4 mnw-260px" >Thao tác</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td class="text-center text-muted  fw-semibold">{{ ($requests->currentPage() - 1) * $requests->perPage() + $loop->iteration }}</td>
                                <td class="ps-4">
                                    <div class="fw-semibold text-primary mb-1">BC {{ $request->contract_number }}</div>
                                    @if($request->contract && $request->contract->customer)
                                        <div class="fw-semibold">{{ $request->contract->customer->name }}</div>
                                        <div class=" text-muted text-truncate mxw-320px" >
                                            {{ $request->contract->customer->address }}
                                        </div>
                                    @else
                                        <div class=" text-muted">{{ $request->referrer_info ?: 'Không có thông tin khách hàng' }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $request->receiver_name }}</div>
                                    <div class=" text-muted">{{ $request->receiver_phone ?: 'Chưa có số điện thoại' }}</div>
                                    <div class="text-muted text-truncate text-truncate-200">
                                        @if($request->bank_code && $request->bank_number)
                                            {{ $request->bank_code }} - {{ $request->bank_number }}
                                        @else
                                            {{ $request->bank_account ?: 'Chưa có tài khoản ngân hàng' }}
                                        @endif
                                    </div>
                                    @if($request->qr_url)
                                        <div class="mt-1">
                                            <button type="button" 
                                                    class="btn btn-sm btn-link p-0 text-decoration-none text-info fw-bold d-inline-flex align-items-center gap-1"
                                                    wire:click="viewRequest({{ $request->id }})">
                                                <i class="fa-solid fa-qrcode me-1"></i> Xem QR thanh toán
                                            </button>
                                        </div>
                                    @endif
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
                                            <small>{{ $request->processed_at?->format('H:i - d/m/Y') }}</small>
                                        </span>
                                    @elseif($request->status === 'Đã duyệt')
                                        <span class="badge bg-soft-warning text-warning px-3 py-2">
                                            Đã duyệt
                                            <br>
                                            <small>Chờ chi</small>
                                        </span>
                                    @elseif($request->status === 'Từ chối')
                                        <span class="badge bg-soft-danger text-danger px-3 py-2">
                                            Từ chối
                                            <br>
                                            <small>{{ $request->processed_at?->format('H:i - d/m/Y') }}</small>
                                        </span>
                                        @if($this->rejectionReason($request->notes))
                                            <div class=" text-muted mt-1 mxw-190px"  title="{{ $this->rejectionReason($request->notes) }}">
                                                Lý do: {{ $this->rejectionReasonPreview($request->notes, 70) }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge bg-soft-secondary text-secondary px-3 py-2">Dự chi</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $request->created_at->format('d/m/Y') }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-outline-info" wire:click="viewRequest({{ $request->id }})">
                                            <i class="fa-solid fa-eye me-1"></i> Xem chi tiết
                                        </button>
                                        @if($request->payment_bill_path)
                                            <a href="{{ $request->payment_bill_url }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="fa-solid fa-file-text me-1"></i> Xem hóa đơn
                                            </a>
                                        @elseif($request->status === 'Đã duyệt' && auth()->check() && (auth()->user()->hasRole(App\Enums\Role::KE_TOAN->value) || auth()->user()->hasRole(App\Enums\Role::GIAM_DOC->value)))
                                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openUploadBillModal({{ $request->id }})">
                                                <i class="fa-solid fa-upload me-1"></i> Up hóa đơn
                                            </button>
                                        @endif
                                        @if($canApprove && $request->status === 'Dự chi')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    wire:click="approve({{ $request->id }})"
                                                    wire:confirm="Xác nhận duyệt chi yêu cầu này?">
                                                <i class="fa-solid fa-circle-check me-1"></i>
                                                Duyệt
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    wire:click="startReject({{ $request->id }})">
                                                <i class="fa-solid fa-xmark-circle me-1"></i>
                                                Từ chối
                                            </button>
                                        @endif

                                        @php
                                            $isOwner = auth()->check() && $request->user_id === auth()->id();
                                            $rowCanEdit = $canEdit || ($isOwner && !auth()->user()->hasRole(App\Enums\Role::KE_TOAN->value));
                                            $rowCanDelete = $canDelete || $isOwner;
                                        @endphp

                                        @if($rowCanEdit && !in_array($request->status, ['Đã duyệt', 'Đã chi'], true))
                                            <a href="{{ route('app.commissions.edit', $request->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-pen-square me-1"></i>
                                                Sửa
                                            </a>
                                        @endif

                                        @if($rowCanDelete && (!in_array($request->status, ['Đã duyệt', 'Đã chi'], true) || auth()->user()->hasRole(App\Enums\Role::KE_TOAN->value)))
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    wire:click="delete({{ $request->id }})"
                                                    wire:confirm="Xác nhận xóa yêu cầu này?">
                                                <i class="fa-solid fa-trash me-1"></i>
                                                Xóa
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-inbox d-block mb-2" aria-hidden="true"></i>
                                    <strong class="d-block text-body mb-1">Chưa có yêu cầu phù hợp</strong>
                                    <span>Thử thay đổi bộ lọc hoặc tạo yêu cầu hoa hồng mới.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($requests->isNotEmpty())
                        <tfoot class="table-light fw-bold border-top-2">
                            <tr>
                                <td colspan="5" class="text-end ps-4">
                                    <div class="d-flex justify-content-end align-items-center gap-4">
                                        <div>
                                            <span class="text-secondary fw-semibold">Tổng dự chi:</span>
                                            <span class="font-monospace">{{ number_format($summary['total_estimated'], 0, ',', '.') }} đ</span>
                                        </div>
                                        <div class="border-start ps-4 py-1">
                                            <span class="text-warning fw-semibold">Tổng chờ chi:</span>
                                            <span class="text-warning font-monospace">{{ number_format($summary['total_pending_payout'], 0, ',', '.') }} đ</span>
                                        </div>
                                        <div class="border-start ps-4 py-1">
                                            <span class="text-success">Tổng đã chi:</span>
                                            <span class="text-success font-monospace">{{ number_format($summary['total_payout'], 0, ',', '.') }} đ</span>
                                        </div>
                                    </div>
                                </td>
                                @canany(['commissions.edit', 'commissions.delete', 'commissions.create'])
                                    <td colspan="3"></td>
                                @else
                                    <td colspan="2"></td>
                                @endcanany
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if($requests->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $requests->links('pagination::bootstrap-5') }}
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

    <!-- View QR & Details Modal -->
    <div wire:ignore.self class="modal fade" id="viewRequestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i class="fa-solid fa-credit-card"></i> Chi tiết thanh toán & QR Code
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="closeView"></button>
                </div>
                <div class="modal-body p-4">
                    @if($viewingRequest)
                        <div class="row g-4">
                            <!-- Left Column: Recipient Details (col-md-6) -->
                            <div class="col-md-6 d-flex">
                                <div class="card border-light-subtle shadow-sm w-100 h-100">
                                    <div class="card-header bg-light border-bottom py-3">
                                        <h6 class="card-title mb-0 d-flex align-items-center gap-2 text-primary fw-bold">
                                            <i class="fa-solid fa-user-bounding-box"></i> Thông tin người nhận
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex flex-column gap-3" style="font-size: 1.05rem;">
                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                <span class="text-muted d-flex align-items-center gap-2"><i class="fa-solid fa-user text-secondary"></i> Họ và tên:</span>
                                                <span class="fw-bold text-dark text-end">{{ strtoupper(\Illuminate\Support\Str::ascii($viewingRequest->receiver_name)) }}</span>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                <span class="text-muted d-flex align-items-center gap-2"><i class="fa-solid fa-phone text-secondary"></i> Số điện thoại:</span>
                                                <span class="fw-bold text-dark text-end">{{ $viewingRequest->receiver_phone ?: 'Chưa cập nhật' }}</span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                <span class="text-muted d-flex align-items-center gap-2"><i class="fa-solid fa-file-text text-secondary"></i> Hợp đồng:</span>
                                                <span class="fw-bold text-primary text-end">BC {{ $viewingRequest->contract_number }}</span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                <span class="text-muted d-flex align-items-center gap-2"><i class="fa-solid fa-money-bill-wave text-secondary"></i> Số tiền:</span>
                                                <span class="fw-bold text-danger fs-5 text-end">{{ number_format($viewingRequest->amount, 0, ',', '.') }} đ</span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                <span class="text-muted d-flex align-items-center gap-2"><i class="fa-solid fa-chart-line text-secondary"></i> Trạng thái:</span>
                                                <span class="fw-bold text-end" style="font-size: 0.95rem;">
                                                    @if($viewingRequest->status === 'Đã chi')
                                                        <span class="text-success"><i class="fa-solid fa-circle-check-fill"></i> Đã chi ({{ $viewingRequest->processed_at?->format('H:i - d/m/Y') }})</span>
                                                    @elseif($viewingRequest->status === 'Đã duyệt')
                                                        <span class="text-warning"><i class="fa-solid fa-certificate-fill"></i> Đã duyệt - Chờ chi</span>
                                                    @elseif($viewingRequest->status === 'Từ chối')
                                                        <span class="text-danger"><i class="fa-solid fa-xmark-circle-fill"></i> Từ chối ({{ $viewingRequest->processed_at?->format('H:i - d/m/Y') }})</span>
                                                    @else
                                                        <span class="text-secondary"><i class="fa-solid fa-calculator"></i> Dự chi</span>
                                                    @endif
                                                </span>
                                            </div>

                                            @if($viewingRequest->bank_code && $viewingRequest->bank_number)
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                    <span class="text-muted d-flex align-items-center gap-2"><i class="fa-solid fa-building-columns text-secondary"></i> Ngân hàng:</span>
                                                    <span class="fw-bold text-dark font-monospace text-end">{{ $viewingRequest->bank_code }}</span>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                    <span class="text-muted d-flex align-items-center gap-2"><i class="fa-solid fa-credit-card text-secondary"></i> Số tài khoản:</span>
                                                    <span class="fw-bold text-dark font-monospace text-end">{{ $viewingRequest->bank_number }}</span>
                                                </div>
                                            @endif

                                            @if($viewingRequest->bank_account)
                                                <div class="d-flex justify-content-between align-items-start border-bottom pb-2">
                                                    <span class="text-muted d-flex align-items-center gap-2"><i class="fa-solid fa-circle-info text-secondary"></i> Thông tin khác:</span>
                                                    <span class="text-dark small fw-semibold text-end ms-3">{{ $viewingRequest->bank_account }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: QR Code Preview (col-md-6) -->
                            <div class="col-md-6 d-flex">
                                @if($viewingRequest->qr_url)
                                    <div class="card border-light-subtle shadow-sm w-100 h-100">
                                        <div class="card-header bg-light border-bottom py-3 text-center">
                                            <h6 class="card-title mb-0 fw-bold text-secondary">
                                                <i class="fa-solid fa-qrcode-scan me-1"></i> Quét mã QR chuyển khoản
                                            </h6>
                                        </div>
                                        <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center bg-white">
                                            <img src="{{ $viewingRequest->qr_url }}" class="img-fluid rounded border shadow-sm" style="width: 100%; max-width: 340px; height: auto; aspect-ratio: 1/1; object-fit: contain;" alt="Payment QR Code">
                                        </div>
                                    </div>
                                @else
                                    <div class="card border-light-subtle shadow-sm w-100 h-100">
                                        <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center">
                                            <i class="fa-solid fa-triangle-exclamation text-warning mb-3" style="font-size: 3rem;"></i>
                                            <h6 class="fw-bold text-secondary mb-1">Không tạo được QR</h6>
                                            <span class="text-muted small">Không tìm thấy tài khoản ngân hàng của người nhận.</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Payment Bill section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-light-subtle shadow-sm">
                                    <div class="card-header bg-light border-bottom py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0 d-flex align-items-center gap-2 text-primary fw-bold">
                                            <i class="fa-solid fa-file-image"></i> Hóa đơn thanh toán / Minh chứng
                                        </h6>
                                        @if($viewingRequest->payment_bill_path)
                                            <span class="badge bg-soft-success text-success px-2 py-1"><i class="fa-solid fa-circle-check"></i> Đã tải lên</span>
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary px-2 py-1">Chưa có hóa đơn</span>
                                        @endif
                                    </div>
                                    <div class="card-body p-3">
                                        @if($viewingRequest->payment_bill_path)
                                            <div class="d-flex flex-column align-items-center gap-3">
                                                @php
                                                    $isPdf = Str::endsWith(strtolower($viewingRequest->payment_bill_path), '.pdf');
                                                @endphp

                                                @if($isPdf)
                                                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded w-100 border">
                                                        <i class="fa-solid fa-file-pdf-fill text-danger fs-1"></i>
                                                        <div class="flex-grow-1">
                                                            <div class="fw-bold text-dark text-truncate">Hóa đơn thanh toán (PDF)</div>
                                                            <span class="text-muted small">Nhấp để mở hoặc tải về file PDF.</span>
                                                        </div>
                                                        <a href="{{ $viewingRequest->payment_bill_url }}" target="_blank" class="btn btn-primary d-flex align-items-center gap-1">
                                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Xem PDF
                                                        </a>
                                                    </div>
                                                @else
                                                    <div class="text-center w-100">
                                                        <a href="{{ $viewingRequest->payment_bill_url }}" target="_blank" title="Nhấp để phóng to ảnh">
                                                            <img src="{{ $viewingRequest->payment_bill_url }}" class="img-thumbnail rounded border shadow-sm" style="max-height: 300px; width: auto; object-fit: contain;" alt="Payment Bill">
                                                        </a>
                                                        <div class="mt-2 text-muted small"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp vào ảnh để xem chi tiết kích thước đầy đủ</div>
                                                    </div>
                                                @endif

                                                @if(auth()->check() && (auth()->user()->hasRole(App\Enums\Role::KE_TOAN->value) || auth()->user()->hasRole(App\Enums\Role::GIAM_DOC->value)))
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1 mt-2" 
                                                            wire:click="deleteBill" 
                                                            wire:confirm="Bạn có chắc chắn muốn xóa hóa đơn thanh toán này?">
                                                        <i class="fa-solid fa-trash"></i> Xóa hóa đơn & Tải lại
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-center py-3">
                                                <p class="text-muted mb-3">Chưa có hóa đơn/minh chứng thanh toán nào được cập nhật.</p>

                                                @if($viewingRequest->status === 'Đã duyệt' && auth()->check() && (auth()->user()->hasRole(App\Enums\Role::KE_TOAN->value) || auth()->user()->hasRole(App\Enums\Role::GIAM_DOC->value)))
                                                    <div class="d-flex flex-column align-items-center justify-content-center border border-dashed rounded-3 p-4 bg-light position-relative" style="border-style: dashed !important; border-width: 2px !important; border-color: #dee2e6 !important;">
                                                        <i class="fa-solid fa-cloud-arrow-up text-primary fs-2 mb-2"></i>
                                                        <h6 class="fw-semibold text-secondary mb-2">Tải lên hóa đơn (Minh chứng)</h6>
                                                        <p class="text-muted small mb-3">Chấp nhận JPG, PNG, JPEG hoặc PDF. Tối đa 10MB.</p>

                                                        <div class="w-100" style="max-width: 400px;">
                                                            <input type="file" 
                                                                   wire:model="billFile" 
                                                                   id="billFileUpload"
                                                                   class="form-control @error('billFile') is-invalid @enderror">
                                                            @error('billFile')
                                                                <div class="invalid-feedback mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <div wire:loading wire:target="billFile" class="mt-3 text-info small">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                                            Đang tải file lên tạm thời...
                                                        </div>

                                                        @if($billFile && !$errors->has('billFile'))
                                                            <div class="mt-3 d-flex align-items-center gap-2">
                                                                <span class="text-success small fw-semibold"><i class="fa-solid fa-check-circle me-1"></i> Sẵn sàng tải lên: {{ $billFile->getClientOriginalName() }}</span>
                                                                <button type="button" 
                                                                        class="btn btn-success btn-sm d-flex align-items-center gap-1"
                                                                        wire:click="uploadBill">
                                                                    <i class="fa-solid fa-cloud-arrow-up"></i> Xác nhận lưu
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                            <div>Đang tải thông tin...</div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    @if($viewingRequest && $canApprove && $viewingRequest->status === 'Dự chi')
                        <button type="button" class="btn btn-success" 
                                wire:click="approve({{ $viewingRequest->id }})"
                                wire:confirm="Xác nhận duyệt chi yêu cầu này?">
                            <i class="fa-solid fa-circle-check me-1"></i> Duyệt chi
                        </button>
                    @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="closeView">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Bill Modal -->
    <div wire:ignore.self class="modal fade" id="uploadBillModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-cloud-arrow-up-fill me-1"></i> Tải lên hóa đơn thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="closeUploadBillModal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($uploadingBillRequestId)
                        @php
                            $uploadingReq = App\Models\CommissionRequest::find($uploadingBillRequestId);
                        @endphp
                        @if($uploadingReq)
                            <div class="mb-3 p-3 bg-light rounded border">
                                <div class="row g-2" style="font-size: 0.95rem;">
                                    <div class="col-6 text-muted">Người nhận:</div>
                                    <div class="col-6 fw-bold text-dark">{{ $uploadingReq->receiver_name }}</div>
                                    <div class="col-6 text-muted">Số tiền:</div>
                                    <div class="col-6 fw-bold text-danger">{{ number_format($uploadingReq->amount, 0, ',', '.') }} đ</div>
                                    <div class="col-6 text-muted">Hợp đồng:</div>
                                    <div class="col-6 fw-bold text-primary">BC {{ $uploadingReq->contract_number }}</div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="d-flex flex-column align-items-center justify-content-center border border-dashed rounded-3 p-4 bg-light position-relative" style="border-style: dashed !important; border-width: 2px !important; border-color: #dee2e6 !important;">
                        <i class="fa-solid fa-cloud-arrow-up text-warning fs-2 mb-2"></i>
                        <h6 class="fw-semibold text-secondary mb-2">Chọn file minh chứng thanh toán</h6>
                        <p class="text-muted small mb-3">Chấp nhận JPG, PNG, JPEG hoặc PDF. Tối đa 10MB.</p>

                        <div class="w-100">
                            <input type="file" 
                                   wire:model="billFile" 
                                   id="quickBillFileUpload"
                                   class="form-control @error('billFile') is-invalid @enderror">
                            @error('billFile')
                                <div class="invalid-feedback mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div wire:loading wire:target="billFile" class="mt-3 text-info small">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Đang tải file lên tạm thời...
                        </div>

                        @if($billFile && !$errors->has('billFile'))
                            <div class="mt-3 d-flex align-items-center gap-2">
                                <span class="text-success small fw-semibold"><i class="fa-solid fa-check-circle me-1"></i> Sẵn sàng: {{ $billFile->getClientOriginalName() }}</span>
                                <button type="button" 
                                        class="btn btn-success btn-sm d-flex align-items-center gap-1"
                                        wire:click="uploadBill">
                                    <i class="fa-solid fa-cloud-arrow-up"></i> Lưu lại
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="closeUploadBillModal">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            const rejectModalEl = document.getElementById('rejectReasonModal');
            const rejectModal = rejectModalEl ? new bootstrap.Modal(rejectModalEl) : null;
            
            Livewire.on('open-reject-modal', () => rejectModal && rejectModal.show());
            Livewire.on('close-reject-modal', () => rejectModal && rejectModal.hide());

            const viewModalEl = document.getElementById('viewRequestModal');
            const viewModal = viewModalEl ? new bootstrap.Modal(viewModalEl) : null;
            
            Livewire.on('open-view-modal', () => viewModal && viewModal.show());
            Livewire.on('close-view-modal', () => viewModal && viewModal.hide());

            const uploadBillModalEl = document.getElementById('uploadBillModal');
            const uploadBillModal = uploadBillModalEl ? new bootstrap.Modal(uploadBillModalEl) : null;
            
            Livewire.on('open-upload-bill-modal', () => uploadBillModal && uploadBillModal.show());
            Livewire.on('close-upload-bill-modal', () => uploadBillModal && uploadBillModal.hide());
        });
    </script>
    @endpush
</div>
