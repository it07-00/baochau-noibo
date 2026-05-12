<div>
    @section('title', 'Nhật ký hoạt động')
    @section('page_title', 'Nhật ký hoạt động')

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Nhật ký hoạt động']
        ];
    @endphp

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">

                {{-- Header --}}
                <div class="pure-card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <h3 class="pure-card-title m-0">
                        <i class="bi bi-clock-history me-2"></i>Nhật ký hoạt động
                    </h3>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis fs-6 fw-normal">
                        {{ $activities->total() }} bản ghi
                    </span>
                </div>

                {{-- Filter Bar --}}
                <div class="px-3 py-2 border-bottom bg-light-subtle">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-transparent">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input wire:model.live.debounce.300ms="search"
                                       type="text"
                                       class="form-control border-start-0 ps-0"
                                       placeholder="Tìm theo mô tả, người thực hiện...">
                                @if($search)
                                    <button wire:click="$set('search', '')" class="btn btn-outline-secondary border-start-0" type="button" tabindex="-1">
                                        <i class="bi bi-x"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <select wire:model.live="subjectType" class="form-select form-select-sm {{ $subjectType ? 'border-primary text-primary fw-semibold' : '' }}">
                                <option value="">Tất cả loại đối tượng</option>
                                @foreach ($subjectTypes as $st)
                                    <option value="{{ $st['value'] }}">{{ $st['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select wire:model.live="event" class="form-select form-select-sm {{ $event ? 'border-primary text-primary fw-semibold' : '' }}">
                                <option value="">Tất cả sự kiện</option>
                                @foreach ($events as $ev)
                                    <option value="{{ $ev }}">{{ ucfirst($ev) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select wire:model.live="perPage" class="form-select form-select-sm">
                                <option value="20">20 / trang</option>
                                <option value="50">50 / trang</option>
                                <option value="100">100 / trang</option>
                            </select>
                        </div>
                        @if($search || $subjectType || $event)
                            <div class="col-6 col-md-1">
                                <button wire:click="resetFilters" class="btn btn-sm btn-outline-danger w-100" title="Xóa bộ lọc">
                                    <i class="bi bi-funnel-fill me-1"></i>Xóa
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Table --}}
                <div class="pure-card-body p-0 position-relative">
                    <div wire:loading.block class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75" style="z-index:10;">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        <span class="text-muted small">Đang tải...</span>
                    </div>
                    <div class="table-responsive" wire:loading.class="opacity-50">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Người thực hiện</th>
                                    <th style="width: 100px;">Sự kiện</th>
                                    <th>Đối tượng</th>
                                    <th>Mô tả</th>
                                    <th style="width: 80px;">Thay đổi</th>
                                    <th style="width: 150px;">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activities as $activity)
                                    <tr>
                                        <td class="text-muted small">{{ $activity->id }}</td>
                                        <td>
                                            @if ($activity->causer)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                                         style="width:28px;height:28px;">
                                                        <span class="text-primary fw-bold" style="font-size:11px;">
                                                            {{ strtoupper(substr($activity->causer->name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <span class="fw-semibold small">{{ $activity->causer->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic small">
                                                    <i class="bi bi-gear me-1"></i>Hệ thống
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $eventColor = match($activity->event) {
                                                    'created' => 'success',
                                                    'updated' => 'primary',
                                                    'deleted' => 'danger',
                                                    default   => 'secondary',
                                                };
                                                $eventIcon = match($activity->event) {
                                                    'created' => 'bi-plus-circle-fill',
                                                    'updated' => 'bi-pencil-fill',
                                                    'deleted' => 'bi-trash-fill',
                                                    default   => 'bi-circle-fill',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $eventColor }}-subtle text-{{ $eventColor }}-emphasis border border-{{ $eventColor }}-subtle">
                                                <i class="bi {{ $eventIcon }} me-1" style="font-size:10px;"></i>{{ ucfirst($activity->event ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-nowrap fw-semibold small">{{ class_basename($activity->subject_type ?? '') }}</span>
                                            @if ($activity->subject_id)
                                                <span class="text-muted small">#{{ $activity->subject_id }}</span>
                                            @endif
                                        </td>
                                        <td class="text-truncate" style="max-width: 280px;" title="{{ $activity->description }}">
                                            <span class="small">{{ $activity->description }}</span>
                                        </td>
                                        <td>
                                            @if ($activity->properties && $activity->properties->count())
                                                <button class="btn btn-sm btn-outline-secondary py-0 px-2"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#activityModal{{ $activity->id }}"
                                                        title="Xem chi tiết thay đổi">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="text-muted small" title="{{ $activity->created_at->format('d/m/Y H:i:s') }}">
                                                <i class="bi bi-clock me-1 opacity-50"></i>{{ $activity->created_at->diffForHumans() }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="bi bi-inbox text-muted" style="font-size:2rem;"></i>
                                            <p class="text-muted mt-2 mb-0">
                                                @if($search || $subjectType || $event)
                                                    Không có kết quả phù hợp với bộ lọc hiện tại.
                                                @else
                                                    Chưa có nhật ký hoạt động nào.
                                                @endif
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($activities->hasPages())
                    <div class="pure-card-footer d-flex align-items-center justify-content-between py-2 px-3 flex-wrap gap-2">
                        <small class="text-muted">
                            Hiển thị {{ $activities->firstItem() }}–{{ $activities->lastItem() }} trong tổng {{ $activities->total() }} bản ghi
                        </small>
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modals for detail --}}
    @foreach ($activities as $activity)
        @if ($activity->properties && $activity->properties->count())
            <div class="modal fade" id="activityModal{{ $activity->id }}" tabindex="-1" wire:ignore.self>
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-clock-history me-2 text-primary"></i>
                                Chi tiết thay đổi — <span class="text-primary">{{ class_basename($activity->subject_type ?? '') }}</span> #{{ $activity->subject_id }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            @if ($activity->properties->has('old') && $activity->properties->has('attributes'))
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:30%">Trường</th>
                                                <th style="width:35%"><span class="text-danger"><i class="bi bi-dash-circle-fill me-1"></i>Giá trị cũ</span></th>
                                                <th style="width:35%"><span class="text-success"><i class="bi bi-plus-circle-fill me-1"></i>Giá trị mới</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activity->properties['attributes'] as $key => $newVal)
                                                @php
                                                    $oldVal = $activity->properties['old'][$key] ?? null;
                                                    $changed = $oldVal !== $newVal;
                                                @endphp
                                                <tr class="{{ $changed ? '' : 'opacity-50' }}">
                                                    <td class="fw-semibold small font-monospace">{{ $key }}</td>
                                                    <td class="{{ $changed ? 'text-danger bg-danger-subtle' : 'text-muted' }} small">
                                                        {{ $oldVal !== null ? (is_array($oldVal) ? json_encode($oldVal) : $oldVal) : '—' }}
                                                    </td>
                                                    <td class="{{ $changed ? 'text-success bg-success-subtle' : 'text-muted' }} small">
                                                        {{ is_array($newVal) ? json_encode($newVal) : $newVal }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @elseif ($activity->properties->has('attributes'))
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:40%">Trường</th>
                                                <th>Giá trị</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activity->properties['attributes'] as $key => $val)
                                                <tr>
                                                    <td class="fw-semibold small font-monospace">{{ $key }}</td>
                                                    <td class="small">{{ is_array($val) ? json_encode($val) : $val }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <pre class="bg-light p-3 m-0 small">{{ json_encode($activity->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </div>
                        <div class="modal-footer justify-content-between">
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>{{ $activity->causer?->name ?? 'Hệ thống' }}
                                &nbsp;·&nbsp;
                                <i class="bi bi-clock me-1"></i>{{ $activity->created_at->format('d/m/Y H:i:s') }}
                            </small>
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
