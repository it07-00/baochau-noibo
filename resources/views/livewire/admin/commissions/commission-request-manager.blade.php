<div>
    <div class="page-title-box d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Quản lý Yêu cầu chi hoa hồng</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Quản lý Yêu cầu chi hoa hồng</li>
                </ol>
            </nav>
        </div>
        <div class="page-title-right">
            <a href="{{ route('app.commissions.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Thêm mới
            </a>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0">Bộ lọc Yêu cầu chi hoa hồng</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Phòng ban</label>
                    <select wire:model.live="departmentFilter" class="form-select">
                        <option value="">Chọn phòng ban</option>
                        {{-- Add departments here --}}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tình trạng</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Chọn tình trạng</option>
                        <option value="Chờ chi">Chờ chi</option>
                        <option value="Đã chi">Đã chi</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tìm kiếm</label>
                    <div class="input-group">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Tìm hợp đồng, khách hàng, người nhận...">
                        <button class="btn btn-info text-white"><i class="bi bi-search"></i> Lọc</button>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100"><i class="bi bi-file-earmark-text"></i> Quy trình</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0">Danh sách Yêu cầu chi hoa hồng</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Thông tin hợp đồng</th>
                            <th>Khách hàng</th>
                            <th class="text-center">Tình trạng thực hiện</th>
                            <th class="text-center">Ngày gửi yêu cầu</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td class="ps-4">
                                    <div class="mb-1">
                                        <small class="text-muted">Số HĐ BC:</small><br>
                                        <strong>{{ $request->contract->shd_ad ?? 'N/A' }}</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted">Nhân viên CS:</small>
                                        <span>{{ $request->contract->staff->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($request->contract && $request->contract->customer)
                                        <div class="fw-bold">{{ $request->contract->customer->name }}</div>
                                        <div class="small text-muted">
                                            {{ $request->contract->customer->contact_person }} - {{ $request->contract->customer->phone }} - {{ $request->contract->customer->email }}
                                        </div>
                                        <div class="small text-muted text-truncate" style="max-width: 400px;">
                                            {{ $request->contract->customer->address }}
                                        </div>
                                    @else
                                        <strong>{{ $request->referrer_info ?? 'N/A' }}</strong>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($request->status === 'Đã chi')
                                        <span class="badge bg-soft-success text-success px-3 py-2">
                                            Đã chi<br>
                                            <small>{{ $request->processed_at?->format('d/m/Y') }}</small>
                                        </span>
                                    @else
                                        <span class="badge bg-soft-warning text-warning px-3 py-2">
                                            {{ $request->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $request->created_at->format('d/m/Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="#" class="text-success"><i class="bi bi-copy"></i></a>
                                        <a href="#" class="text-danger"><i class="bi bi-eye"></i></a>
                                        <a href="#" class="text-info"><i class="bi bi-chat-dots"></i></a>
                                        <a href="{{ route('app.commissions.edit', $request->id) }}" class="text-primary"><i class="bi bi-pencil-square"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Không tìm thấy yêu cầu nào.</td>
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
</div>
