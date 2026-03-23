<div>
    <div class="page-title-box d-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0">Quản lý Báo cáo ngày</h4>
        <div class="page-title-right">
            <button wire:click="openCreateModal" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Gửi báo cáo mới
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Ngày báo cáo</label>
                    <input type="date" wire:model.live="dateFilter" class="form-control">
                </div>
                @if(auth()->user()->can('daily-reports.view-all'))
                <div class="col-md-3">
                    <label class="form-label">Nhân viên</label>
                    <select wire:model.live="userIdFilter" class="form-select">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-4">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Tìm nội dung, kế hoạch...">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Ngày</th>
                            <th>Nhân viên</th>
                            <th>Nội dung công việc</th>
                            <th>Kế hoạch ngày mai</th>
                            <th>Khó khăn/Kiến nghị</th>
                            <th style="width: 120px;" class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $loop->iteration + ($reports->currentPage() - 1) * $perPage }}</td>
                                <td>{{ $report->date->format('d/m/Y') }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <strong>{{ $report->user->name }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-wrap" style="max-width: 300px;">
                                        {!! nl2br(e($report->content)) !!}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-wrap" style="max-width: 300px;">
                                        {!! nl2br(e($report->plan)) !!}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-wrap" style="max-width: 200px;">
                                        {!! nl2br(e($report->issues)) !!}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button wire:click="edit({{ $report->id }})" class="btn btn-sm btn-soft-info" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button onclick="confirmDelete({{ $report->id }})" class="btn btn-sm btn-soft-danger" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy báo cáo nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reports->hasPages())
                <div class="p-3 border-top">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">
                        {{ $reportId ? 'Chỉnh sửa báo cáo' : 'Gửi báo cáo ngày mới' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Ngày báo cáo <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Nội dung công việc hôm nay <span class="text-danger">*</span></label>
                                <textarea wire:model="content" class="form-control @error('content') is-invalid @enderror" rows="5" placeholder="Mô tả chi tiết những việc bạn đã làm..."></textarea>
                                @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Kế hoạch công việc ngày mai <span class="text-danger">*</span></label>
                                <textarea wire:model="plan" class="form-control @error('plan') is-invalid @enderror" rows="4" placeholder="Bạn dự kiến sẽ làm gì vào ngày mai?"></textarea>
                                @error('plan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Khó khăn hoặc Kiến nghị (nếu có)</label>
                                <textarea wire:model="issues" class="form-control @error('issues') is-invalid @enderror" rows="3" placeholder="Ghi chú những khó khăn gặp phải hoặc đề xuất..."></textarea>
                                @error('issues') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $reportId ? 'Cập nhật' : 'Gửi báo cáo' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('openModal', () => {
            const modal = new bootstrap.Modal(document.getElementById('reportModal'));
            modal.show();
        });

        $wire.on('closeModal', () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
            if (modal) modal.hide();
        });

        window.confirmDelete = function(id) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Bạn không thể hoàn tác hành động này!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.dispatch('deleteConfirmed', { id: id });
                }
            });
        }
    </script>
    @endscript
</div>
