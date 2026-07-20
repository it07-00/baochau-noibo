<div class="container-fluid px-3 px-lg-4 py-4">
    @section('title', 'Nhật ký hoạt động')
    @section('page_title', 'Nhật ký hoạt động')

    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white p-3">
                <i class="fa-solid fa-clock-rotate-left fs-5"></i>
            </span>
            <div>
                <h4 class="fw-bold text-body mb-1">Nhật ký hoạt động</h4>
                <p class="text-secondary mb-0">Tra cứu lịch sử thay đổi và người thực hiện trong hệ thống.</p>
            </div>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
            {{ number_format($activities->total()) }} kết quả
        </span>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border shadow-sm">

                {{-- Filters & Toolbar --}}
                <div class="card-header bg-body border-bottom p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                        <div>
                            <h6 class="fw-bold text-body mb-1"><i class="fa-solid fa-filter text-primary me-2"></i>Bộ lọc nhật ký</h6>
                            <p class="text-secondary small mb-0">Có thể kết hợp nhiều điều kiện để thu hẹp kết quả.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($this->activeFilterCount > 0)
                                <span class="badge bg-primary-subtle text-primary">{{ $this->activeFilterCount }} bộ lọc</span>
                                <button wire:click="resetFilters" class="btn btn-outline-secondary btn-sm text-nowrap" type="button">
                                    <i class="fa-solid fa-rotate-left me-1"></i>Đặt lại
                                </button>
                            @endif
                            <div wire:loading wire:target="search,logName,subjectType,event,dateFrom,dateTo,perPage,resetFilters" class="text-primary small fw-semibold" role="status">
                                <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Đang lọc
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label for="activity-search" class="form-label fw-semibold">Tìm kiếm</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                                <input id="activity-search" wire:model.live.debounce.300ms="search" type="search" class="form-control" placeholder="Mô tả hoặc người thực hiện">
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <label for="activity-subject" class="form-label fw-semibold">Đối tượng</label>
                            <select id="activity-subject" wire:model.live="subjectType" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach ($subjectTypes as $st)
                                    <option value="{{ $st['value'] }}">{{ $st['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <label for="activity-event" class="form-label fw-semibold">Sự kiện</label>
                            <select id="activity-event" wire:model.live="event" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach ($events as $ev)
                                    <option value="{{ $ev }}">{{ $this->eventBadge($ev)['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <label for="activity-from" class="form-label fw-semibold">Từ ngày</label>
                            <input id="activity-from" wire:model.live="dateFrom" type="date" class="form-control">
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <label for="activity-to" class="form-label fw-semibold">Đến ngày</label>
                            <input id="activity-to" wire:model.live="dateTo" type="date" class="form-control">
                        </div>
                        <div class="col-12 col-md-3 col-xl-2">
                            <label for="activity-log-name" class="form-label fw-semibold">Nhóm nhật ký</label>
                            <select id="activity-log-name" wire:model.live="logName" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach ($logNames as $name)
                                    <option value="{{ $name }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 col-xl-2">
                            <label for="activity-per-page" class="form-label fw-semibold">Hiển thị</label>
                            <select id="activity-per-page" wire:model.live="perPage" class="form-select">
                                <option value="20">20 dòng</option>
                                <option value="50">50 dòng</option>
                                <option value="100">100 dòng</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-nowrap">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th class="px-3 py-3">ID</th>
                                    <th class="border-0 px-4 py-3">Người thực hiện</th>
                                    <th class="px-3 py-3">Sự kiện</th>
                                    <th class="border-0 px-4 py-3">Đối tượng</th>
                                    <th class="border-0 px-4 py-3">Mô tả</th>
                                    <th class="px-3 py-3 text-center">Thay đổi</th>
                                    <th class="px-3 py-3">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                @forelse ($activities as $activity)
                                    <tr wire:key="al-{{ $activity->id }}">
                                        <td class="text-muted fw-semibold px-4">{{ $activity->id }}</td>

                                        <td class="px-4">
                                            @if ($activity->causer)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold rounded-circle" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                        {{ strtoupper(substr($activity->causer->name, 0, 1)) }}
                                                    </div>
                                                    <span class="fw-semibold text-body">{{ $activity->causer->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">
                                                    <i class="fa-solid fa-gear me-1"></i>Hệ thống
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4">
                                            <span class="badge {{ $this->eventBadge($activity->event)['cls'] }} px-2 py-1.5" style="font-size: 0.75rem;">
                                                <i class="bi {{ $this->eventBadge($activity->event)['icon'] }} me-1"></i>{{ $this->eventBadge($activity->event)['label'] }}
                                            </span>
                                        </td>

                                        <td class="px-4">
                                            <span class="fw-semibold text-body">{{ class_basename($activity->subject_type ?? '') }}</span>
                                            @if ($activity->subject_id)
                                                <span class="text-muted small"> #{{ $activity->subject_id }}</span>
                                            @endif
                                        </td>

                                        <td class="px-4 text-truncate max-w-260px text-body" title="{{ $activity->description }}">
                                            {{ $activity->description }}
                                        </td>

                                        <td class="px-4 text-center">
                                            @if ($activity->properties && $activity->properties->count())
                                                <button class="btn btn-sm btn-outline-secondary rounded-8px"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#alModal{{ $activity->id }}">
                                                    <i class="fa-solid fa-eye me-1"></i>Xem
                                                </button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <td class="px-4 text-muted text-nowrap" title="{{ $activity->created_at->format('d/m/Y H:i:s') }}">
                                            <i class="fa-solid fa-clock me-1 opacity-50"></i>{{ $activity->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-body-tertiary text-secondary p-4 mb-3">
                                                <i class="fa-solid fa-box-open fs-3"></i>
                                            </span>
                                            <h6 class="fw-bold text-body mb-1">
                                                {{ $this->activeFilterCount > 0 ? 'Không tìm thấy kết quả' : 'Chưa có nhật ký hoạt động' }}
                                            </h6>
                                            <p class="text-secondary mb-3">
                                                {{ $this->activeFilterCount > 0 ? 'Hãy thay đổi hoặc đặt lại bộ lọc để xem thêm dữ liệu.' : 'Các thao tác trong hệ thống sẽ xuất hiện tại đây.' }}
                                            </p>
                                            @if($this->activeFilterCount > 0)
                                                <button type="button" wire:click="resetFilters" class="btn btn-outline-primary btn-sm">
                                                    <i class="fa-solid fa-rotate-left me-1"></i>Đặt lại bộ lọc
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                @if ($activities->hasPages())
                    <div class="card-footer border-top bg-transparent px-4 py-3">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Detail Modals --}}
    @foreach ($activities as $activity)
        @if ($activity->properties && $activity->properties->count())
            <div class="modal fade" id="alModal{{ $activity->id }}" tabindex="-1" wire:ignore.self>
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-bottom-0 bg-transparent p-4 pb-3">
                            <h5 class="modal-title fw-bold text-body">
                                <i class="fa-solid fa-clock-history me-2 text-primary"></i>
                                Chi tiết — <span class="text-primary">{{ class_basename($activity->subject_type ?? '') }}</span> #{{ $activity->subject_id }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                        </div>
                        <div class="modal-body p-0 border-0">
                            @if ($activity->properties->has('old') && $activity->properties->has('attributes'))
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="bg-body-tertiary text-uppercase text-secondary font-monospace" style="font-size: 0.75rem; border-bottom: 1px solid var(--bs-border-color-translucent);">
                                            <tr>
                                                <th class="px-4 py-3 w-30pct">Trường</th>
                                                <th class="px-4 py-3 w-35pct"><span class="text-danger"><i class="fa-solid fa-circle-minus me-1"></i>Cũ</span></th>
                                                <th class="px-4 py-3 w-35pct"><span class="text-success"><i class="fa-solid fa-circle-plus me-1"></i>Mới</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activity->properties['attributes'] as $key => $newVal)
                                                <tr class="{{ $this->hasChangedValue($activity->properties['old'][$key] ?? null, $newVal) ? '' : 'opacity-50' }}" style="border-bottom: 1px solid var(--bs-border-color-translucent);">
                                                    <td class="fw-semibold font-monospace px-4 text-body" style="font-size: 0.8rem;">{{ $key }}</td>
                                                    <td class="px-4 {{ $this->hasChangedValue($activity->properties['old'][$key] ?? null, $newVal) ? 'text-danger bg-danger-subtle fw-semibold' : 'text-muted' }}" style="font-size: 0.85rem;">
                                                        {{ $this->displayValue($activity->properties['old'][$key] ?? null) }}
                                                    </td>
                                                    <td class="px-4 {{ $this->hasChangedValue($activity->properties['old'][$key] ?? null, $newVal) ? 'text-success bg-success-subtle fw-semibold' : 'text-muted' }}" style="font-size: 0.85rem;">
                                                        {{ $this->displayValue($newVal) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @elseif ($activity->properties->has('attributes'))
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="bg-body-tertiary text-uppercase text-secondary font-monospace" style="font-size: 0.75rem; border-bottom: 1px solid var(--bs-border-color-translucent);">
                                            <tr>
                                                <th class="px-4 py-3 w-40pct">Trường</th>
                                                <th class="px-4 py-3">Giá trị</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activity->properties['attributes'] as $key => $val)
                                                <tr style="border-bottom: 1px solid var(--bs-border-color-translucent);">
                                                    <td class="fw-semibold font-monospace px-4 text-body" style="font-size: 0.8rem;">{{ $key }}</td>
                                                    <td class="px-4 text-body" style="font-size: 0.85rem;">{{ $this->displayValue($val) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <pre class="bg-body-tertiary border text-body font-monospace p-4 m-3 rounded-3" style="font-size: 0.85rem; max-height: 400px; overflow-y: auto;">{{ json_encode($activity->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </div>
                        <div class="modal-footer border-top-0 justify-content-between p-4 pt-3">
                            <small class="text-muted">
                                <i class="fa-solid fa-user me-1"></i>{{ $activity->causer?->name ?? 'Hệ thống' }}
                                &nbsp;·&nbsp;
                                <i class="fa-solid fa-clock me-1"></i>{{ $activity->created_at->format('d/m/Y H:i:s') }}
                            </small>
                            <button type="button" class="btn btn-sm btn-secondary rounded-8px" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
