@once
    @push('styles')
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/marketing-content-manager.css') }}?v={{ filemtime(public_path('assets/css/marketing-content-manager.css')) }}">
    @endpush
@endonce

<div class="marketing-content-page pb-4">
    <div class="page-header d-flex align-items-start align-items-lg-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-1">Kế hoạch content Marketing</h4>
            <p class="text-muted mb-0">Lịch nội dung theo tháng, gom bài viết theo ngày đăng và trạng thái duyệt.</p>
        </div>
        <div class="d-flex gap-2 ms-auto flex-wrap justify-content-end align-items-center">
            @if($isMarketing)
                <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1" wire:click="openCreate">
                    <i class="fa-solid fa-plus-lg"></i> Tạo bài mới
                </button>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mc-marketing-panel mc-calendar-card" wire:loading.class="opacity-75" wire:target="calendarMonth,previousCalendarMonth,nextCalendarMonth,goToCurrentCalendarMonth">
        <div class="card-header bg-body d-flex align-items-center justify-content-between flex-wrap gap-3 p-3">
            <div>
                <div class="text-muted small fw-bold text-uppercase">Lịch đăng</div>
                <h5 class="mb-1 fw-bold">{{ $calendarMonthLabel }}</h5>
                <div class="text-muted small">{{ $monthContentsCount }} bài trong tháng đang chọn</div>
            </div>

            <div class="d-flex align-items-center justify-content-end flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm mc-icon-btn" wire:click="previousCalendarMonth" title="Tháng trước">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <label class="visually-hidden" for="marketing-content-month">Chọn tháng</label>
                <input id="marketing-content-month" type="month" class="form-control form-control-sm w-auto" wire:model.live="calendarMonth">
                <button type="button" class="btn btn-outline-secondary btn-sm mc-icon-btn" wire:click="nextCalendarMonth" title="Tháng sau">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="goToCurrentCalendarMonth">
                    Hôm nay
                </button>
            </div>
        </div>

        <div class="card-body border-bottom bg-body-tertiary p-3">
            <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="badge rounded-pill text-bg-primary">
                <span>Tổng</span>
                <strong class="ms-1">{{ $monthContentsCount }}</strong>
            </span>
            @foreach($statusLabels as $status => $label)
                <span class="badge rounded-pill text-bg-{{ $statusColors[$status] ?? 'secondary' }}">
                    <span>{{ $label }}</span>
                    <strong class="ms-1">{{ $calendarStatusCounts->get($status, 0) }}</strong>
                </span>
            @endforeach
            </div>
        </div>

        <div class="overflow-auto">
            <div class="mc-calendar-weekdays bg-body-tertiary text-muted small fw-bold text-center text-uppercase border-bottom" aria-hidden="true">
                @foreach(['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'] as $weekday)
                    <div class="p-2">{{ $weekday }}</div>
                @endforeach
            </div>

            <div class="mc-calendar-grid">
                @foreach($calendarDays as $day)
                    @php
                        $dayItems = $day['items'];
                    @endphp
                    <section class="mc-calendar-day p-2 border-end border-bottom {{ $day['isCurrentMonth'] ? 'bg-body' : 'bg-body-tertiary text-secondary' }} {{ $day['isToday'] ? 'border-primary' : '' }} @if($isMarketing) mc-calendar-day-action @endif"
                        wire:key="marketing-calendar-day-{{ $day['key'] }}"
                        @if($isMarketing)
                            role="button"
                            tabindex="0"
                            title="Tạo bài cho ngày {{ $day['date']->format('d/m/Y') }}"
                            wire:click="openCreateForDate('{{ $day['key'] }}')"
                            wire:keydown.enter="openCreateForDate('{{ $day['key'] }}')"
                        @endif>
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <span class="fw-bold">{{ $day['date']->format('d') }}</span>
                            @if($day['isToday'])
                                <span class="badge rounded-pill text-bg-primary">Hôm nay</span>
                            @elseif($dayItems->isNotEmpty())
                                <span class="badge rounded-pill text-bg-secondary">{{ $dayItems->count() }}</span>
                            @endif
                        </div>

                        <div class="d-grid gap-2">
                            @forelse($dayItems as $item)
                                @php
                                    $statusColor = $statusColors[$item->status] ?? 'secondary';
                                @endphp
                                <article class="mc-calendar-event border rounded-2 bg-body p-2 shadow-sm"
                                    wire:key="marketing-calendar-event-{{ $item->id }}"
                                    role="button"
                                    tabindex="0"
                                    wire:click.stop="openCalendarContent({{ $item->id }})"
                                    wire:keydown.enter.stop="openCalendarContent({{ $item->id }})">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="min-w-0">
                                            <div class="fw-semibold small text-truncate">{{ $item->title }}</div>
                                            <div class="text-muted small text-truncate">{{ \Illuminate\Support\Str::limit($item->content, 48) }}</div>
                                        </div>
                                        <span class="badge rounded-pill text-bg-{{ $statusColor }} flex-shrink-0">
                                            {{ $statusLabels[$item->status] ?? $item->status }}
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 text-muted small">
                                        <span class="d-inline-flex align-items-center gap-1"><i class="{{ $this->listScheduleIcon($item->scheduled_at) }}"></i>{{ $this->listScheduleText($item->scheduled_at) }}</span>
                                        @if(!$isMarketing)
                                            <span class="d-inline-flex align-items-center gap-1"><i class="fa-solid fa-user"></i>{{ $item->user?->name ?? 'Chưa rõ' }}</span>
                                        @endif
                                        @if(count($item->images ?? []) > 0)
                                            <span class="d-inline-flex align-items-center gap-1"><i class="fa-solid fa-image"></i>{{ count($item->images ?? []) }}</span>
                                        @endif
                                    </div>

                                    @if(($isMarketing && ($item->isEditable() || $item->isDraft())) || ($isReviewer && $item->isPending()))
                                        <div class="d-flex align-items-center justify-content-end gap-1 mt-2">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            @if($isMarketing && $item->isEditable())
                                                <button type="button" class="btn btn-sm btn-outline-secondary mc-icon-btn" wire:click.stop="openEdit({{ $item->id }})" title="Sửa">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                            @endif

                                            @if($isMarketing && $item->isDraft())
                                                <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold d-inline-flex align-items-center gap-1" wire:click.stop="submitForReview({{ $item->id }})" wire:confirm="Gửi bài này để duyệt?" title="Gửi duyệt">
                                                    <i class="fa-solid fa-paper-plane"></i><span>Gửi duyệt</span>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger mc-icon-btn" wire:click.stop="deleteContent({{ $item->id }})" wire:confirm="Xóa bài content này?" title="Xóa">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            @endif

                                            @if($isReviewer && $item->isPending())
                                                <button type="button" class="btn btn-sm btn-success mc-icon-btn" wire:click.stop="openReview({{ $item->id }})" title="Duyệt">
                                                    <i class="fa-solid fa-check-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                        </div>
                                    @endif
                                </article>
                            @empty
                                @if($isMarketing)
                                    <span class="mc-calendar-add-hint d-inline-flex align-items-center justify-content-center rounded-2 border text-secondary small py-2">
                                        <i class="fa-solid fa-plus-lg"></i>
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>

    @if($unscheduledContents->isNotEmpty())
        <div class="card border-0 shadow-sm mt-3 mc-marketing-panel mc-unscheduled-card">
            <div class="card-header bg-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Chưa chốt lịch</div>
                    <h6 class="mb-0 fw-semibold">Bài content chưa có ngày đăng</h6>
                </div>
                <span class="badge rounded-pill text-bg-secondary">{{ $unscheduledContents->count() }} bài</span>
            </div>

            <div class="list-group list-group-flush">
                @foreach($unscheduledContents as $item)
                    @php
                        $statusColor = $statusColors[$item->status] ?? 'secondary';
                    @endphp
                    <div class="list-group-item d-flex align-items-start justify-content-between flex-wrap gap-3" wire:key="marketing-unscheduled-{{ $item->id }}">
                        <button type="button" class="mc-unscheduled-main min-w-0 text-start flex-grow-1" wire:click="openDetail({{ $item->id }})">
                            <strong class="d-block text-truncate">{{ $item->title }}</strong>
                            <small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($item->content, 90) }}</small>
                        </button>

                        <div class="d-flex align-items-center justify-content-end flex-wrap gap-2">
                            <span class="badge rounded-pill text-bg-{{ $statusColor }} flex-shrink-0">
                                {{ $statusLabels[$item->status] ?? $item->status }}
                            </span>

                            @if($isMarketing && $item->isEditable())
                                <button type="button" class="btn btn-sm btn-outline-secondary mc-icon-btn" wire:click.stop="openEdit({{ $item->id }})" title="Sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            @endif

                            @if($isMarketing && $item->isDraft())
                                <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold d-inline-flex align-items-center gap-1" wire:click.stop="submitForReview({{ $item->id }})" wire:confirm="Gửi bài này để duyệt?" title="Gửi duyệt">
                                    <i class="fa-solid fa-paper-plane"></i><span>Gửi duyệt</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger mc-icon-btn" wire:click.stop="deleteContent({{ $item->id }})" wire:confirm="Xóa bài content này?" title="Xóa">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            @endif

                            @if($isReviewer && $item->isPending())
                                <button type="button" class="btn btn-sm btn-success mc-icon-btn" wire:click.stop="openReview({{ $item->id }})" title="Duyệt">
                                    <i class="fa-solid fa-check-circle"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div wire:ignore.self class="modal fade" id="contentFormModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow mc-content-form-modal">
                <div class="modal-header bg-body align-items-start border-bottom">
                    <div class="d-flex align-items-start gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary-subtle text-secondary flex-shrink-0 mc-form-hero-icon">
                            <i class="fa-solid fa-bullhorn"></i>
                        </span>
                        <div>
                            <h5 class="modal-title fw-bold mb-1">{{ $isEditing ? 'Chỉnh sửa bài content' : 'Tạo bài content mới' }}</h5>
                            <p class="mb-0 small text-muted">Soạn caption, chốt lịch đăng và kiểm tra bản xem nhanh trước khi lưu nháp.</p>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <span class="badge rounded-pill text-bg-light border text-secondary">Nháp</span>
                                <span class="badge rounded-pill text-bg-light border text-secondary">Lịch content</span>
                                <span class="badge rounded-pill text-bg-light border text-secondary">Media ready</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form wire:submit.prevent="save" class="d-flex flex-column flex-grow-1 overflow-hidden mc-modal-form">
                    <div class="modal-body p-4 overflow-auto mc-modal-scroll">
                        @php
                            $mediaPreviewCount = count($existingImages) + count($newImages);
                        @endphp

                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="card border shadow-sm mb-4">
                                    <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase">Nội dung chính</div>
                                            <h6 class="mb-0 fw-semibold">Caption và lịch đăng</h6>
                                        </div>
                                        <span class="badge rounded-pill text-bg-secondary">Bước 1</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-lg-8">
                                                <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-lg @error('formTitle') is-invalid @enderror"
                                                    wire:model.live.debounce.400ms="formTitle" placeholder="Ví dụ: Checklist môi trường tháng này">
                                                @error('formTitle')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-4">
                                                <label class="form-label fw-semibold">Ngày đăng <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control form-control-lg @error('formScheduledAt') is-invalid @enderror"
                                                    wire:model.live="formScheduledAt">
                                                @error('formScheduledAt')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Nội dung caption <span class="text-danger">*</span></label>
                                                <textarea class="form-control @error('formContent') is-invalid @enderror"
                                                    wire:model.live.debounce.600ms="formContent" rows="10"
                                                    placeholder="Viết caption hoàn chỉnh, CTA, hashtag hoặc ghi chú triển khai..."></textarea>
                                                @error('formContent')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border shadow-sm">
                                    <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase">Media</div>
                                            <h6 class="mb-0 fw-semibold">Ảnh minh họa</h6>
                                        </div>
                                        <span class="badge rounded-pill text-bg-secondary">{{ $mediaPreviewCount }} ảnh</span>
                                    </div>
                                    <div class="card-body">
                                        @if(!empty($existingImages))
                                            <div class="mb-4">
                                                <div class="fw-semibold mb-2">Ảnh hiện tại</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($existingImages as $path)
                                                        <div class="mc-image-tile">
                                                            <img src="{{ asset('storage/' . $path) }}" alt="Ảnh hiện tại">
                                                            <button type="button" class="mc-image-remove"
                                                                wire:click="removeExistingImage('{{ $path }}')">
                                                                <i class="fa-solid fa-xmark"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div class="border border-2 rounded-3 bg-body-tertiary p-4 text-center mc-upload-zone">
                                            <i class="fa-solid fa-cloud-arrow-up fs-1 text-secondary"></i>
                                            <label class="form-label fw-semibold d-block mt-2 mb-2">{{ $isEditing ? 'Thêm ảnh mới' : 'Tải ảnh lên' }}</label>
                                            <input type="file" class="form-control @error('newImages.*') is-invalid @enderror"
                                                wire:model="newImages" multiple accept="image/*">
                                            <div class="form-text">Cho phép nhiều ảnh, tối đa 50MB mỗi ảnh (tự động chuyển sang định dạng WebP).</div>
                                            @error('newImages.*')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        @if(!empty($newImages))
                                            <div class="mt-4">
                                                <div class="fw-semibold mb-2">Ảnh mới sẽ tải lên</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($newImages as $i => $img)
                                                        <div class="mc-image-tile">
                                                            <img src="{{ $img->temporaryUrl() }}" alt="Ảnh xem trước">
                                                            <button type="button" class="mc-image-remove"
                                                                wire:click="removeNewImage({{ $i }})">
                                                                <i class="fa-solid fa-xmark"></i>
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
                            </div>

                            <div class="col-lg-5">
                                <div class="position-sticky top-0">
                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-body d-flex align-items-center justify-content-between gap-2">
                                            <div>
                                                <div class="text-muted small fw-bold text-uppercase">Bản xem nhanh</div>
                                                <h6 class="mb-0 fw-semibold">Lên lịch đăng</h6>
                                            </div>
                                            <span class="badge rounded-pill text-bg-secondary">Nháp</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-body-tertiary border mb-3">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success mc-form-hero-icon">
                                                    <i class="fa-solid fa-calendar-day"></i>
                                                </span>
                                                <div>
                                                    <div class="fw-bold">{{ $this->formSchedulePreviewValue() }}</div>
                                                    <div class="text-muted small">{{ $this->formSchedulePreviewHint() }}</div>
                                                </div>
                                            </div>

                                            <h5 class="fw-bold mb-2 text-break">{{ filled($formTitle) ? $formTitle : 'Tiêu đề bài viết sẽ hiển thị ở đây' }}</h5>
                                            <div class="text-muted small mc-prewrap text-break">{{ filled($formContent) ? \Illuminate\Support\Str::limit($formContent, 420) : 'Caption, thông điệp chính và CTA sẽ xuất hiện tại đây trong lúc soạn.' }}</div>

                                            <div class="border-top pt-3 mt-3">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="fw-semibold small">Media đính kèm</span>
                                                    <span class="badge rounded-pill text-bg-secondary">{{ $mediaPreviewCount }}</span>
                                                </div>

                                                @if($mediaPreviewCount > 0)
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($existingImages as $path)
                                                            <img class="mc-preview-thumb rounded-2 border" src="{{ asset('storage/' . $path) }}" alt="Ảnh hiện tại">
                                                        @endforeach
                                                        @foreach($newImages as $img)
                                                            <img class="mc-preview-thumb rounded-2 border" src="{{ $img->temporaryUrl() }}" alt="Ảnh mới">
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="border rounded-3 bg-body-tertiary p-4 text-center text-muted small">
                                                        <i class="fa-solid fa-image d-block fs-2 mb-2"></i>
                                                        Chưa có ảnh đính kèm
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border rounded-3 bg-body-tertiary mt-3 mb-0 p-3 text-body-secondary">
                                        <div class="fw-semibold text-body mb-1"><i class="fa-solid fa-lightbulb text-warning me-1"></i>Gợi ý</div>
                                        <div class="small">Tiêu đề ngắn, caption rõ CTA và ảnh khớp thông điệp sẽ giúp vòng duyệt nhanh hơn.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end flex-wrap gap-2 bg-body flex-shrink-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        @if($isEditing && $editingRecord?->isDraft())
                            <button type="button" class="btn btn-warning text-dark fw-semibold"
                                wire:click="saveAndSubmitForReview"
                                wire:confirm="Lưu nội dung hiện tại và gửi bài này để duyệt?"
                                wire:loading.attr="disabled"
                                wire:target="saveAndSubmitForReview,newImages">
                                <span wire:loading wire:target="saveAndSubmitForReview" class="spinner-border spinner-border-sm me-1"></span>
                                <i class="fa-solid fa-paper-plane me-1"></i>Gửi duyệt
                            </button>
                        @endif
                        <button type="submit" class="btn btn-success"
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
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-warning-subtle text-warning-emphasis">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">
                                <i class="fa-solid fa-check-circle me-2"></i>Duyệt bài content
                            </h5>
                            <p class="mb-0 small">Kiểm tra caption và media trước khi phê duyệt hoặc phản hồi.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="border rounded-2 p-3 mb-4">
                                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                                        <div>
                                            <div class="text-muted small mb-1">Tiêu đề</div>
                                            <div class="fw-bold fs-4">{{ $reviewRecord->title }}</div>
                                        </div>
                                        <span class="badge rounded-pill text-bg-warning">Chờ duyệt</span>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <span class="badge rounded-pill text-bg-light border text-body"><i class="fa-solid fa-user me-1"></i>{{ $reviewRecord->user?->name ?? 'Chưa rõ phụ trách' }}</span>
                                        <span class="badge rounded-pill text-bg-light border text-body"><i class="fa-solid fa-calendar-days me-1"></i>{{ $reviewRecord->scheduled_at?->format('d/m/Y') ?? 'Chưa chốt lịch' }}</span>
                                    </div>
                                </div>

                                <div class="border rounded-2 p-3 mb-4">
                                    <div class="fw-semibold text-muted small mb-2">Nội dung caption</div>
                                    <div class="bg-body-tertiary border rounded-2 p-3 text-break mc-prewrap">{{ $reviewRecord->content }}</div>
                                </div>

                                @if(!empty($reviewRecord->images))
                                    <div class="border rounded-2 p-3">
                                        <div class="fw-semibold text-muted small mb-2">Hình ảnh ({{ count($reviewRecord->images) }})</div>
                                        <div class="row g-2">
                                            @foreach($reviewRecord->images as $path)
                                                <div class="col-6 col-md-4">
                                                    <a href="{{ asset('storage/' . $path) }}" target="_blank" class="d-block">
                                                        <img class="mc-gallery-thumb rounded-2 border" src="{{ asset('storage/' . $path) }}" alt="Hình ảnh bài viết">
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="col-lg-5">
                                <div class="border rounded-2 bg-body-tertiary p-3 h-100">
                                    <div class="fw-semibold mb-3">Quyết định duyệt</div>

                                    <label class="form-label fw-semibold">Ghi chú từ chối</label>
                                    <textarea class="form-control @error('reviewNote') is-invalid @enderror"
                                        wire:model.defer="reviewNote" rows="6"
                                        placeholder="Nhập lý do từ chối nếu cần..."></textarea>
                                    @error('reviewNote')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                    <div class="form-text mt-3">Chỉ cần nhập ghi chú khi từ chối. Ghi rõ điểm cần sửa để đội marketing chỉnh nhanh và gửi lại đúng vòng duyệt.</div>

                                    <div class="border-top mt-4 pt-4 small text-muted d-grid gap-2">
                                        <div><i class="fa-solid fa-check me-2"></i>Kiểm tra caption có đúng định hướng nội dung.</div>
                                        <div><i class="fa-solid fa-check me-2"></i>Đảm bảo hình ảnh khớp với thông điệp bài viết.</div>
                                        <div><i class="fa-solid fa-check me-2"></i>Ưu tiên ghi chú cụ thể nếu cần chỉnh sửa lại.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between flex-wrap gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-danger" wire:click="reject">
                                <i class="fa-solid fa-xmark-circle me-1"></i>Từ chối
                            </button>
                            <button type="button" class="btn btn-success" wire:click="approve">
                                <i class="fa-solid fa-circle-check me-1"></i>Duyệt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($detailRecord)
        @php
            $detailImageUrls = collect($detailRecord->images ?? [])
                ->map(fn ($path) => asset('storage/' . $path))
                ->values()
                ->all();
            $detailDownloadName = \Illuminate\Support\Str::slug($detailRecord->title ?: 'marketing-content');
        @endphp

        <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content border-0 shadow mc-detail-modal">
                    <div class="modal-header mc-detail-modal-header">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">{{ $detailRecord->title }}</h5>
                            <p class="mb-0 small opacity-75">Xem nội dung, lịch đăng, ảnh đính kèm và trạng thái xử lý của bài viết.</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-xl-8">
                                <div class="border rounded-2 p-3 mb-4">
                                    <div class="d-flex flex-column flex-lg-row align-items-start justify-content-between gap-4 mb-4">
                                        <div class="flex-grow-1">
                                            <div class="text-muted small fw-bold text-uppercase mb-2">Bài viết</div>
                                            <h6 class="fw-bold fs-5 mb-2">{{ $detailRecord->title }}</h6>

                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="badge rounded-pill text-bg-{{ $statusColors[$detailRecord->status] ?? 'secondary' }}">
                                                    {{ $statusLabels[$detailRecord->status] ?? $detailRecord->status }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="border rounded-2 bg-body-tertiary p-3 min-w-220">
                                            <div class="text-muted small fw-bold text-uppercase">Lịch đăng</div>
                                            <div class="fw-bold mt-1">{{ $this->detailScheduleValue($detailRecord->scheduled_at) }}</div>
                                            <div class="text-muted small mt-1">{{ $this->detailScheduleHint($detailRecord->scheduled_at) }}</div>
                                        </div>
                                    </div>

                                    <div class="text-muted small fw-bold text-uppercase mb-2">Caption</div>
                                    <div class="bg-body-tertiary border rounded-2 p-3 text-break mc-prewrap">{{ $detailRecord->content }}</div>
                                </div>

                                <div class="border rounded-2 p-3">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Media</div>
                                            <h6 class="mb-0 fw-semibold">Thư viện ảnh</h6>
                                        </div>
                                        <span class="badge rounded-pill text-bg-secondary">{{ count($detailRecord->images ?? []) }} ảnh</span>
                                    </div>

                                    @if(!empty($detailRecord->images))
                                        <div id="detailCarousel-{{ $detailRecord->id }}" class="carousel slide border rounded-2 overflow-hidden bg-body-tertiary" data-bs-interval="false">
                                            <div class="carousel-inner">
                                                @foreach($detailRecord->images as $path)
                                                    <div class="carousel-item @if($loop->first) active @endif">
                                                        <div class="mc-detail-slide">
                                                            <img src="{{ asset('storage/' . $path) }}" alt="Hình ảnh bài viết {{ $loop->iteration }}">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if(count($detailRecord->images) > 1)
                                                <button class="carousel-control-prev" type="button" data-bs-target="#detailCarousel-{{ $detailRecord->id }}" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Ảnh trước</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#detailCarousel-{{ $detailRecord->id }}" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Ảnh sau</span>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <div class="alert alert-secondary mb-0">Bài viết này chưa có hình ảnh đính kèm.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="border rounded-2 bg-body-tertiary p-3">
                                    <div class="text-muted small fw-bold text-uppercase mb-1">Tóm tắt</div>
                                    <h6 class="mb-3 fw-semibold">Thông tin bài viết</h6>

                                    <div class="list-group list-group-flush rounded-2">
                                        <div class="list-group-item">
                                            <div class="text-muted small fw-bold text-uppercase">Người phụ trách</div>
                                            <div class="fw-semibold">{{ $detailRecord->user?->name ?? 'Chưa rõ phụ trách' }}</div>
                                            <div class="text-muted small">Tạo lúc {{ $detailRecord->created_at?->format('d/m/Y H:i') }}</div>
                                        </div>

                                        <div class="list-group-item">
                                            <div class="text-muted small fw-bold text-uppercase">Trạng thái</div>
                                            <div class="mt-1">
                                                <span class="badge rounded-pill text-bg-{{ $statusColors[$detailRecord->status] ?? 'secondary' }}">
                                                    {{ $statusLabels[$detailRecord->status] ?? $detailRecord->status }}
                                                </span>
                                            </div>
                                            @if($detailRecord->reviewed_at)
                                                <div class="text-muted small mt-1">Duyệt bởi {{ $detailRecord->reviewer?->name ?? 'Hệ thống' }} lúc {{ $detailRecord->reviewed_at->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </div>

                                        <div class="list-group-item">
                                            <div class="text-muted small fw-bold text-uppercase">Lịch đăng</div>
                                            <div class="fw-semibold">{{ $this->detailScheduleValue($detailRecord->scheduled_at) }}</div>
                                            <div class="text-muted small">{{ $this->detailScheduleHint($detailRecord->scheduled_at) }}</div>
                                        </div>

                                        <div class="list-group-item">
                                            <div class="text-muted small fw-bold text-uppercase">Cập nhật gần nhất</div>
                                            <div class="fw-semibold">{{ $detailRecord->updated_at?->format('d/m/Y H:i') }}</div>
                                            <div class="text-muted small">{{ $detailRecord->updated_at?->diffForHumans() }}</div>
                                        </div>
                                    </div>

                                    @if($detailRecord->status === 'rejected' && $detailRecord->reviewer_note)
                                        <div class="alert alert-danger mt-4 mb-0">
                                            <div class="fw-semibold mb-1"><i class="fa-solid fa-xmark-circle me-1"></i>Lý do từ chối</div>
                                            <div class="small">{{ $detailRecord->reviewer_note }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            @if($isMarketing && $detailRecord->isDraft())
                                <button type="button"
                                    class="btn btn-warning text-dark fw-semibold"
                                    wire:click="submitForReview({{ $detailRecord->id }})"
                                    wire:confirm="Gửi bài này để duyệt?">
                                    <i class="fa-solid fa-paper-plane me-1"></i>Gửi duyệt
                                </button>
                            @endif
                            @if($isReviewer && $detailRecord->isPending())
                                <button type="button"
                                    class="btn btn-success fw-semibold"
                                    wire:click="openReview({{ $detailRecord->id }})">
                                    <i class="fa-solid fa-check-circle me-1"></i>Duyệt bài
                                </button>
                            @endif
                            <button type="button"
                                class="btn btn-outline-primary"
                                onclick="window.marketingContentCopy(this, @js($detailRecord->content), @js($detailImageUrls))">
                                <i class="fa-solid fa-clipboard-check me-1"></i>Copy đăng bài
                            </button>
                            <button type="button"
                                class="btn btn-outline-secondary"
                                onclick="window.marketingContentDownload(@js($detailRecord->content), @js($detailImageUrls), @js($detailDownloadName))">
                                <i class="fa-solid fa-download me-1"></i>Tải xuống
                            </button>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    if (!window._marketingContentModalListenersRegistered) {
        window._marketingContentModalListenersRegistered = true;

        const openModal = (id, attempt = 0) => {
            const el = document.getElementById(id);
            if (!el) {
                if (attempt < 6) {
                    window.setTimeout(() => openModal(id, attempt + 1), 25);
                }
                return;
            }

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
        window.addEventListener('closeDetailModal', () => closeModal('detailModal'));
    }

    if (!window._marketingContentShareHelpersRegistered) {
        window._marketingContentShareHelpersRegistered = true;

        const toast = (type, message) => {
            window.dispatchEvent(new CustomEvent('swal:toast', {
                detail: [{ type, message }],
            }));
        };

        const safeFileName = (value, fallback = 'marketing-content') => {
            return String(value || fallback)
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-zA-Z0-9-_]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .toLowerCase() || fallback;
        };

        const downloadUrl = (url, name) => {
            const link = document.createElement('a');
            link.href = url;
            link.download = name;
            link.rel = 'noopener';
            document.body.appendChild(link);
            link.click();
            link.remove();
        };

        const downloadText = (text, name) => {
            const blob = new Blob([text || ''], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            downloadUrl(url, name);
            window.setTimeout(() => URL.revokeObjectURL(url), 1000);
        };

        const imageExtension = (url) => {
            try {
                const pathname = new URL(url, window.location.href).pathname;
                const match = pathname.match(/\.([a-z0-9]{2,5})$/i);
                return match ? match[1].toLowerCase() : 'jpg';
            } catch (error) {
                return 'jpg';
            }
        };

        const imageToPngBlob = async (url) => {
            const response = await fetch(url, { cache: 'no-store' });
            if (!response.ok) {
                throw new Error('Không tải được ảnh.');
            }

            const sourceBlob = await response.blob();
            if (!sourceBlob.type.startsWith('image/')) {
                throw new Error('File không phải ảnh.');
            }

            if (sourceBlob.type === 'image/png') {
                return sourceBlob;
            }

            if (!window.createImageBitmap) {
                throw new Error('Trình duyệt không hỗ trợ chuyển ảnh sang clipboard.');
            }

            const bitmap = await createImageBitmap(sourceBlob);
            const canvas = document.createElement('canvas');
            canvas.width = bitmap.width;
            canvas.height = bitmap.height;
            canvas.getContext('2d').drawImage(bitmap, 0, 0);
            bitmap.close?.();

            return await new Promise((resolve, reject) => {
                canvas.toBlob((blob) => {
                    blob ? resolve(blob) : reject(new Error('Không chuyển được ảnh.'));
                }, 'image/png');
            });
        };

        const fallbackCopyText = (text) => {
            const textarea = document.createElement('textarea');
            textarea.value = text || '';
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            const copied = document.execCommand('copy');
            textarea.remove();
            return copied;
        };

        window.marketingContentCopy = async (button, text, imageUrls = []) => {
            const originalHtml = button?.innerHTML;

            if (button) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang copy';
            }

            try {
                const caption = text || '';
                const images = Array.isArray(imageUrls) ? imageUrls : [];
                const textBlob = new Blob([caption], { type: 'text/plain' });
                const imageBlobs = [];

                if (images.length > 0 && window.ClipboardItem && navigator.clipboard?.write) {
                    for (const imageUrl of images) {
                        try {
                            imageBlobs.push(await imageToPngBlob(imageUrl));
                        } catch (error) {
                            break;
                        }
                    }

                    if (imageBlobs.length > 0) {
                        try {
                            const items = [
                                new ClipboardItem({ 'text/plain': textBlob }),
                                ...imageBlobs.map((blob) => new ClipboardItem({ 'image/png': blob })),
                            ];
                            await navigator.clipboard.write(items);
                            toast('success', `Đã copy caption và ${imageBlobs.length} ảnh.`);
                            return;
                        } catch (error) {
                            try {
                                await navigator.clipboard.write([
                                    new ClipboardItem({
                                        'text/plain': textBlob,
                                        'image/png': imageBlobs[0],
                                    }),
                                ]);
                                toast('success', images.length > 1
                                    ? 'Đã copy caption và ảnh đầu tiên. Các ảnh còn lại có thể tải xuống.'
                                    : 'Đã copy caption và ảnh.');
                                return;
                            } catch (innerError) {
                                // Fallback to text copy below.
                            }
                        }
                    }
                }

                let copiedText = false;

                if (navigator.clipboard?.writeText) {
                    try {
                        await navigator.clipboard.writeText(caption);
                        copiedText = true;
                    } catch (error) {
                        copiedText = false;
                    }
                }

                if (!copiedText && !fallbackCopyText(caption)) {
                    throw new Error('Trình duyệt không cho copy clipboard.');
                }

                toast(images.length > 0 ? 'warning' : 'success', images.length > 0
                    ? 'Đã copy caption. Trình duyệt không cho copy ảnh, hãy dùng Tải xuống.'
                    : 'Đã copy caption.');
            } catch (error) {
                toast('error', 'Không copy được bài đăng. Hãy dùng nút Tải xuống.');
            } finally {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
            }
        };

        window.marketingContentDownload = (text, imageUrls = [], baseName = 'marketing-content') => {
            const name = safeFileName(baseName);
            const images = Array.isArray(imageUrls) ? imageUrls : [];

            downloadText(text || '', `${name}-caption.txt`);

            images.forEach((url, index) => {
                window.setTimeout(() => {
                    downloadUrl(url, `${name}-${String(index + 1).padStart(2, '0')}.${imageExtension(url)}`);
                }, 120 * (index + 1));
            });

            toast('success', images.length > 0
                ? `Đang tải caption và ${images.length} ảnh.`
                : 'Đang tải caption.');
        };
    }
</script>
@endscript
