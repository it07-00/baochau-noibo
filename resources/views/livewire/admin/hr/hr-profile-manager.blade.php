<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/hr-profile.css') }}">
    @endpush

    <div class="row g-3 mb-4 px-3 pt-3">
        <!-- Stats Cards -->
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-stat-card bg-white shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="hr-stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600;">TỔNG NHÂN SỰ</div>
                        <div class="fw-bold fs-4">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-stat-card bg-white shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="hr-stat-icon" style="background: #dcfce7; color: #166534;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600;">CHÍNH THỨC</div>
                        <div class="fw-bold fs-4">{{ $stats['active'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-stat-card bg-white shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="hr-stat-icon" style="background: #fef9c3; color: #854d0e;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600;">THỬ VIỆC</div>
                        <div class="fw-bold fs-4">{{ $stats['probation'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-stat-card bg-white shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="hr-stat-icon" style="background: #e0e7ff; color: #3730a3;">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600;">THỰC TẬP</div>
                        <div class="fw-bold fs-4">{{ $stats['intern'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="hr-stat-card bg-white shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="hr-stat-icon" style="background: #fee2e2; color: #991b1b;">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600;">ĐÃ NGHỈ</div>
                        <div class="fw-bold fs-4">{{ $stats['resigned'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="px-3 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-light-subtle"><i class="bi bi-search"></i></span>
                            <input type="text" wire:model.live.debounce.400ms="search"
                                class="form-control border-light-subtle"
                                placeholder="Tìm theo tên, mã NV, email, SĐT..."
                                style="border-radius: 0 8px 8px 0;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="departmentFilter" class="form-select form-select-sm border-light-subtle" style="border-radius: 8px;">
                            <option value="">Tất cả phòng ban</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="statusFilter" class="form-select form-select-sm border-light-subtle" style="border-radius: 8px;">
                            <option value="">Tất cả trạng thái</option>
                            @foreach(\App\Models\User::EMPLOYMENT_STATUSES as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="workTypeFilter" class="form-select form-select-sm border-light-subtle" style="border-radius: 8px;">
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
        <div class="card border-0 shadow-sm hr-table" style="border-radius: 12px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Nhân viên</th>
                            <th>Mã NV</th>
                            <th>Phòng ban</th>
                            <th>Trạng thái</th>
                            <th>Loại</th>
                            <th>Ngày vào</th>
                            <th class="text-center">SĐT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr onclick="window.location='{{ route('app.hr.detail', $u) }}'" style="cursor: pointer;">
                                <td class="ps-3 text-muted">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; background: #f1f5f9;" class="flex-shrink-0">
                                            @if($u->avatar_url)
                                                <img src="{{ $u->avatar_url }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center h-100 text-muted" style="font-size: 0.85rem; font-weight: 600;">
                                                    {{ mb_substr($u->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-body">{{ $u->name }}</div>
                                            <div class="text-muted" style="font-size: 0.78rem;">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted fw-bold" style="font-size: 0.85rem;">{{ $u->employee_code ?: '—' }}</span>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem;">{{ $u->department->name ?? '—' }}</span>
                                </td>
                                <td @click.stop>
                                    <select class="form-select form-select-sm border-0 hr-inline-select hr-badge-{{ $u->employment_status }}"
                                        wire:change="updateQuickField({{ $u->id }}, 'employment_status', $event.target.value)">
                                        @foreach(\App\Models\User::EMPLOYMENT_STATUSES as $val => $label)
                                            <option value="{{ $val }}" {{ $u->employment_status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td @click.stop>
                                    <select class="form-select form-select-sm border-0 hr-inline-select hr-badge-{{ $u->work_type }}"
                                        wire:change="updateQuickField({{ $u->id }}, 'work_type', $event.target.value)">
                                        @foreach(\App\Models\User::WORK_TYPES as $val => $label)
                                            <option value="{{ $val }}" {{ $u->work_type === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem;">{{ $u->start_date?->format('d/m/Y') ?? '—' }}</span>
                                </td>
                                <td class="text-center">
                                    <span style="font-size: 0.85rem;">{{ $u->phone ?? '—' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    Không tìm thấy nhân viên nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="card-footer bg-white border-top py-2 px-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
