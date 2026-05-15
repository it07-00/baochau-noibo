<?php

namespace App\Livewire\Admin\Finance;

use App\Enums\Permission;
use App\Enums\Role;
use App\Services\GoogleSheetTotalExtractor;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CashFlowDashboard extends Component
{
    use WithPagination;

    private const VAT_MULTIPLIER = 1.08;

    public int $filterYear;

    public string $filterPeriodType = 'year';  // year | quarter | month

    public int $filterMonth = 0;

    public int $filterQuarter = 0;

    public string $filterContractType = 'all';

    public array $sheetUrls = [];

    public array $baoChauInvoiceMessages = [];

    protected $paginationTheme = 'bootstrap';

    private const CONTRACT_SOURCES = [
        'waste' => [ContractWaste::class,         'Chất thải & Tiếng ồn'],
        'consulting' => [ContractLegal::class,         'Pháp lý & Hồ sơ MT'],
        'project' => [ContractTechnical::class,     'Kỹ thuật & Ứng phó SC'],
        'commercial' => [ContractResearch::class,      'NC & CĐ Công nghệ'],
        'sustainability' => [ContractSustainability::class, 'TV & BC PTBV'],
        'energy' => [ContractEmission::class,      'Phát thải & Năng lượng'],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can(Permission::CASH_FLOW_VIEW->value), 403);
        $this->filterYear = (int) now()->format('Y');
    }

    public function updatedFilterPeriodType(): void
    {
        $this->filterMonth = 0;
        $this->filterQuarter = 0;
        $this->resetPage();
    }

    public function updatedFilterYear(): void
    {
        $this->resetPage();
    }

    public function updatedFilterMonth(): void
    {
        $this->resetPage();
    }

    public function updatedFilterQuarter(): void
    {
        $this->resetPage();
    }

    public function updatedFilterContractType(): void
    {
        $this->resetPage();
    }

    private function buildQuery(string $modelClass)
    {
        $query = $modelClass::query()->with(['customer:id,name,slug', 'staff:id,name']);

        $query->whereNotNull('signed_at')->whereYear('signed_at', $this->filterYear);

        if ($this->filterPeriodType === 'month' && $this->filterMonth > 0) {
            $query->whereMonth('signed_at', $this->filterMonth);
        } elseif ($this->filterPeriodType === 'quarter' && $this->filterQuarter > 0) {
            $start = ($this->filterQuarter - 1) * 3 + 1;
            $query->whereMonth('signed_at', '>=', $start)
                ->whereMonth('signed_at', '<=', $start + 2);
        }

        return $query;
    }

    private function collectRows(): array
    {
        $sources = $this->filterContractType === 'all'
            ? self::CONTRACT_SOURCES
            : [$this->filterContractType => self::CONTRACT_SOURCES[$this->filterContractType]];

        $rows = [];
        foreach ($sources as $key => [$modelClass, $label]) {
            foreach ($this->buildQuery($modelClass)->get() as $contract) {
                $contractValue = (int) $contract->value;
                $revenue = (int) $contract->revenue;
                $nccPayment = (int) $contract->ncc_payment;

                $rows[] = [
                    'id' => $contract->id,
                    'source_key' => $key,
                    'type' => $label,
                    'shd_bc' => $contract->shd_bc,
                    'customer' => $contract->customer?->name,
                    'customer_slug' => $contract->customer?->slug,
                    'staff' => $contract->staff?->name,
                    'signed_at' => $contract->signed_at?->format('d/m/Y'),
                    'value_without_vat' => (int) round($contractValue / self::VAT_MULTIPLIER),
                    'revenue' => $revenue,
                    'commission' => (int) $contract->commission,
                    'ncc_payment' => $nccPayment,
                    'ncc_payment_sheet_url' => (string) ($contract->ncc_payment_sheet_url ?? ''),
                    'ncc_payment_updated_at' => $contract->ncc_payment_updated_at?->format('d/m/Y H:i'),
                    'net_received' => $revenue - $nccPayment,
                ];
            }
        }

        usort($rows, fn ($a, $b) => strcmp($b['signed_at'] ?? '', $a['signed_at'] ?? ''));

        return $rows;
    }

    public function updateBaoChauInvoiceNumber(string $sourceKey, int $contractId, mixed $invoiceNumber): void
    {
        abort_unless(auth()->user()?->hasRole(Role::KE_TOAN->value), 403);
        abort_unless(array_key_exists($sourceKey, self::CONTRACT_SOURCES), 404);

        [$modelClass] = self::CONTRACT_SOURCES[$sourceKey];
        $value = substr(trim((string) $invoiceNumber), 0, 255);
        $stateKey = $this->sheetStateKey($sourceKey, $contractId);

        if ($value !== '' && $this->baoChauInvoiceNumberExists($value, $sourceKey, $contractId)) {
            $this->baoChauInvoiceMessages[$stateKey] = [
                'type' => 'error',
                'text' => 'Số hóa đơn Bảo Châu này đã tồn tại.',
            ];

            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => 'Số hóa đơn Bảo Châu này đã tồn tại.',
            ]);

            return;
        }

        $contract = $modelClass::query()->findOrFail($contractId);
        $contract->forceFill(['shd_bc' => $value !== '' ? $value : null])->save();

        $this->baoChauInvoiceMessages[$stateKey] = [
            'type' => 'success',
            'text' => 'Đã cập nhật hợp đồng Bảo Châu.',
        ];

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'Đã cập nhật số hóa đơn Bảo Châu.',
        ]);
    }

    public function importNccPaymentFromSheet(string $sourceKey, int $contractId): void
    {
        abort_unless(auth()->user()?->hasRole(Role::KE_TOAN->value), 403);
        abort_unless(array_key_exists($sourceKey, self::CONTRACT_SOURCES), 404);

        $stateKey = $this->sheetStateKey($sourceKey, $contractId);
        $sheetUrl = trim((string) ($this->sheetUrls[$stateKey] ?? ''));

        if ($sheetUrl === '') {
            $this->dispatch('swal:toast', [
                'type' => 'warning',
                'message' => 'Vui lòng nhập link Google Sheet trước.',
            ]);

            return;
        }

        try {
            $amount = $this->extractAmountFromSheetUrl($sheetUrl, true);
        } catch (Throwable $e) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);

            return;
        }

        [$modelClass] = self::CONTRACT_SOURCES[$sourceKey];
        $contract = $modelClass::query()->findOrFail($contractId);
        $contract->forceFill([
            'ncc_payment' => $amount,
            'ncc_payment_sheet_url' => $sheetUrl,
            'ncc_payment_updated_at' => now(),
        ])->save();

        $this->sheetUrls[$stateKey] = $sheetUrl;

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'Đã cập nhật chi nhà cung cấp: ' . number_format($amount) . 'đ',
        ]);
    }

    public function importAllNccPaymentsFromSheets(): void
    {
        abort_unless(auth()->user()?->hasRole(Role::KE_TOAN->value), 403);

        $rows = $this->collectRows();
        $this->primeSheetUrls($rows);

        $updatedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($rows as $row) {
            $stateKey = $this->sheetStateKey($row['source_key'], $row['id']);
            $sheetUrl = trim((string) ($this->sheetUrls[$stateKey] ?? ''));

            if ($sheetUrl === '') {
                $skippedCount++;
                continue;
            }

            try {
                $amount = $this->extractAmountFromSheetUrl($sheetUrl, true);

                [$modelClass] = self::CONTRACT_SOURCES[$row['source_key']];
                $contract = $modelClass::query()->findOrFail($row['id']);
                $contract->forceFill([
                    'ncc_payment' => $amount,
                    'ncc_payment_sheet_url' => $sheetUrl,
                    'ncc_payment_updated_at' => now(),
                ])->save();

                $updatedCount++;
            } catch (Throwable) {
                $failedCount++;
            }
        }

        if ($updatedCount === 0 && $failedCount === 0) {
            $this->dispatch('swal:toast', [
                'type' => 'warning',
                'message' => 'Không có link Google Sheet nào để cập nhật.',
            ]);

            return;
        }

        $message = "Đã cập nhật {$updatedCount} hợp đồng";

        if ($skippedCount > 0) {
            $message .= ", bỏ qua {$skippedCount} hợp đồng chưa có link";
        }

        if ($failedCount > 0) {
            $message .= ", lỗi {$failedCount} hợp đồng";
        }

        $this->dispatch('swal:toast', [
            'type' => $failedCount > 0 ? 'warning' : 'success',
            'message' => $message . '.',
        ]);
    }

    private function baoChauInvoiceNumberExists(string $invoiceNumber, string $currentSourceKey, int $currentContractId): bool
    {
        foreach (self::CONTRACT_SOURCES as $sourceKey => [$modelClass]) {
            $model = new $modelClass;
            $query = $modelClass::query()->where('shd_bc', $invoiceNumber);

            if ($sourceKey === $currentSourceKey) {
                $query->where($model->getKeyName(), '!=', $currentContractId);
            }

            if ($query->exists()) {
                return true;
            }
        }

        return false;
    }

    public function exportExcel(): StreamedResponse
    {
        abort_unless(auth()->user()->can(Permission::CASH_FLOW_EXPORT->value), 403);

        $rows = $this->collectRows();
        $periodLabel = $this->buildPeriodLabel();
        $totals = $this->buildTotals($rows);

        return response()->streamDownload(
            function () use ($rows, $periodLabel, $totals) {
                echo view('admin.finance.cash-flow-export', compact('rows', 'periodLabel', 'totals'));
            },
            'DongTien_'.now()->format('d_m_Y').'.xls',
            ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']
        );
    }

    private function buildTotals(array $rows): array
    {
        return [
            'value_without_vat' => array_sum(array_column($rows, 'value_without_vat')),
            'revenue' => array_sum(array_column($rows, 'revenue')),
            'commission' => array_sum(array_column($rows, 'commission')),
            'ncc_payment' => array_sum(array_column($rows, 'ncc_payment')),
            'net_received' => array_sum(array_column($rows, 'net_received')),
            'count' => count($rows),
        ];
    }

    private function buildPeriodLabel(): string
    {
        return match ($this->filterPeriodType) {
            'month' => "Tháng {$this->filterMonth}/{$this->filterYear}",
            'quarter' => "Quý {$this->filterQuarter}/{$this->filterYear}",
            default => "Năm {$this->filterYear}",
        };
    }

    public function render()
    {
        $allRows = $this->collectRows();
        $this->primeSheetUrls($allRows);
        $totals = $this->buildTotals($allRows);

        $perPage = 10;
        $currentPage = $this->getPage();
        $currentItems = array_slice($allRows, ($currentPage - 1) * $perPage, $perPage);
        $paginatedRows = new LengthAwarePaginator(
            $currentItems,
            count($allRows),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        return view('livewire.admin.finance.cash-flow-dashboard', [
            'rows' => $paginatedRows,
            'totals' => $totals,
            'periodLabel' => $this->buildPeriodLabel(),
            'contractTypes' => array_map(fn ($s) => $s[1], self::CONTRACT_SOURCES),
            'availableYears' => range((int) now()->format('Y'), 2024),
            'canEditBaoChauInvoice' => auth()->user()->hasRole(Role::KE_TOAN->value),
            'canManageNccPayment' => auth()->user()->hasRole(Role::KE_TOAN->value),
        ])->layout('admin.layouts.app', ['title' => 'Dòng tiền']);
    }

    private function sheetStateKey(string $sourceKey, int $contractId): string
    {
        return $sourceKey . '_' . $contractId;
    }

    private function extractAmountFromSheetUrl(string $sheetUrl, bool $forceRefresh = false): int
    {
        return app(GoogleSheetTotalExtractor::class)->extractTotalFromUrl($sheetUrl, $forceRefresh);
    }

    private function primeSheetUrls(array $rows): void
    {
        foreach ($rows as $row) {
            $stateKey = $this->sheetStateKey($row['source_key'], $row['id']);

            if (! array_key_exists($stateKey, $this->sheetUrls)) {
                $this->sheetUrls[$stateKey] = $row['ncc_payment_sheet_url'] ?? '';
            }
        }
    }
}
