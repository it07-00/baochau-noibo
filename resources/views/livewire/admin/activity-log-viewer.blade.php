<div class="activity-log-page">
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
            <div class="pure-card al-card card-bg shadow-custom">

                {{-- Header --}}
                <div class="pure-card-header al-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between">
                    <div>
                        <h3 class="al-title m-0">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Nhật ký hoạt động
                        </h3>
                        <div class="text-muted small mt-1">Toàn bộ thao tác tạo, sửa, xóa trong hệ thống.</div>
                    </div>

                    <div class="al-toolbar d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 mt-2 mt-lg-0">
                        <div class="input-group input-group-sm al-search flex-grow-1">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search"
                                   type="text"
                                   class="form-control border-start-0 ps-0"
                                   placeholder="Tìm theo mô tả, người thực hiện...">
                            @if($search)
                                <button wire:click="$set('search', '')" class="btn btn-outline-secondary" type="button">
                                    <i class="bi bi-x"></i>
                                </button>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            <select wire:model.live="subjectType" class="form-select form-select-sm al-select" style="min-width:160px;">
                                <option value="">Tất cả đối tượng</option>
                                @foreach ($subjectTypes as $st)
                                    <option value="{{ $st['value'] }}">{{ $st['label'] }}</option>
                                @endforeach
                            </select>

                            <select wire:model.live="event" class="form-select form-select-sm al-select" style="min-width:130px;">
                                <option value="">Tất cả sự kiện</option>
                                @foreach ($events as $ev)
                                    <option value="{{ $ev }}">{{ ucfirst($ev) }}</option>
                                @endforeach
                            </select>

                            <select wire:model.live="perPage" class="form-select form-select-sm al-select" style="width:120px;">
                                <option value="20">20 dòng</option>
                                <option value="50">50 dòng</option>
                                <option value="100">100 dòng</option>
                            </select>
                        </div>

                        @if($search || $subjectType || $event || $dateFrom || $dateTo)
                            <button wire:click="resetFilters" class="btn btn-sm btn-outline-danger text-nowrap">
                                <i class="bi bi-x-circle me-1"></i>Xóa lọc
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Table --}}
                <div class="pure-card-body p-0">
                    <div class="table-responsive">
                        <table class="table al-table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width:56px;">ID</th>
                                    <th>Người thực hiện</th>
                                    <th style="width:120px;">Sự kiện</th>
                                    <th>Đối tượng</th>
                                    <th>Mô tả</th>
                                    <th style="width:90px;">Thay đổi</th>
                                    <th style="width:160px;">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activities as $activity)
                                    <tr wire:key="al-{{ $activity->id }}">
                                        <td class="text-muted fw-semibold">{{ $activity->id }}</td>

                                        <td>
                                            @if ($activity->causer)
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="al-avatar">{{ strtoupper(substr($activity->causer->name, 0, 1)) }}</span>
                                                    <span class="fw-semibold">{{ $activity->causer->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">
                                                    <i class="bi bi-gear me-1"></i>Hệ thống
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            @php
                                                $badge = match($activity->event) {
                                                    'created' => ['cls' => 'bg-label-success', 'icon' => 'bi-plus-circle-fill', 'label' => 'Tạo mới'],
                                                    'updated' => ['cls' => 'bg-label-primary',  'icon' => 'bi-pencil-fill',      'label' => 'Cập nhật'],
                                                    'deleted' => ['cls' => 'bg-label-danger',   'icon' => 'bi-trash-fill',       'label' => 'Xóa'],
                                                    default   => ['cls' => 'bg-label-secondary','icon' => 'bi-circle-fill',      'label' => ucfirst($activity->event ?? 'N/A')],
                                                };
                                            @endphp
                                            <span class="badge {{ $badge['cls'] }}">
                                                <i class="bi {{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="fw-semibold">{{ class_basename($activity->subject_type ?? '') }}</span>
                                            @if ($activity->subject_id)
                                                <span class="text-muted"> #{{ $activity->subject_id }}</span>
                                            @endif
                                        </td>

                                        <td class="text-truncate" style="max-width:280px;" title="{{ $activity->description }}">
                                            {{ $activity->description }}
                                        </td>

                                        <td>
                                            @if ($activity->properties && $activity->properties->count())
                                                <button class="btn btn-sm btn-light"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#alModal{{ $activity->id }}">
                                                    <i class="bi bi-eye me-1"></i>Xem
                                                </button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <td class="text-muted text-nowrap" title="{{ $activity->created_at->format('d/m/Y H:i:s') }}">
                                            <i class="bi bi-clock me-1 opacity-50"></i>{{ $activity->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="bi bi-inbox text-muted d-block mb-2" style="font-size:2.5rem;"></i>
                                            <span class="text-muted">
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
                    <div class="pure-card-footer border-top px-4 py-3">
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
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-clock-history me-2 text-primary"></i>
                                Chi tiết — <span class="text-primary">{{ class_basename($activity->subject_type ?? '') }}</span> #{{ $activity->subject_id }}
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
                                                <th style="width:35%"><span class="text-danger"><i class="bi bi-dash-circle-fill me-1"></i>Cũ</span></th>
                                                <th style="width:35%"><span class="text-success"><i class="bi bi-plus-circle-fill me-1"></i>Mới</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activity->properties['attributes'] as $key => $newVal)
                                                @php
                                                    $oldVal  = $activity->properties['old'][$key] ?? null;
                                                    $changed = $oldVal !== $newVal;
                                                @endphp
                                                <tr class="{{ $changed ? '' : 'opacity-50' }}">
                                                    <td class="fw-semibold font-monospace">{{ $key }}</td>
                                                    <td class="{{ $changed ? 'text-danger bg-danger-subtle' : 'text-muted' }}">
                                                        {{ $oldVal !== null ? (is_array($oldVal) ? json_encode($oldVal) : $oldVal) : '—' }}
                                                    </td>
                                                    <td class="{{ $changed ? 'text-success bg-success-subtle' : 'text-muted' }}">
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
                                                    <td class="fw-semibold font-monospace">{{ $key }}</td>
                                                    <td>{{ is_array($val) ? json_encode($val) : $val }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <pre class="bg-light p-3 m-0">{{ json_encode($activity->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
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
