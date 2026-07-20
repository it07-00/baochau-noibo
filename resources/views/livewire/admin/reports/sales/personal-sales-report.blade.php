<div class="personal-sales-board">

    {{-- Bộ lọc --}}
    <div class="card border border-light-subtle shadow-sm mb-4 rounded-3 bg-body">
        <div class="card-body py-3 px-4">
            <div class="row g-3 align-items-end">
                @if(auth()->user()->hasAnyRole([\App\Enums\Role::IT->value, \App\Enums\Role::GIAM_DOC->value, \App\Enums\Role::TP_KINH_DOANH->value]) || auth()->user()->can('roles.view'))
                <div class="col-md-4 col-lg-4">
                    <label class="form-label fw-semibold mb-1  text-muted">Nhân viên</label>
                    <select wire:model.live="filter_staff" class="form-select border-light-subtle">
                        <option value="">Tất cả nhân viên</option>
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3 col-lg-2">
                    <label class="form-label fw-semibold mb-1  text-muted">Năm</label>
                    <select wire:model.live="year" class="form-select border-light-subtle">
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
        Dữ liệu theo: <span class="fw-bold">{{ $staffDetail?->name ?? 'Tất cả nhân viên' }}</span> - Năm {{ $year }}
    </div>

    <div class="card border border-light-subtle shadow-sm mb-4 rounded-3 overflow-hidden bg-body">
        <div class="card-header bg-body-tertiary border-bottom py-3">
            <h5 class="mb-0 fw-bold text-uppercase board-title board-title-kpi">Bảng doanh số cá nhân</h5>
        </div>
        <div class="table-responsive">
            <table class="table board-table table-kpi align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center w-80px" >Tháng</th>
                        <th class="text-end">DS cam kết</th>
                        <th class="text-end">DS cam kết lũy kế</th>
                        <th class="text-end">Thực tế (Doanh số từ HĐ) (đ)</th>
                        <th class="text-end">DS thực hiện lũy kế</th>
                        <th class="text-end">Còn thiếu</th>
                        <th class="text-center w-120px" >% hoàn thành KPI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($personalRows as $row)
                        <tr>
                            <td class="text-center fw-semibold">{{ $row['month'] }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row['target'], 0, ',', '.') }}đ</td>
                            <td class="text-end fw-semibold text-danger">{{ number_format($row['target_cumulative'], 0, ',', '.') }}đ</td>
                            <td class="text-end fw-semibold">{{ $row['actual'] > 0 ? number_format($row['actual'], 0, ',', '.') . 'đ' : '—' }}</td>
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
