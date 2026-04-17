<div class="personal-sales-board">
    <style>
        .personal-sales-board .board-title {
            color: #0f172a;
            letter-spacing: .2px;
        }

        .personal-sales-board .board-title-kpi { border-left: 4px solid #ec4899; padding-left: 12px; }

        .personal-sales-board .board-table thead th {
            color: #fff;
            font-weight: 700;
            border-color: rgba(255, 255, 255, 0.25);
        }

        .personal-sales-board .table-kpi thead th {
            background: linear-gradient(90deg, #ec4899 0%, #f43f5e 100%);
        }

        .personal-sales-board .board-table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .personal-sales-board .board-table tbody tr:hover {
            background: #f1f5f9;
        }

        .personal-sales-board .board-table tfoot tr {
            background: linear-gradient(90deg, #6d28d9 0%, #7c3aed 100%);
            color: #fff;
        }

        /* Dark mode */
        :root[data-bs-theme=dark] .personal-sales-board .board-title {
            color: rgba(255, 255, 255, 0.9);
        }
        :root[data-bs-theme=dark] .personal-sales-board .board-table tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.04);
        }
        :root[data-bs-theme=dark] .personal-sales-board .board-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        :root[data-bs-theme=dark] .personal-sales-board .card-header.bg-white {
            background-color: var(--bs-body-bg) !important;
        }
    </style>

    @php
        $scopeLabel = $staffDetail?->name ?? 'Tất cả nhân viên';
    @endphp

    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3 px-4">
            <div class="row g-3 align-items-end">
                @if(auth()->user()->hasAnyRole(['it', 'giam-doc', 'quan-ly', 'tp-kinh-doanh']) || auth()->user()->can('roles.view'))
                <div class="col-md-4 col-lg-4">
                    <label class="form-label fw-semibold mb-1  text-muted">Nhân viên</label>
                    <select wire:model.live="filter_staff" class="form-select">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3 col-lg-2">
                    <label class="form-label fw-semibold mb-1  text-muted">Năm</label>
                    <select wire:model.live="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}">Năm {{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <button type="button" wire:click="$refresh" class="btn btn-success w-100 fw-semibold">Thống Kê</button>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4  text-muted fw-semibold">
        Dữ liệu theo: <span class="text-dark">{{ $scopeLabel }}</span> - Năm {{ $year }}
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-uppercase board-title board-title-kpi">Bảng doanh số cá nhân</h5>
        </div>
        <div class="table-responsive">
            <table class="table board-table table-kpi align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width:80px">Tháng</th>
                        <th class="text-end">DS cam kết</th>
                        <th class="text-end">DS cam kết lũy kế</th>
                        <th class="text-end">Thực tế (Tổng giá trị HĐ ký) (đ)</th>
                        <th class="text-end">DS thực hiện lũy kế</th>
                        <th class="text-end">Còn thiếu</th>
                        <th class="text-center" style="width:140px">% hoàn thành KPI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($personalRows as $row)
                        <tr>
                            <td class="text-center fw-semibold">{{ $row['month'] }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row['target'], 0, ',', '.') }}đ</td>
                            <td class="text-end fw-semibold text-danger">{{ number_format($row['target_cumulative'], 0, ',', '.') }}đ</td>
                            <td class="text-end fw-semibold text-dark">{{ $row['actual'] > 0 ? number_format($row['actual'], 0, ',', '.') . 'đ' : '—' }}</td>
                            <td class="text-end fw-semibold text-success">{{ number_format($row['actual_cumulative'], 0, ',', '.') }}đ</td>
                            <td class="text-end fw-semibold text-danger">{{ number_format($row['remaining'], 0, ',', '.') }}đ</td>
                            <td class="text-center fw-bold {{ ($row['kpi_pct'] ?? 0) >= 100 ? 'text-success' : 'text-danger' }}">
                                {{ $row['kpi_pct'] !== null ? $row['kpi_pct'] . '%' : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="fw-bold">
                    <tr>
                        <td class="text-center">Tổng cộng</td>
                        <td class="text-end">{{ number_format($personalTotals['target'], 0, ',', '.') }}đ</td>
                        <td class="text-end">{{ number_format($personalTotals['target_cumulative'], 0, ',', '.') }}đ</td>
                        <td class="text-end">{{ number_format($personalTotals['actual'], 0, ',', '.') }}đ</td>
                        <td class="text-end text-success">{{ number_format($personalTotals['actual_cumulative'], 0, ',', '.') }}đ</td>
                        <td class="text-end text-danger">{{ number_format($personalTotals['remaining'], 0, ',', '.') }}đ</td>
                        <td class="text-center">{{ $personalTotals['kpi_pct'] !== null ? $personalTotals['kpi_pct'] . '%' : '—' }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
