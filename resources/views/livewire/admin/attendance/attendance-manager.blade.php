<div>
    @section('title', 'Chấm công')
    @section('page_title', 'Bảng chấm công')

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Chấm công'],
        ];
    @endphp

    <div class="row g-3 mt-1 px-4">
        {{-- Header --}}
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="mb-1 fw-bold">Bảng chấm công</h5>
                        <div class="text-muted">
                            @if($lastImport)
                                Cập nhật lần cuối: {{ $lastImport->created_at->format('d/m/Y H:i') }}
                                · {{ $lastImport->total_records }} bản ghi
                            @else
                                Chưa có dữ liệu cho tháng này
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <input type="month" wire:model.live="selectedMonth" class="form-control form-control-sm"
                               style="max-width:180px;">
                        <a href="{{ route('app.attendance.export', ['month' => $selectedMonth]) }}"
                           class="btn btn-success btn-sm d-flex align-items-center gap-2">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16">
                                <path d="M2 12h12M8 2v8M4 10l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Xuất tổng hợp
                        </a>
                        <a href="{{ route('app.attendance.export-detail', ['month' => $selectedMonth]) }}"
                           class="btn btn-outline-success btn-sm d-flex align-items-center gap-2">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16">
                                <path d="M2 12h12M8 2v8M4 10l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Xuất chi tiết
                        </a>
                        <button wire:click="$set('showImportModal', true)"
                                class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16">
                                <path d="M8 2v8M4 6l4 4 4-4M2 13h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Import dữ liệu
                        </button>
                        <a href="{{ route('app.attendance.employees') }}"
                           class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16">
                                <circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M3 14c0-2.8 2.2-5 5-5s5 2.2 5 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                            </svg>
                            Quản lý NV
                        </a>
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
                <div class="table-responsive" style="max-height:88vh;">
                    <table class="table table-bordered table-hover table-sm mb-0 text-nowrap" style="font-size: 0.88rem;">
                        <thead class="table-light" style="position:sticky;top:0;z-index:2;">
                            <tr>
                                <th style="position:sticky;left:0;z-index:3;background:#f8f9fa;min-width:40px;" class="text-center">#</th>
                                <th style="position:sticky;left:40px;z-index:3;background:#f8f9fa;min-width:120px;">Nhân viên</th>
                                @foreach($dates as $date)
                                    <th class="text-center {{ $date->isSunday() ? 'bg-light text-danger' : '' }}"
                                        style="min-width:65px;">
                                        <div>{{ $date->format('d') }}</div>
                                        <div class="fw-normal" style="font-size:0.75rem;">{{ $date->locale('vi')->shortDayName }}</div>
                                    </th>
                                @endforeach
                                <th class="text-center" style="min-width:50px;background:#f8f9fa;">Công</th>
                                <th class="text-center" style="min-width:50px;background:#f8f9fa;">Trễ</th>
                                <th class="text-center" style="min-width:50px;background:#f8f9fa;">Sớm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($grid as $empId => $row)
                                <tr>
                                    <td style="position:sticky;left:0;z-index:1;background:#fff;" class="text-center">
                                        {{ $row['employee']->device_uid }}
                                    </td>
                                    <td style="position:sticky;left:40px;z-index:1;background:#fff;" class="fw-semibold">
                                        {{ $row['employee']->name }}
                                    </td>
                                    @foreach($dates as $date)
                                        @php
                                            $day = $row['days'][$date->day] ?? null;
                                            $isSun = $date->isSunday();
                                        @endphp
                                        @if($isSun)
                                            <td class="text-center bg-light" style="vertical-align:middle;padding:2px 3px;">
                                                @if($day)
                                                    <div style="font-size:0.82rem;line-height:1.3;">
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
                                                    <div style="font-size:0.82rem;line-height:1.3;font-weight:600;">
                                                        <span>{{ $day['first'] }}</span>
                                                        @if($day['last'])<br><span>{{ $day['last'] }}</span>@endif
                                                    </div>
                                                @elseif($isAbsent)
                                                    <span style="font-size:0.82rem;font-weight:700;">✗</span>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                    <td class="text-center fw-bold" style="background:#f8f9fa;">{{ $row['work_days'] }}</td>
                                    <td class="text-center {{ $row['late_days'] > 0 ? 'text-danger fw-bold' : '' }}" style="background:#f8f9fa;">
                                        {{ $row['late_days'] }}
                                    </td>
                                    <td class="text-center {{ $row['early_days'] > 0 ? 'text-warning fw-bold' : '' }}" style="background:#f8f9fa;">
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
                    <div class="p-3 border-top d-flex flex-wrap gap-4" style="font-size:0.78rem;">
                        <span><span class="d-inline-block rounded" style="width:14px;height:14px;background:#198754;vertical-align:middle;"></span> Đúng giờ</span>
                        <span><span class="d-inline-block rounded" style="width:14px;height:14px;background:#fd7e14;vertical-align:middle;"></span> Đi trễ (&gt; 08:00)</span>
                        <span><span class="d-inline-block rounded" style="width:14px;height:14px;background:#ffc107;vertical-align:middle;"></span> Về sớm (&lt; 17:00)</span>
                        <span><span class="d-inline-block rounded" style="width:14px;height:14px;background:#dc3545;vertical-align:middle;"></span> Trễ + Sớm / Vắng</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Import --}}
    @if($showImportModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);"
             wire:click.self="$set('showImportModal', false)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit="import">
                        <div class="modal-header">
                            <h5 class="modal-title">Import dữ liệu chấm công</h5>
                            <button type="button" class="btn-close"
                                    wire:click="$set('showImportModal', false)"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">File user.dat <span class="text-danger">*</span></label>
                                <input type="file" wire:model="userFile" class="form-control form-control-sm" accept=".dat">
                                <div class="form-text">File danh sách nhân viên từ máy chấm công</div>
                                @error('userFile') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">File attlog.dat <span class="text-danger">*</span></label>
                                <input type="file" wire:model="attlogFile" class="form-control form-control-sm" accept=".dat">
                                <div class="form-text">File log chấm công (tên có dạng 8116..._attlog.dat)</div>
                                @error('attlogFile') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="alert alert-info mb-0 py-2">
                                <strong>Lưu ý:</strong> Import sẽ thay thế toàn bộ dữ liệu chấm công của tháng tương ứng.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm"
                                    wire:click="$set('showImportModal', false)">Hủy</button>
                            <button type="submit" class="btn btn-primary btn-sm"
                                    wire:loading.attr="disabled">
                                <span wire:loading wire:target="import">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                </span>
                                Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
