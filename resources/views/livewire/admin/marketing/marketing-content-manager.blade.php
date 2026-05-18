@once
    @push('styles')
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/marketing-content-manager.css') }}?v={{ config('app.version') }}">
    @endpush
@endonce

<div class="marketing-content-page">
    @php
        $tableColspan = $isMarketing ? 8 : 9;
    @endphp

    <div class="mc-page-header page-header d-flex align-items-start align-items-lg-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-1">Kế hoạch content Marketing</h4>
            <p class="text-muted mb-0">Danh sách nội dung, trạng thái duyệt và lịch đăng theo dạng bảng.</p>
        </div>
        <div class="d-flex gap-2 ms-auto flex-wrap justify-content-end align-items-center">
            @if($isMarketing)
                <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1" wire:click="openCreate">
                    <i class="bi bi-plus-lg"></i> Tạo bài mới
                </button>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mc-table-card">
        <div class="table-responsive overflow-auto">
            <table class="table table-hover align-middle mb-0 mc-table" style="min-width: 980px;">
                <thead class="mc-table-head">
                    <tr>
                        <th class="ps-3 w-50px">#</th>
                        <th class="minw-300px">Tiêu đề / Nội dung</th>
                        <th class="w-150px">Ngày đăng</th>
                        @if(!$isMarketing)
                            <th class="w-150px">Phụ trách</th>
                        @endif
                        <th class="w-140px text-center">Trạng thái</th>
                        <th class="w-100px text-center">Ảnh</th>
                        <th class="w-160px">Cập nhật</th>
                        <th class="w-220px">Ghi chú</th>
                        <th class="text-center pe-3 w-220px">#</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contents as $item)
                        @php
                            $color = $statusColors[$item->status] ?? 'secondary';
                            $label = $statusLabels[$item->status] ?? $item->status;
                            $imgs = $item->images ?? [];
                            $scheduledAt = $item->scheduled_at;
                            $scheduleIcon = 'bi bi-calendar3';
                            $scheduleText = 'Chưa chốt lịch đăng';

                            if ($scheduledAt) {
                                if ($scheduledAt->isToday()) {
                                    $scheduleIcon = 'bi bi-lightning-charge';
                                    $scheduleText = 'Đăng hôm nay';
                                } elseif ($scheduledAt->isPast()) {
                                    $scheduleIcon = 'bi bi-exclamation-circle';
                                    $scheduleText = 'Quá lịch ' . $scheduledAt->format('d/m');
                                } elseif ($scheduledAt->diffInDays(now()) <= 7) {
                                    $scheduleIcon = 'bi bi-calendar-week';
                                    $scheduleText = 'Trong ' . $scheduledAt->diffInDays(now()) . ' ngày';
                                } else {
                                    $scheduleText = 'Lên lịch ' . $scheduledAt->format('d/m');
                                }
                            }
                        @endphp

                        <tr wire:key="marketing-content-row-{{ $item->id }}">
                            <td class="ps-3 text-muted small">{{ ($contents->currentPage() - 1) * $contents->perPage() + $loop->iteration }}</td>
                            <td>
                                <div class="mc-table-title">{{ $item->title }}</div>
                                <div class="mc-table-caption">{{ \Illuminate\Support\Str::limit($item->content, 180) }}</div>
                            </td>
                            <td>
                                <div class="mc-table-date">{{ $scheduledAt?->format('d/m/Y') ?? 'Chưa chọn' }}</div>
                                <div class="mc-table-subtext">
                                    <i class="{{ $scheduleIcon }} me-1"></i>{{ $scheduleText }}
                                </div>
                            </td>
                            @if(!$isMarketing)
                                <td>
                                    <div class="mc-table-date">{{ $item->user->name }}</div>
                                    <div class="mc-table-subtext">{{ $item->created_at?->format('d/m/Y') }}</div>
                                </td>
                            @endif
                            <td class="text-center">
                                <span class="mc-status-badge mc-status-badge-{{ $color }}">
                                    <span class="mc-status-dot bg-{{ $color }}"></span>
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if(count($imgs) > 0)
                                    <span class="mc-file-count">{{ count($imgs) }} ảnh</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="mc-table-date">{{ $item->updated_at?->format('d/m/Y H:i') }}</div>
                                <div class="mc-table-subtext">{{ $item->updated_at?->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($item->status === 'rejected' && $item->reviewer_note)
                                    <div class="mc-table-note">{{ \Illuminate\Support\Str::limit($item->reviewer_note, 120) }}</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="mc-table-actions d-flex justify-content-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openDetail({{ $item->id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    @if($isMarketing && $item->isEditable())
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $item->id }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif

                                    @if($isMarketing && $item->isDraft())
                                        <button type="button" class="btn btn-sm btn-warning" wire:click="submitForReview({{ $item->id }})" wire:confirm="Gửi bài này để duyệt?">
                                            <i class="bi bi-send"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteContent({{ $item->id }})" wire:confirm="Xóa bài content này?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif

                                    @if($isReviewer && $item->isPending())
                                        <button type="button" class="btn btn-sm btn-success" wire:click="openReview({{ $item->id }})">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tableColspan }}" class="text-center py-5 text-muted">Không có kế hoạch content phù hợp</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contents->hasPages())
            <div class="px-3 py-3 border-top">
                {{ $contents->links() }}
            </div>
        @endif
    </div>

    <div wire:ignore.self class="modal fade" id="contentFormModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header mc-modal-header border-0 px-4 py-4">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">{{ $isEditing ? 'Chỉnh sửa bài content' : 'Tạo bài content mới' }}</h5>
                        <p class="mc-modal-copy mb-0">Nhập thông tin chính và lưu nháp.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 p-lg-5">
                        <div class="mc-modal-panel mb-4">
                            <div class="row g-3">
                                <div class="col-lg-8">
                                    <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('formTitle') is-invalid @enderror"
                                        wire:model.defer="formTitle" placeholder="Nhập tiêu đề bài viết">
                                    @error('formTitle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">Ngày đăng <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('formScheduledAt') is-invalid @enderror"
                                        wire:model.defer="formScheduledAt">
                                    @error('formScheduledAt')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Nội dung caption <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('formContent') is-invalid @enderror"
                                        wire:model.defer="formContent" rows="8"
                                        placeholder="Nhập nội dung cần đăng..."></textarea>
                                    @error('formContent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mc-modal-panel">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                <div class="fw-semibold">Hình ảnh</div>
                                @if(!empty($existingImages) || !empty($newImages))
                                    <span class="mc-file-count">{{ count($existingImages) + count($newImages) }} ảnh</span>
                                @endif
                            </div>

                            @if(!empty($existingImages))
                                <div class="mb-4">
                                    <div class="fw-semibold mb-2">Ảnh hiện tại</div>
                                    <div class="mc-image-grid">
                                        @foreach($existingImages as $path)
                                            <div class="mc-image-tile">
                                                <img src="{{ asset('storage/' . $path) }}" alt="Ảnh hiện tại">
                                                <button type="button" class="mc-image-remove"
                                                    wire:click="removeExistingImage('{{ $path }}')">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mc-upload-box">
                                <label class="form-label fw-semibold mb-2">{{ $isEditing ? 'Thêm ảnh mới' : 'Tải ảnh lên' }}</label>
                                <input type="file" class="form-control @error('newImages.*') is-invalid @enderror"
                                    wire:model="newImages" multiple accept="image/*">
                                <div class="form-text">Cho phép nhiều ảnh, tối đa 10MB mỗi ảnh.</div>
                                @error('newImages.*')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(!empty($newImages))
                                <div class="mt-4">
                                    <div class="fw-semibold mb-2">Ảnh mới sẽ tải lên</div>
                                    <div class="mc-image-grid">
                                        @foreach($newImages as $i => $img)
                                            <div class="mc-image-tile">
                                                <img src="{{ $img->temporaryUrl() }}" alt="Ảnh xem trước">
                                                <button type="button" class="mc-image-remove"
                                                    wire:click="removeNewImage({{ $i }})">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div wire:loading wire:target="newImages" class="text-muted small mt-3">
                                <span class="spinner-border spinner-border-sm me-1"></span> Đang tải ảnh...
                            </div>
                        </div>
                    </div>

                    <div class="mc-modal-footer modal-footer border-0 px-4 px-lg-5 pb-4 pb-lg-5 pt-0 d-flex justify-content-end flex-wrap gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary"
                            wire:loading.attr="disabled" wire:target="save,newImages">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            Lưu nháp
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($reviewRecord)
        <div wire:ignore.self class="modal fade" id="reviewModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg overflow-hidden">
                    <div class="mc-review-header modal-header border-0 p-4 p-lg-5">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">
                                <i class="bi bi-check2-circle me-2"></i>Duyệt bài content
                            </h5>
                            <p class="mc-review-copy mb-0">Kiểm tra caption và media trước khi phê duyệt hoặc phản hồi.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4 p-lg-5">
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="mc-modal-panel mb-4">
                                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                                        <div>
                                            <div class="text-muted small mb-1">Tiêu đề</div>
                                            <div class="fw-bold fs-4">{{ $reviewRecord->title }}</div>
                                        </div>
                                        <span class="mc-state-badge mc-state-badge-warning">Chờ duyệt</span>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <span class="mc-meta-chip"><i class="bi bi-person me-1"></i>{{ $reviewRecord->user->name }}</span>
                                        <span class="mc-meta-chip"><i class="bi bi-calendar3 me-1"></i>{{ $reviewRecord->scheduled_at?->format('d/m/Y') ?? 'Chưa chốt lịch' }}</span>
                                    </div>
                                </div>

                                <div class="mc-modal-panel mb-4">
                                    <div class="fw-semibold text-muted small mb-2">Nội dung caption</div>
                                    <div class="mc-review-caption">{{ $reviewRecord->content }}</div>
                                </div>

                                @if(!empty($reviewRecord->images))
                                    <div class="mc-modal-panel">
                                        <div class="fw-semibold text-muted small mb-2">Hình ảnh ({{ count($reviewRecord->images) }})</div>
                                        <div class="mc-gallery-grid">
                                            @foreach($reviewRecord->images as $path)
                                                <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $path) }}" alt="Hình ảnh bài viết">
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="col-lg-5">
                                <div class="mc-side-panel h-100">
                                    <div class="fw-semibold mb-3">Quyết định duyệt</div>

                                    <label class="form-label fw-semibold">Ghi chú từ chối</label>
                                    <textarea class="form-control @error('reviewNote') is-invalid @enderror"
                                        wire:model.defer="reviewNote" rows="6"
                                        placeholder="Nhập lý do từ chối nếu cần..."></textarea>
                                    @error('reviewNote')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                    <div class="mc-form-help mt-3">Chỉ cần nhập ghi chú khi từ chối. Ghi rõ điểm cần sửa để đội marketing chỉnh nhanh và gửi lại đúng vòng duyệt.</div>

                                    <div class="mc-checklist border-top mt-4 pt-4 small text-muted d-grid gap-2">
                                        <div><i class="bi bi-check2 me-2"></i>Kiểm tra caption có đúng định hướng nội dung.</div>
                                        <div><i class="bi bi-check2 me-2"></i>Đảm bảo hình ảnh khớp với thông điệp bài viết.</div>
                                        <div><i class="bi bi-check2 me-2"></i>Ưu tiên ghi chú cụ thể nếu cần chỉnh sửa lại.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mc-modal-footer modal-footer border-0 px-4 px-lg-5 pb-4 pb-lg-5 pt-0 d-flex justify-content-between flex-wrap gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-danger" wire:click="reject">
                                <i class="bi bi-x-circle me-1"></i>Từ chối
                            </button>
                            <button type="button" class="btn btn-success" wire:click="approve">
                                <i class="bi bi-check-circle me-1"></i>Duyệt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($detailRecord)
        @php
            $detailColor = $statusColors[$detailRecord->status] ?? 'secondary';
            $detailStatusLabel = $statusLabels[$detailRecord->status] ?? $detailRecord->status;
            $detailImages = $detailRecord->images ?? [];
            $detailScheduledAt = $detailRecord->scheduled_at;
            $detailScheduleValue = $detailScheduledAt?->format('d/m/Y') ?? 'Chưa chốt lịch';
            $detailScheduleHint = 'Chưa có ngày đăng chính thức';

            if ($detailScheduledAt) {
                if ($detailScheduledAt->isToday()) {
                    $detailScheduleHint = 'Dự kiến đăng hôm nay';
                } elseif ($detailScheduledAt->isPast()) {
                    $detailScheduleHint = 'Đã quá lịch đăng';
                } elseif ($detailScheduledAt->diffInDays(now()) <= 7) {
                    $detailScheduleHint = 'Dự kiến đăng trong ' . $detailScheduledAt->diffInDays(now()) . ' ngày';
                } else {
                    $detailScheduleHint = 'Đã lên lịch đăng';
                }
            }
        @endphp

        <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg overflow-hidden">
                    <div class="modal-header mc-modal-header border-0 p-4 p-lg-5">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">{{ $detailRecord->title }}</h5>
                            <p class="mc-modal-copy mb-0">Xem nội dung, lịch đăng, ảnh đính kèm và trạng thái xử lý của bài viết.</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4 p-lg-5">
                        <div class="row g-4">
                            <div class="col-xl-8">
                                <div class="mc-modal-panel mb-4">
                                    <div class="d-flex flex-column flex-lg-row align-items-start justify-content-between gap-4 mb-4">
                                        <div class="flex-grow-1">
                                            <div class="mc-detail-section-label">Bài viết</div>
                                            <h6 class="mc-detail-heading mb-2">{{ $detailRecord->title }}</h6>

                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="mc-status-badge mc-status-badge-{{ $detailColor }}">
                                                    <span class="mc-status-dot bg-{{ $detailColor }}"></span>
                                                    {{ $detailStatusLabel }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mc-detail-datebox">
                                            <div class="mc-detail-datebox-label">Lịch đăng</div>
                                            <div class="mc-detail-datebox-value">{{ $detailScheduleValue }}</div>
                                            <div class="mc-detail-datebox-note">{{ $detailScheduleHint }}</div>
                                        </div>
                                    </div>

                                    <div class="mc-detail-section-label">Caption</div>
                                    <div class="mc-detail-caption">{{ $detailRecord->content }}</div>
                                </div>

                                <div class="mc-modal-panel">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                        <div>
                                            <div class="mc-detail-section-label">Media</div>
                                            <h6 class="mb-0 fw-semibold">Thư viện ảnh</h6>
                                        </div>
                                        <span class="mc-file-count">{{ count($detailImages) }} ảnh</span>
                                    </div>

                                    @if(!empty($detailImages))
                                        <div id="detailCarousel-{{ $detailRecord->id }}" class="carousel slide mc-detail-carousel" data-bs-interval="false">
                                            <div class="carousel-inner">
                                                @foreach($detailImages as $path)
                                                    <div class="carousel-item @if($loop->first) active @endif">
                                                        <div class="mc-detail-slide">
                                                            <img src="{{ asset('storage/' . $path) }}" alt="Hình ảnh bài viết {{ $loop->iteration }}">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if(count($detailImages) > 1)
                                                <button class="carousel-control-prev mc-detail-carousel-control" type="button" data-bs-target="#detailCarousel-{{ $detailRecord->id }}" data-bs-slide="prev">
                                                    <span class="mc-detail-carousel-arrow" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                                                    <span class="visually-hidden">Ảnh trước</span>
                                                </button>
                                                <button class="carousel-control-next mc-detail-carousel-control" type="button" data-bs-target="#detailCarousel-{{ $detailRecord->id }}" data-bs-slide="next">
                                                    <span class="mc-detail-carousel-arrow" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                                                    <span class="visually-hidden">Ảnh sau</span>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mc-detail-empty">Bài viết này chưa có hình ảnh đính kèm.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="mc-detail-aside">
                                    <div class="mc-side-panel">
                                        <div class="mc-detail-section-label">Tóm tắt</div>
                                        <h6 class="mb-3 fw-semibold">Thông tin bài viết</h6>

                                        <div class="mc-detail-facts">
                                            <div class="mc-detail-fact">
                                                <div class="mc-detail-fact-label">Người phụ trách</div>
                                                <div class="mc-detail-fact-value">{{ $detailRecord->user->name }}</div>
                                                <div class="mc-detail-fact-note">Tạo lúc {{ $detailRecord->created_at?->format('d/m/Y H:i') }}</div>
                                            </div>
                                               <div class="mc-detail-fact">
                                                   <div class="mc-detail-fact-label">Trạng thái</div>
                                                   <div class="mc-detail-fact-value">
                                                       <span class="mc-status-badge mc-status-badge-{{ $detailColor }}">
                                                           <span class="mc-status-dot bg-{{ $detailColor }}"></span>
                                                           {{ $detailStatusLabel }}
                                                       </span>
                                                   </div>
                                                   @if($detailRecord->reviewed_at)
                                                       <div class="mc-detail-fact-note">Duyệt bởi {{ $detailRecord->reviewer?->name ?? 'Hệ thống' }} lúc {{ $detailRecord->reviewed_at->format('d/m/Y H:i') }}</div>
                                                   @endif
                                               </div>

                                            <div class="mc-detail-fact">
                                                <div class="mc-detail-fact-label">Lịch đăng</div>
                                                <div class="mc-detail-fact-value">{{ $detailScheduleValue }}</div>
                                                <div class="mc-detail-fact-note">{{ $detailScheduleHint }}</div>
                                            </div>

                                            <div class="mc-detail-fact">
                                                <div class="mc-detail-fact-label">Cập nhật gần nhất</div>
                                                <div class="mc-detail-fact-value">{{ $detailRecord->updated_at?->format('d/m/Y H:i') }}</div>
                                                <div class="mc-detail-fact-note">{{ $detailRecord->updated_at?->diffForHumans() }}</div>
                                            </div>
                                        </div>

                                        @if($detailRecord->status === 'rejected' && $detailRecord->reviewer_note)
                                            <div class="mc-note-alert mt-4 p-3">
                                                <div class="fw-semibold mb-1"><i class="bi bi-x-circle me-1"></i>Lý do từ chối</div>
                                                <div class="small">{{ $detailRecord->reviewer_note }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mc-modal-footer modal-footer border-0 px-4 px-lg-5 pb-4 pb-lg-5 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    if (!window._marketingContentModalListenersRegistered) {
        window._marketingContentModalListenersRegistered = true;

        const openModal = (id) => {
            const el = document.getElementById(id);
            if (!el) return;

            bootstrap.Modal.getOrCreateInstance(el).show();
        };

        const closeModal = (id) => {
            const el = document.getElementById(id);
            if (!el) return;

            bootstrap.Modal.getInstance(el)?.hide();
        };

        window.addEventListener('openContentFormModal', () => openModal('contentFormModal'));
        window.addEventListener('closeContentFormModal', () => closeModal('contentFormModal'));
        window.addEventListener('openReviewModal', () => openModal('reviewModal'));
        window.addEventListener('closeReviewModal', () => closeModal('reviewModal'));
        window.addEventListener('openDetailModal', () => openModal('detailModal'));
    }
</script>
@endpush
