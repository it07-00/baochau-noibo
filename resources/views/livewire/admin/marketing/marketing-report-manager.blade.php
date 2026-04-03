<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Báo cáo Marketing hàng ngày</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Báo cáo Marketing</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @unless($isViewOnly)
            <button class="btn btn-sm {{ $activeTab === 'form' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="$set('activeTab','form')">
                <i class="bi bi-pencil-square me-1"></i> Báo cáo hôm nay
            </button>
            @endunless
            <button class="btn btn-sm {{ $activeTab === 'history' ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="$set('activeTab','history')">
                <i class="bi bi-clock-history me-1"></i> Lịch sử
            </button>
        </div>
    </div>

    {{-- TAB FORM --}}
    @if($activeTab === 'form' && !$isViewOnly)
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="bi bi-megaphone me-2"></i>Báo cáo ngày {{ \Carbon\Carbon::parse($report_date)->format('d/m/Y') }}</h6>
                        @if($isEditing)
                        <span class="badge bg-success bg-opacity-75"><i class="bi bi-check-circle me-1"></i>Đã có báo cáo hôm nay</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <form wire:submit.prevent="save">
                        {{-- Ngày báo cáo --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Ngày báo cáo</label>
                            <input type="date" class="form-control form-control-sm" style="max-width:200px;" wire:model="report_date">
                        </div>

                        {{-- Số lượng content theo kênh --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Số lượng content / bài viết đã đăng hôm nay</label>
                            <div class="row g-2 mt-1">
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small text-muted mb-1"><i class="bi bi-facebook text-primary me-1"></i>Facebook</label>
                                    <input type="number" min="0" class="form-control form-control-sm text-center" wire:model="facebook_count">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small text-muted mb-1"><i class="bi bi-chat-dots text-success me-1"></i>Zalo</label>
                                    <input type="number" min="0" class="form-control form-control-sm text-center" wire:model="zalo_count">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small text-muted mb-1"><i class="bi bi-globe text-info me-1"></i>Website</label>
                                    <input type="number" min="0" class="form-control form-control-sm text-center" wire:model="website_count">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small text-muted mb-1"><i class="bi bi-tiktok me-1"></i>TikTok</label>
                                    <input type="number" min="0" class="form-control form-control-sm text-center" wire:model="tiktok_count">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small text-muted mb-1"><i class="bi bi-youtube text-danger me-1"></i>YouTube</label>
                                    <input type="number" min="0" class="form-control form-control-sm text-center" wire:model="youtube_count">
                                </div>
                            </div>
                        </div>

                        {{-- Nội dung đã làm --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nội dung / công việc đã làm hôm nay <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-sm" wire:model="content_details" rows="4"
                                placeholder="VD: Viết 3 bài về dịch vụ quan trắc môi trường, thiết kế 2 banner cho campaign tháng 4..."></textarea>
                            @error('content_details')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        {{-- Banner / ấn phẩm --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Banner / ấn phẩm đã tạo</label>
                            <textarea class="form-control form-control-sm" wire:model="banners" rows="2"
                                placeholder="VD: Banner tháng 4 cho Facebook (1200x628), Story Instagram dịch vụ xử lý chất thải..."></textarea>
                        </div>

                        {{-- Chỉ tiêu đạt được --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Chỉ tiêu đạt được</label>
                            <textarea class="form-control form-control-sm" wire:model="targets_achieved" rows="2"
                                placeholder="VD: Reach Facebook 500 người, tăng 20 followers, 3 leads từ website..."></textarea>
                        </div>

                        {{-- Ghi chú --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Ghi chú / vấn đề phát sinh</label>
                            <textarea class="form-control form-control-sm" wire:model="notes" rows="2"
                                placeholder="VD: Cần duyệt thêm ngân sách ads, tài khoản Facebook bị hạn chế..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bi bi-check-lg me-1"></i> {{ $isEditing ? 'Cập nhật báo cáo' : 'Lưu báo cáo' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB HISTORY --}}
    @if($activeTab === 'history')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Tháng</label>
                    <input type="month" class="form-control form-control-sm" wire:model.live="filterMonth">
                </div>
                @if($isManager || $isViewOnly)
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Nhân viên</label>
                    <select class="form-select form-select-sm" wire:model.live="filterUser">
                        <option value="">Tất cả</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-sm btn-outline-secondary" wire:click="$refresh"><i class="bi bi-arrow-clockwise me-1"></i>Làm mới</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-sm" style="font-size:0.875rem;">
                <thead class="bg-light">
                    <tr class="text-muted fw-bold">
                        <th class="ps-3" style="width:40px;">STT</th>
                        @if($isManager || $isViewOnly)<th>Nhân viên</th>@endif
                        <th style="width:110px;">Ngày</th>
                        <th class="text-center" style="width:80px;">Tổng bài</th>
                        <th class="text-center" style="width:80px;"><i class="bi bi-facebook text-primary"></i></th>
                        <th class="text-center" style="width:80px;"><i class="bi bi-chat-dots text-success"></i> Zalo</th>
                        <th class="text-center" style="width:80px;"><i class="bi bi-globe text-info"></i> Web</th>
                        <th class="text-center" style="width:80px;"><i class="bi bi-tiktok"></i></th>
                        <th>Nội dung</th>
                        <th>Banner</th>
                        <th>Chỉ tiêu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $row)
                    <tr>
                        <td class="ps-3">{{ ($history->currentPage()-1) * $history->perPage() + $loop->iteration }}</td>
                        @if($isManager || $isViewOnly)<td>{{ $row->user->name ?? '-' }}</td>@endif
                        <td class="fw-semibold">{{ $row->report_date->format('d/m/Y') }}</td>
                        <td class="text-center fw-bold text-primary">{{ $row->total_content }}</td>
                        <td class="text-center">{{ $row->facebook_count ?: '-' }}</td>
                        <td class="text-center">{{ $row->zalo_count ?: '-' }}</td>
                        <td class="text-center">{{ $row->website_count ?: '-' }}</td>
                        <td class="text-center">{{ $row->tiktok_count ?: '-' }}</td>
                        <td class="small text-muted" style="max-width:200px; white-space:pre-wrap;">{{ \Illuminate\Support\Str::limit($row->content_details, 80) }}</td>
                        <td class="small text-muted" style="max-width:150px;">{{ \Illuminate\Support\Str::limit($row->banners, 60) }}</td>
                        <td class="small text-muted" style="max-width:150px;">{{ \Illuminate\Support\Str::limit($row->targets_achieved, 60) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ ($isManager || $isViewOnly) ? 11 : 10 }}" class="text-center py-5 text-muted">Không có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($history->hasPages())
        <div class="px-3 py-3 border-top">
            {{ $history->links('livewire.admin.users.pagination') }}
        </div>
        @endif
    </div>
    @endif
</div>
