<div>
    @section('title', 'Chấm công')
    @section('page_title', 'Bảng chấm công')

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Chấm công'],
        ];
    @endphp

    <div class="row g-3 mt-1 px-2 px-md-4">
        {{-- Header --}}
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold">Bảng chấm công</h5>
                        <div class="text-muted fs-85" >
                            @if($lastImport)
                                Cập nhật lần cuối: {{ $lastImport->created_at->format('d/m/Y H:i') }}
                                · {{ $lastImport->total_records }} bản ghi
                            @else
                                Chưa có dữ liệu cho tháng này
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2 mt-2 mt-md-0">
                        <input type="month" wire:model.live="selectedMonth" class="form-control form-control-sm mxw-100"
                               >
                        <div class="d-grid d-sm-flex gap-2 grid-2col" >
                            <a href="{{ route('app.attendance.export', ['month' => $selectedMonth]) }}"
                               class="btn btn-success btn-sm d-flex align-items-center justify-content-center gap-1">
                                <svg width="15" height="15" fill="none" viewBox="0 0 16 16">
                                    <path d="M2 12h12M8 2v8M4 10l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Xuất tổng hợp</span>
                            </a>
                            <a href="{{ route('app.attendance.export-detail', ['month' => $selectedMonth]) }}"
                               class="btn btn-outline-success btn-sm d-flex align-items-center justify-content-center gap-1">
                                <svg width="15" height="15" fill="none" viewBox="0 0 16 16">
                                    <path d="M2 12h12M8 2v8M4 10l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Xuất chi tiết</span>
                            </a>
                            <button wire:click="openImportModal"
                                    class="btn btn-primary btn-sm d-flex align-items-center justify-content-center gap-1">
                                <svg width="15" height="15" fill="none" viewBox="0 0 16 16">
                                    <path d="M8 2v8M4 6l4 4 4-4M2 13h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Import dữ liệu</span>
                            </button>
                            <a href="{{ route('app.attendance.employees') }}"
                               class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center gap-1">
                                <svg width="15" height="15" fill="none" viewBox="0 0 16 16">
                                    <circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.3"/>
                                    <path d="M3 14c0-2.8 2.2-5 5-5s5 2.2 5 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                </svg>
                                <span>Quản lý NV</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
            <div class="pure-card rounded-custom card-bg shadow-custom">
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
                                        @php
                                            $day = $row['days'][$date->day] ?? null;
                                            $isSun = $date->isSunday();
                                        @endphp
                                        @if($isSun)
                                            <td class="text-center bg-light align-middle px-1 py-0" >
                                                @if($day)
                                                    <div class="fs-82 lh-sm">
                                                        <span>{{ $day['first'] }}</span>
                                                        @if($day['last'])<br><span>{{ $day['last'] }}</span>@endif
                                                    </div>
                                                @endif
                                            </td>
                                        @else
                                            @php
                                                $isLate  = $day && $day['first'] > '08:00';
                                                $isEarly = $day && $day['last'] && $day['last'] < '17:00';
                                                $isAbsent = !$day && $date->lte(now());

                                                if ($isAbsent) {
                                                    $cellBg = '#dc3545'; $cellColor = '#fff';
                                                } elseif ($day && $isLate && $isEarly) {
                                                    $cellBg = '#dc3545'; $cellColor = '#fff';
                                                } elseif ($day && $isLate) {
                                                    $cellBg = '#fd7e14'; $cellColor = '#fff';
                                                } elseif ($day && $isEarly) {
                                                    $cellBg = '#ffc107'; $cellColor = '#000';
                                                } elseif ($day) {
                                                    $cellBg = '#198754'; $cellColor = '#fff';
                                                } else {
                                                    $cellBg = 'transparent'; $cellColor = 'inherit';
                                                }
                                            @endphp
                                            <td class="text-center" style="vertical-align:middle;padding:2px 3px;background:{{ $cellBg }};color:{{ $cellColor }};">
                                                @if($day)
                                                    <div class="fs-82 lh-sm fw-semibold">
                                                        <span>{{ $day['first'] }}</span>
                                                        @if($day['last'])<br><span>{{ $day['last'] }}</span>@endif
                                                    </div>
                                                @elseif($isAbsent)
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
                                                                <span title="Đang bị chặn" class="fs-85">🚫</span>
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
