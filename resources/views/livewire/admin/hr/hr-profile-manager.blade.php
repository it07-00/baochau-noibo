<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/hr-profile.css') }}?v={{ config('app.version') }}">
    @endpush

    <div class="row g-3 mb-4 px-3 pt-3">
        <!-- Stats Cards -->
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-stat-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="hr-stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-75 fw-semibold">TỔNG NHÂN SỰ</div>
                        <div class="fw-bold fs-4">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-stat-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="hr-stat-icon bg-green-pale text-green-dark">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-75 fw-semibold">CHÍNH THỨC</div>
                        <div class="fw-bold fs-4">{{ $stats['active'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-stat-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="hr-stat-icon bg-yellow-pale text-yellow-dark">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-75 fw-semibold">THỬ VIỆC</div>
                        <div class="fw-bold fs-4">{{ $stats['probation'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-stat-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="hr-stat-icon bg-indigo-pale text-indigo-dark">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-75 fw-semibold">THỰC TẬP</div>
                        <div class="fw-bold fs-4">{{ $stats['intern'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-stat-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="hr-stat-icon bg-red-pale text-red-dark">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-75 fw-semibold">ĐÃ NGHỈ</div>
                        <div class="fw-bold fs-4">{{ $stats['resigned'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="px-3 mb-3">
        <div class="card border border-light-subtle shadow-sm rounded-3 bg-body">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-light-subtle bg-body-tertiary"><i
                                    class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" wire:model.live.debounce.400ms="search"
                                class="form-control border-light-subtle rounded-end-2 bg-body-tertiary"
                                placeholder="Tìm theo tên, mã NV, email, SĐT...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="departmentFilter"
                            class="form-select form-select-sm border-light-subtle rounded-3 bg-body-tertiary">
                            <option value="">Tất cả phòng ban</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="statusFilter"
                            class="form-select form-select-sm border-light-subtle rounded-3 bg-body-tertiary">
                            <option value="">Tất cả trạng thái</option>
                            @foreach(\App\Models\User::EMPLOYMENT_STATUSES as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="workTypeFilter"
                            class="form-select form-select-sm border-light-subtle rounded-3 bg-body-tertiary">
                            <option value="">Tất cả loại</option>
                            @foreach(\App\Models\User::WORK_TYPES as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Table -->
    <div class="px-3">
        <div class="card border border-light-subtle shadow-sm hr-table rounded-3 overflow-hidden bg-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-body-tertiary border-bottom border-light-subtle">
                        <tr>
                            <th class="ps-3 w-50px">#</th>
                            <th>Nhân viên</th>
                            <th>Phòng ban</th>
                            <th>Trạng thái</th>
                            <th>Loại</th>
                            <th>Ngày vào</th>
                            <th class="text-center">SĐT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr onclick="window.location='{{ route('app.hr.detail', $u) }}'" class="cursor-pointer">
                                <td class="ps-3 text-muted">
                                    {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="wh-36 rounded-circle overflow-hidden bg-light flex-shrink-0">
                                            @if($u->avatar_url)
                                                <img src="{{ $u->avatar_url }}" alt="" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <div
                                                    class="d-flex align-items-center justify-content-center h-100 text-muted fs-85 fw-semibold">
                                                    {{ mb_substr($u->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-body">{{ $u->name }}</div>
                                            <div class="text-muted fs-78">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fs-85">{{ $u->department->name ?? '—' }}</span>
                                </td>
                                <td @click.stop>
                                    <select
                                        class="form-select form-select-sm border-0 hr-inline-select hr-badge-{{ $u->employment_status }}"
                                        wire:change="updateQuickField({{ $u->id }}, 'employment_status', $event.target.value)">
                                        @foreach(\App\Models\User::EMPLOYMENT_STATUSES as $val => $label)
                                            <option value="{{ $val }}" {{ $u->employment_status === $val ? 'selected' : '' }}>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td @click.stop>
                                    <select
                                        class="form-select form-select-sm border-0 hr-inline-select hr-badge-{{ $u->work_type }}"
                                        wire:change="updateQuickField({{ $u->id }}, 'work_type', $event.target.value)">
                                        @foreach(\App\Models\User::WORK_TYPES as $val => $label)
                                            <option value="{{ $val }}" {{ $u->work_type === $val ? 'selected' : '' }}>{{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <span class="fs-85">{{ $u->start_date?->format('d/m/Y') ?? '—' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fs-85">{{ $u->phone ?? '—' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    Không tìm thấy nhân viên nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="card-footer border-top py-2 px-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</div>
</div>