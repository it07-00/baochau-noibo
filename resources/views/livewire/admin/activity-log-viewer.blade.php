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
                <div class="pure-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h3 class="pure-card-title m-0">
                        <i class="bi bi-clock-history me-2"></i>Nhật ký hoạt động
                    </h3>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        {{-- Search --}}
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0" placeholder="Tìm kiếm...">
                        </div>

                        {{-- Subject type filter --}}
                        <select wire:model.live="subjectType" class="form-select form-select-sm" style="width: 170px;">
                            <option value="">-- Loại đối tượng --</option>
                            @foreach ($subjectTypes as $st)
                                <option value="{{ $st['value'] }}">{{ $st['label'] }}</option>
                            @endforeach
                        </select>

                        {{-- Event filter --}}
                        <select wire:model.live="event" class="form-select form-select-sm" style="width: 140px;">
                            <option value="">-- Sự kiện --</option>
                            @foreach ($events as $ev)
                                <option value="{{ $ev }}">{{ ucfirst($ev) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pure-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Người thực hiện</th>
                                    <th>Sự kiện</th>
                                    <th>Đối tượng</th>
                                    <th>Mô tả</th>
                                    <th>Thay đổi</th>
                                    <th style="width: 150px;">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activities as $activity)
                                    <tr>
                                        <td class="text-muted">{{ $activity->id }}</td>
                                        <td>
                                            @if ($activity->causer)
                                                <span class="fw-semibold">{{ $activity->causer->name }}</span>
                                            @else
                                                <span class="text-muted fst-italic">Hệ thống</span>
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
                                            @endphp
                                            <span class="badge bg-{{ $eventColor }}">{{ ucfirst($activity->event ?? 'N/A') }}</span>
                                        </td>
                                        <td>
                                            <span class="text-nowrap">{{ class_basename($activity->subject_type ?? '') }}</span>
                                            @if ($activity->subject_id)
                                                <span class="text-muted ">#{{ $activity->subject_id }}</span>
                                            @endif
                                        </td>
                                        <td class="text-truncate" style="max-width: 250px;" title="{{ $activity->description }}">
                                            {{ $activity->description }}
                                        </td>
                                        <td>
                                            @if ($activity->properties && $activity->properties->count())
                                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#activityModal{{ $activity->id }}">
                                                    <i class="bi bi-eye"></i> Chi tiết
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap text-muted ">
                                            {{ $activity->created_at->format('d/m/Y H:i:s') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Chưa có nhật ký hoạt động nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($activities->hasPages())
                    <div class="pure-card-footer d-flex justify-content-center py-3">
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
                                Chi tiết thay đổi — {{ class_basename($activity->subject_type ?? '') }} #{{ $activity->subject_id }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @if ($activity->properties->has('old') && $activity->properties->has('attributes'))
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Trường</th>
                                                <th>Giá trị cũ</th>
                                                <th>Giá trị mới</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activity->properties['attributes'] as $key => $newVal)
                                                @php $oldVal = $activity->properties['old'][$key] ?? '-'; @endphp
                                                <tr>
                                                    <td class="fw-semibold">{{ $key }}</td>
                                                    <td class="text-danger">{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}</td>
                                                    <td class="text-success">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</td>
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
                                                <th>Trường</th>
                                                <th>Giá trị</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activity->properties['attributes'] as $key => $val)
                                                <tr>
                                                    <td class="fw-semibold">{{ $key }}</td>
                                                    <td>{{ is_array($val) ? json_encode($val) : $val }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <pre class="bg-light p-3 rounded  mb-0">{{ json_encode($activity->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
