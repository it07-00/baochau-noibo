<?php

namespace App\Livewire\Admin\Reports;

use App\Services\Reports\PotentialTrendsReportService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Response;
use Livewire\Component;

class PotentialTrendsReport extends Component
{
    public string $period = 'this_month';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $staffId = '';

    public string $service = '';

    public string $province = '';

    public string $status = '';

    protected $queryString = [
        'period' => ['except' => 'this_month'],
        'dateFrom' => ['as' => 'tu-ngay', 'except' => ''],
        'dateTo' => ['as' => 'den-ngay', 'except' => ''],
        'staffId' => ['as' => 'nhan-su', 'except' => ''],
        'service' => ['as' => 'dich-vu', 'except' => ''],
        'province' => ['as' => 'khu-vuc', 'except' => ''],
        'status' => ['as' => 'trang-thai', 'except' => ''],
    ];

    public function mount(PotentialTrendsReportService $reportService): void
    {
        if ($this->dateFrom === '' || $this->dateTo === '') {
            $this->applyPeriod();
        }

        if (! $reportService->canViewAllStaff(auth()->user())) {
            $this->staffId = (string) auth()->id();
        }
    }

    public function updatedPeriod(): void
    {
        $this->applyPeriod();
        $this->dispatch('potential-report-updated');
    }

    public function updatedDateFrom(): void
    {
        $this->period = 'custom';
        $this->dispatch('potential-report-updated');
    }

    public function updatedDateTo(): void
    {
        $this->period = 'custom';
        $this->dispatch('potential-report-updated');
    }

    public function updatedStaffId(PotentialTrendsReportService $reportService): void
    {
        if (! $reportService->canViewAllStaff(auth()->user())) {
            $this->staffId = (string) auth()->id();
        }
        $this->dispatch('potential-report-updated');
    }

    public function updatedService(): void
    {
        $this->dispatch('potential-report-updated');
    }

    public function updatedProvince(): void
    {
        $this->dispatch('potential-report-updated');
    }

    public function updatedStatus(): void
    {
        $this->dispatch('potential-report-updated');
    }

    public function resetFilters(PotentialTrendsReportService $reportService): void
    {
        $this->period = 'this_month';
        $this->staffId = $reportService->canViewAllStaff(auth()->user()) ? '' : (string) auth()->id();
        $this->service = '';
        $this->province = '';
        $this->status = '';
        $this->applyPeriod();
        $this->dispatch('potential-report-updated');
    }

    public function exportCsv(PotentialTrendsReportService $reportService)
    {
        $filters = $this->filters($reportService);
        $rows = $reportService->exportRows(auth()->user(), $filters);
        $filename = 'bao-cao-tiem-nang-'.$filters['date_from'].'-'.$filters['date_to'].'.csv';

        return Response::streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Ngày', 'Nhân sự', 'Khách hàng', 'Dịch vụ', 'Khu vực', 'Trạng thái', 'Giá trị']);

            foreach ($rows as $row) {
                $value = (float) ($row->total_value ?: $row->value_inc_vat ?: $row->original_value ?: 0);
                fputcsv($handle, [
                    $row->date ? CarbonImmutable::parse($row->date)->format('d/m/Y') : '',
                    $row->staff_name ?? 'Chưa phân công',
                    $row->company_name ?? '',
                    $row->service ?? '',
                    $row->province ?? '',
                    $row->status ?? '',
                    number_format($value, 0, ',', '.'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render(PotentialTrendsReportService $reportService)
    {
        $viewer = auth()->user();
        if (! $reportService->canViewAllStaff($viewer)) {
            $this->staffId = (string) $viewer->id;
        }

        $filters = $this->filters($reportService);
        $report = $reportService->build($viewer, $filters);

        return view('livewire.admin.reports.potential-trends-report', [
            'report' => $report,
            'filterOptions' => $reportService->filterOptions($viewer),
            'canViewAllStaff' => $reportService->canViewAllStaff($viewer),
            'periodOptions' => $this->periodOptions(),
        ])->layout('admin.layouts.app', ['title' => 'Báo cáo xu hướng tiềm năng']);
    }

    private function applyPeriod(): void
    {
        $today = CarbonImmutable::today();
        [$from, $to] = match ($this->period) {
            'today' => [$today, $today],
            'last_7_days' => [$today->subDays(6), $today],
            'last_30_days' => [$today->subDays(29), $today],
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            'this_quarter' => [$today->startOfQuarter(), $today],
            'last_quarter' => [$today->subQuarter()->startOfQuarter(), $today->subQuarter()->endOfQuarter()],
            'this_year' => [$today->startOfYear(), $today],
            'last_year' => [$today->subYear()->startOfYear(), $today->subYear()->endOfYear()],
            'custom' => [
                $this->validDate($this->dateFrom) ?? $today->startOfMonth(),
                $this->validDate($this->dateTo) ?? $today,
            ],
            default => [$today->startOfMonth(), $today],
        };

        $this->dateFrom = $from->toDateString();
        $this->dateTo = $to->toDateString();
    }

    /** @return array<string, string> */
    private function periodOptions(): array
    {
        return [
            'today' => 'Hôm nay',
            'last_7_days' => '7 ngày gần nhất',
            'last_30_days' => '30 ngày gần nhất',
            'this_month' => 'Tháng này',
            'last_month' => 'Tháng trước',
            'this_quarter' => 'Quý này',
            'last_quarter' => 'Quý trước',
            'this_year' => 'Năm nay',
            'last_year' => 'Năm trước',
            'custom' => 'Khoảng ngày tùy chọn',
        ];
    }

    /** @return array{date_from:string,date_to:string,staff_id:?int,service:string,province:string,status:string} */
    private function filters(PotentialTrendsReportService $reportService): array
    {
        $today = CarbonImmutable::today();
        $from = $this->validDate($this->dateFrom) ?? $today->startOfMonth();
        $to = $this->validDate($this->dateTo) ?? $today;
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $staffId = $this->staffId !== '' ? (int) $this->staffId : null;
        if (! $reportService->canViewAllStaff(auth()->user())) {
            $staffId = (int) auth()->id();
        }

        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'staff_id' => $staffId,
            'service' => $this->service,
            'province' => $this->province,
            'status' => $this->status,
        ];
    }

    private function validDate(string $value): ?CarbonImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
