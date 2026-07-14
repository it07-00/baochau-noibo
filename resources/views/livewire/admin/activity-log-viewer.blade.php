<div class="container-fluid px-4 py-4">
    @section('title', 'Nhật ký hoạt động')
    @section('page_title', 'Nhật ký hoạt động')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="fa-solid fa-clock-history me-2 text-primary"></i>Nhật ký hoạt động
            </h4>
            <p class="text-muted small mb-0 mt-1">Toàn bộ thao tác tạo, sửa, xóa trong hệ thống.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-12px">

                {{-- Filters & Toolbar --}}
                <div class="card-header border-0 bg-transparent p-4 pb-0">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
                        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 w-100">
                            {{-- Search Input --}}
                            <div class="input-group flex-grow-1">
                                <span class="input-group-text bg-body-tertiary border-end-0 border-light-subtle">
                                    <i class="fa-solid fa-magnifying-glass text-muted"></i>
                                </span>
                                <input wire:model.live.debounce.300ms="search"
                                       type="text"
                                       class="form-control border-start-0 ps-0 border-light-subtle"
                                       placeholder="Tìm theo mô tả, người thực hiện...">
                                @if($search)
                                    <button wire:click="$set('search', '')" class="btn btn-outline-secondary border-light-subtle" type="button">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                @endif
                            </div>

                            {{-- Filters Dropdowns --}}
                            <div class="d-flex gap-2">
                                <select wire:model.live="subjectType" class="form-select border-light-subtle" style="min-width: 180px;">
                                    <option value="">Tất cả đối tượng</option>
                                    @foreach ($subjectTypes as $st)
                                        <option value="{{ $st['value'] }}">{{ $st['label'] }}</option>
                                    @endforeach
                                </select>

                                <select wire:model.live="event" class="form-select border-light-subtle" style="min-width: 140px;">
                                    <option value="">Tất cả sự kiện</option>
                                    @foreach ($events as $ev)
                                        <option value="{{ $ev }}">{{ ucfirst($ev) }}</option>
                                    @endforeach
                                </select>

                                <select wire:model.live="perPage" class="form-select border-light-subtle" style="width: 120px;">
                                    <option value="20">20 dòng</option>
                                    <option value="50">50 dòng</option>
                                    <option value="100">100 dòng</option>
                                </select>
                            </div>

                            @if($search || $subjectType || $event || $dateFrom || $dateTo)
                                <button wire:click="resetFilters" class="btn btn-outline-danger text-nowrap">
                                    <i class="fa-solid fa-circle-xmark me-1"></i>Xóa lọc
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-body-tertiary text-uppercase text-secondary font-monospace" style="font-size: 0.75rem; border-bottom: 1px solid var(--bs-border-color-translucent);">
                                <tr>
                                    <th class="border-0 px-4 py-3" style="width: 80px;">ID</th>
                                    <th class="border-0 px-4 py-3">Người thực hiện</th>
                                    <th class="border-0 px-4 py-3" style="width: 140px;">Sự kiện</th>
                                    <th class="border-0 px-4 py-3">Đối tượng</th>
                                    <th class="border-0 px-4 py-3">Mô tả</th>
                                    <th class="border-0 px-4 py-3" style="width: 100px; text-align: center;">Thay đổi</th>
                                    <th class="border-0 px-4 py-3" style="width: 180px;">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                @forelse ($activities as $activity)
                                    <tr wire:key="al-{{ $activity->id }}" style="border-bottom: 1px solid var(--bs-border-color-translucent);">
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
                                            <i class="fa-solid fa-inbox text-muted d-block mb-3" style="font-size: 3rem; opacity: 0.4;"></i>
                                            <span class="text-muted d-block">
                                                @if($search || $subjectType || $event || $dateFrom || $dateTo)
                                                    Không có kết quả phù hợp với bộ lọc.
                                                @else
                                                    Chưa có nhật ký hoạt động nào.
                                                @endif
                                            </span>
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
