<div>
    @section('title', 'Chấm công')
    @section('page_title', 'Bảng chấm công')

    <div class="row g-3 mt-1">
        {{-- Header --}}
        <div class="col-12">
            <div class="card border border-light-subtle shadow-sm rounded-3 overflow-hidden bg-body">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-xl-between gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary flex-shrink-0 wh-44">
                                <i class="fa-solid fa-calendar-check fs-5"></i>
                            </span>
                            <div>
                                <h4 class="mb-1 fw-bold text-body">Bảng chấm công</h4>
                                <div class="text-muted small">
                            @if($lastImport)
                                Cập nhật lần cuối: {{ $lastImport->created_at->format('d/m/Y H:i') }}
                                · {{ $lastImport->total_records }} bản ghi
                            @else
                                Chưa có dữ liệu cho tháng này
                            @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2">
                            <div>
                                <label for="attendance-month" class="form-label small fw-semibold text-body mb-1">Tháng chấm công</label>
                                <input id="attendance-month" type="month" wire:model.live="selectedMonth" class="form-control border-light-subtle min-h-42px">
                            </div>
                            <div class="d-grid d-sm-flex gap-2 align-self-sm-end">
                            @can(\App\Enums\Permission::CHAM_CONG_EXPORT->value)
                            <a href="{{ route('app.attendance.export', ['month' => $selectedMonth]) }}"
                               class="btn btn-outline-success min-h-42px d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-file-excel"></i><span>Xuất tổng hợp</span>
                            </a>
                            <a href="{{ route('app.attendance.export-detail', ['month' => $selectedMonth]) }}"
                               class="btn btn-outline-success min-h-42px d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-list-check"></i><span>Xuất chi tiết</span>
                            </a>
                            @endcan
                            @can(\App\Enums\Permission::CHAM_CONG_EDIT->value)
                            <button wire:click="openImportModal"
                                    class="btn btn-primary min-h-42px d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-file-arrow-up"></i><span>Import dữ liệu</span>
                            </button>
                            <a href="{{ route('app.attendance.employees') }}"
                               class="btn btn-outline-secondary min-h-42px d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-users-gear"></i><span>Nhân viên</span>
                            </a>
                            @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $attendanceTotals = [
                'employees' => count($grid),
                'work_days' => collect($grid)->sum('work_days'),
                'late_days' => collect($grid)->sum('late_days'),
                'early_days' => collect($grid)->sum('early_days'),
            ];
        @endphp
        @foreach([
            ['label' => 'Nhân viên', 'value' => $attendanceTotals['employees'], 'decimals' => 0, 'icon' => 'fa-users', 'class' => 'primary'],
            ['label' => 'Tổng ngày công', 'value' => $attendanceTotals['work_days'], 'decimals' => 1, 'icon' => 'fa-business-time', 'class' => 'success'],
            ['label' => 'Lượt đi trễ', 'value' => $attendanceTotals['late_days'], 'decimals' => 0, 'icon' => 'fa-clock', 'class' => 'danger'],
            ['label' => 'Lượt về sớm', 'value' => $attendanceTotals['early_days'], 'decimals' => 0, 'icon' => 'fa-person-walking-arrow-right', 'class' => 'warning'],
        ] as $metric)
            <div class="col-6 col-xl-3">
                <div class="card border border-light-subtle shadow-sm rounded-3 h-100 bg-body">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-{{ $metric['class'] }}-subtle text-{{ $metric['class'] }} flex-shrink-0 wh-44">
                            <i class="fa-solid {{ $metric['icon'] }}"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="text-muted small text-truncate">{{ $metric['label'] }}</div>
                            <div class="fs-4 fw-bold text-body lh-sm">{{ number_format($metric['value'], $metric['decimals'], ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if (session('success'))
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        {{-- Bảng chấm công --}}
        <div class="col-12">
            <div class="card border border-light-subtle shadow-sm rounded-3 overflow-hidden bg-body">
                <div class="card-header bg-body-tertiary border-bottom border-light-subtle py-3 px-3 px-md-4 d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div>
                        <h6 class="mb-0 fw-bold">Chi tiết theo ngày</h6>
                        <small class="text-muted">Giờ vào và giờ ra của từng nhân viên trong tháng đã chọn</small>
                    </div>
                    <span wire:loading wire:target="selectedMonth" class="text-primary small" role="status">
                        <span class="spinner-border spinner-border-sm me-1"></span>Đang tải dữ liệu
                    </span>
                </div>
                <div class="table-responsive mxh-80vh overflow-y-auto" >
                    <table class="table table-bordered table-hover table-sm mb-0 text-nowrap fs-82" >
                        <thead style="position:sticky;top:0;z-index:2;background:var(--bs-secondary-bg);">
                            <tr>
                                <th style="position:sticky;left:0;z-index:3;background:var(--bs-secondary-bg);min-width:32px;" class="text-center">#</th>
                                <th style="position:sticky;left:32px;z-index:3;background:var(--bs-secondary-bg);min-width:100px;">Nhân viên</th>
                                @foreach($dates as $date)
                                    <th class="text-center {{ $date->isSunday() ? 'bg-light text-danger' : '' }} mnw-54px"
                                        >
                                        <div>{{ $date->format('d') }}</div>
                                        <div class="fw-normal fs-70" >{{ $date->locale('vi')->shortDayName }}</div>
                                    </th>
                                @endforeach
                                <th class="text-center min-w-40px bg-secondary-subtle" >Công</th>
                                <th class="text-center min-w-40px bg-secondary-subtle" >Trễ</th>
                                <th class="text-center min-w-40px bg-secondary-subtle" >Sớm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($grid as $empId => $row)
                                <tr>
                                    <td style="position:sticky;left:0;z-index:1;background:var(--bs-body-bg);" class="text-center">
                                        {{ $row['employee']->device_uid }}
                                    </td>
                                    <td style="position:sticky;left:32px;z-index:1;background:var(--bs-body-bg);" class="fw-semibold">
                                        {{ $row['employee']->name }}
                                    </td>
                                    @foreach($dates as $date)
                                        @if($date->isSunday())
                                            <td class="text-center bg-light align-middle px-1 py-0" >
                                                @if($this->dayData($row, $date))
                                                    <div class="fs-82 lh-sm">
                                                        <span>{{ $this->dayData($row, $date)['first'] }}</span>
                                                        @if($this->dayData($row, $date)['last'])<br><span>{{ $this->dayData($row, $date)['last'] }}</span>@endif
                                                    </div>
                                                @endif
                                            </td>
                                        @else
                                            <td class="text-center" style="vertical-align:middle;padding:2px 3px;background:{{ $this->attendanceCellStyle($this->dayData($row, $date), $date)['bg'] }};color:{{ $this->attendanceCellStyle($this->dayData($row, $date), $date)['color'] }};">
                                                @if($this->dayData($row, $date))
                                                    <div class="fs-82 lh-sm fw-semibold">
                                                        <span>{{ $this->dayData($row, $date)['first'] }}</span>
                                                        @if($this->dayData($row, $date)['last'])<br><span>{{ $this->dayData($row, $date)['last'] }}</span>@endif
                                                    </div>
                                                @elseif($this->isAbsent($this->dayData($row, $date), $date))
                                                    <span class="fs-82 fw-bold">✗</span>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                    <td class="text-center fw-bold bg-secondary-subtle" >{{ $row['work_days'] }}</td>
                                    <td class="text-center {{ $row['late_days'] > 0 ? 'text-danger fw-bold' : '' }} bg-secondary-subtle" >
                                        {{ $row['late_days'] }}
                                    </td>
                                    <td class="text-center {{ $row['early_days'] > 0 ? 'text-warning fw-bold' : '' }} bg-secondary-subtle" >
                                        {{ $row['early_days'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $daysInMonth + 5 }}" class="text-center text-muted py-5">
                                        Chưa có dữ liệu chấm công. Hãy import file từ máy chấm công.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                @if(count($grid) > 0)
                    <div class="p-2 p-md-3 border-top d-flex flex-wrap gap-2 gap-md-4 fs-75" >
                        <span><span class="d-inline-block rounded color-chip chip-success" ></span> Đúng giờ</span>
                        <span><span class="d-inline-block rounded color-chip chip-orange" ></span> Đi trễ (&gt; 08:00)</span>
                        <span><span class="d-inline-block rounded color-chip chip-warning" ></span> Về sớm (&lt; 17:00)</span>
                        <span><span class="d-inline-block rounded color-chip chip-danger" ></span> Trễ + Sớm / Vắng</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Import --}}
    @if($showImportModal)
        <div class="modal fade show d-block overlay-bg" tabindex="-1" >
            <div class="modal-dialog modal-dialog-centered {{ $importStep === 2 ? 'modal-lg' : '' }}">
                <div class="modal-content border-0 shadow-lg overflow-hidden">

                    {{-- Header --}}
                    <div class="modal-header bg-primary py-3">
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0">Import dữ liệu chấm công</h5>
                            <div class="text-white-50 fs-78" >
                                Bước {{ $importStep }}/2 —
                                {{ $importStep === 1 ? 'Chọn file' : 'Xem trước & xác nhận' }}
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white"
                                wire:click="closeImportModal"></button>
                    </div>

                    {{-- Bước 1: Upload --}}
                    @if($importStep === 1)
                        <form wire:submit="analyze">
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">File attlog.dat <span class="text-danger">*</span></label>
                                    <input type="file" wire:model="attlogFile" class="form-control form-control-sm" accept=".dat,.txt,.log,.csv">
                                    <div class="form-text">File log chấm công xuất từ máy (tên có dạng 8116..._attlog.dat)</div>
                                    @error('attlogFile') <div class="text-danger mt-1 fs-85" >{{ $message }}</div> @enderror
                                </div>
                                <div class="alert alert-info py-2 mb-0 fs-85" >
                                    <strong>Lưu ý:</strong> Để đồng bộ danh sách nhân viên từ máy, vào <strong>Quản lý NV → Đồng bộ từ máy</strong>.
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary btn-sm"
                                        wire:click="closeImportModal">Hủy</button>
                                <button type="submit" class="btn btn-primary btn-sm"
                                        wire:loading.attr="disabled" wire:target="analyze">
                                    <span wire:loading wire:target="analyze" class="spinner-border spinner-border-sm me-1"></span>
                                    Phân tích file →
                                </button>
                            </div>
                        </form>
                    @endif

                    {{-- Bước 2: Preview & Confirm --}}
                    @if($importStep === 2)
                        <div class="modal-body p-4">
                            <div class="row g-3">

                                {{-- Chọn tháng --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Tháng trong file
                                        <span class="badge bg-secondary ms-1">{{ count($detectedMonths) }}</span>
                                    </label>
                                    @error('selectedMonths')
                                        <div class="text-danger mb-1 fs-82" >{{ $message }}</div>
                                    @enderror
                                    <div class="border rounded p-2 fs-90" >
                                        @foreach($detectedMonths as $m)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       wire:model="selectedMonths" value="{{ $m }}"
                                                       id="month_{{ $m }}">
                                                <label class="form-check-label" for="month_{{ $m }}">
                                                    {{ \Carbon\Carbon::parse($m . '-01')->translatedFormat('F Y') }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text">Bỏ tick để bỏ qua tháng đó.</div>
                                </div>

                                {{-- Danh sách nhân viên --}}
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">
                                        Nhân viên trong file
                                        <span class="badge bg-secondary ms-1">{{ count($parsedEmployees) }} người</span>
                                    </label>
                                    <div class="border rounded mxh-280px overflow-y-auto" >
                                        <table class="table table-sm table-hover mb-0 fs-85" >
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th width="36" class="text-center">Nhập</th>
                                                    <th width="70">Mã máy</th>
                                                    <th>Tên nhân viên</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($parsedEmployees as $i => $emp)
                                                    <tr wire:key="emp-preview-{{ $i }}"
                                                        class="{{ $emp['is_blocked'] ? 'opacity-50 bg-light' : '' }}">
                                                        <td class="text-center align-middle">
                                                            @if($emp['is_blocked'])
                                                                <i class="fa-solid fa-ban text-danger" title="Đang bị chặn" aria-label="Đang bị chặn"></i>
                                                            @else
                                                                <input type="checkbox"
                                                                       class="form-check-input"
                                                                       wire:model="includedUids"
                                                                       value="{{ $emp['uid'] }}">
                                                            @endif
                                                        </td>
                                                        <td class="text-muted align-middle">
                                                            {{ str_pad($emp['uid'], 5, '0', STR_PAD_LEFT) }}
                                                            @if($emp['is_unknown'])
                                                                <span class="badge bg-secondary ms-1 fs-60" >Mới</span>
                                                            @endif
                                                        </td>
                                                        <td class="align-middle">
                                                            @if($emp['is_blocked'])
                                                                <span class="text-muted">{{ $emp['name'] }}</span>
                                                                <span class="badge bg-danger ms-1 fs-65" >Bị chặn</span>
                                                            @else
                                                                <input type="text"
                                                                       class="form-control form-control-sm border-0 bg-transparent px-1"
                                                                       wire:model="parsedEmployees.{{ $i }}.name"
                                                                       placeholder="{{ $emp['is_unknown'] ? 'Nhập tên để thêm mới...' : 'Tên nhân viên' }}">
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="form-text">Bỏ tick để bỏ qua logs của nhân viên đó trong lần import này.</div>
                                </div>
                            </div>

                            <div class="alert alert-warning py-2 mt-3 mb-0 fs-83" >
                                <strong>Lưu ý:</strong> Import sẽ thay thế toàn bộ log chấm công của các tháng được chọn.
                                Nhân viên <em>Chưa đăng ký</em> sẽ bị bỏ qua — hãy đồng bộ danh sách trước.
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm"
                                    wire:click="$set('importStep', 1)">← Quay lại</button>
                            <button type="button" class="btn btn-primary btn-sm"
                                    wire:click="import"
                                    wire:loading.attr="disabled" wire:target="import">
                                <span wire:loading wire:target="import" class="spinner-border spinner-border-sm me-1"></span>
                                Xác nhận import
                            </button>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @endif
</div>
