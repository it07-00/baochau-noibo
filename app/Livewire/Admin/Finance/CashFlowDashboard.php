<?php

namespace App\Livewire\Admin\Finance;

use App\Enums\Permission;
use App\Enums\Role;
use App\Enums\ContractType;
use App\Services\GoogleSheetTotalExtractor;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
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

    public string $filterServiceCategory = 'all';

    public string $filterHandlerType = 'all';  // all | tdx | non_tdx

    public string $search = '';

    public array $sheetUrls = [];

    public array $paymentStatuses = [];

    public array $paymentDates = [];

    public array $manualNccAmounts = [];

    public array $invoiceDates = [];

    public array $baoChauInvoiceMessages = [];

    public array $subcontractorInvoiceMessages = [];

    protected $paginationTheme = 'bootstrap';

    private const PAYMENT_STATUS_UNPAID = 'unpaid';

    private const PAYMENT_STATUS_PAID = 'paid';

    public function mount(): void
    {
        abort_unless(auth()->user()->can(Permission::CASH_FLOW_VIEW->value), 403);
        $this->filterYear = (int) now()->format('Y');
    }

    private function contractSources(): array
    {
        $sources = [];

        foreach (ContractType::cases() as $contractType) {
            $sources[$contractType->value] = [
                $contractType->modelClass(),
                $contractType->label(),
            ];
        }

        return $sources;
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
        $this->filterServiceCategory = 'all';
        $this->resetPage();
    }

    public function updatedFilterServiceCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterHandlerType(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    private function buildQuery(string $modelClass)
    {
        $query = $modelClass::query()->with(['customer:id,name,slug', 'staff:id,name', 'handler:id,name']);

        $query->whereNotNull('signed_at')->whereYear('signed_at', $this->filterYear);

        if ($this->filterPeriodType === 'month' && $this->filterMonth > 0) {
            $query->whereMonth('signed_at', $this->filterMonth);
        } elseif ($this->filterPeriodType === 'quarter' && $this->filterQuarter > 0) {
            $start = ($this->filterQuarter - 1) * 3 + 1;
            $query->whereMonth('signed_at', '>=', $start)
                ->whereMonth('signed_at', '<=', $start + 2);
        }

        if ($this->filterServiceCategory !== 'all' && $this->modelHasColumn($modelClass, 'loai_dich_vu')) {
            $query->where('loai_dich_vu', $this->filterServiceCategory);
        }

        return $query;
    }

    private function collectRows(): array
    {
        $contractSources = $this->contractSources();
        $sources = $this->filterContractType === 'all'
            ? $contractSources
            : [$this->filterContractType => $contractSources[$this->filterContractType] ?? null];

        $rows = [];
        foreach ($sources as $key => $source) {
            if (! $source) {
                continue;
            }

            [$modelClass, $label] = $source;

            foreach ($this->buildQuery($modelClass)->get() as $contract) {
                $contractValue = (int) $contract->value;
                $revenue = (int) $contract->revenue;
                $nccPayment = (int) $contract->ncc_payment;
                $paymentStatus = $contract->ncc_payment_status === self::PAYMENT_STATUS_PAID
                    ? self::PAYMENT_STATUS_PAID
                    : self::PAYMENT_STATUS_UNPAID;

                $handlerName = $contract->handler?->name;

                $rows[] = [
                    'id' => $contract->id,
                    'source_key' => $key,
                    'type' => $label,
                    'type_badge_class' => $this->contractTypeBadgeClass($key),
                    'service_category' => (string) ($contract->loai_dich_vu ?? ''),
                    'shd_bc' => $contract->shd_bc,
                    'shd_cxl' => (string) ($contract->shd_cxl ?? ''),
                    'customer' => $contract->customer?->name,
                    'customer_slug' => $contract->customer?->slug,
                    'handler' => $handlerName,
                    'is_tdx_handler' => $this->isTdxHandler($handlerName),
                    'staff' => $contract->staff?->name,
                    'signed_at' => $contract->signed_at?->format('d/m/Y'),
                    'submitted_at' => $contract->submitted_at?->format('d/m/Y'),
                    'submitted_at_input' => $contract->submitted_at?->format('Y-m-d'),
                    'contract_note' => $this->contractNote($contract),
                    'value_without_vat' => (int) round($contractValue / self::VAT_MULTIPLIER),
                    'revenue' => $revenue,
                    'commission' => (int) $contract->commission,
                    'ncc_payment' => $nccPayment,
                    'ncc_payment_sheet_url' => (string) ($contract->ncc_payment_sheet_url ?? ''),
                    'ncc_payment_updated_at' => $contract->ncc_payment_updated_at?->format('d/m/Y H:i'),
                    'ncc_payment_status' => $paymentStatus,
                    'ncc_payment_status_label' => $paymentStatus === self::PAYMENT_STATUS_PAID ? 'Đã thanh toán' : 'Chưa thanh toán',
                    'ncc_payment_status_badge_class' => $paymentStatus === self::PAYMENT_STATUS_PAID
                        ? 'bg-success text-white'
                        : 'bg-danger text-white',
                    'ncc_payment_paid_at' => $contract->ncc_payment_paid_at?->format('d/m/Y'),
                    'ncc_payment_paid_at_input' => $contract->ncc_payment_paid_at?->format('Y-m-d'),
                    'net_received' => $revenue - $nccPayment,
                ];
            }
        }

        if ($this->filterHandlerType === 'tdx') {
            $rows = array_values(array_filter($rows, fn ($r) => $r['is_tdx_handler']));
        } elseif ($this->filterHandlerType === 'non_tdx') {
            $rows = array_values(array_filter($rows, fn ($r) => ! $r['is_tdx_handler']));
        }

        $keyword = mb_strtolower(trim($this->search), 'UTF-8');
        if ($keyword !== '') {
            $rows = array_values(array_filter($rows, function ($r) use ($keyword) {
                return str_contains(mb_strtolower((string) ($r['customer'] ?? ''), 'UTF-8'), $keyword)
                    || str_contains(mb_strtolower((string) ($r['shd_bc'] ?? ''), 'UTF-8'), $keyword)
                    || str_contains(mb_strtolower((string) ($r['handler'] ?? ''), 'UTF-8'), $keyword);
            }));
        }

        usort($rows, fn ($a, $b) => strcmp($b['signed_at'] ?? '', $a['signed_at'] ?? ''));

        return $rows;
    }

    private function contractTypeBadgeClass(string $sourceKey): string
    {
        return match ($sourceKey) {
            'waste' => 'bg-success text-white',
            'consulting' => 'bg-primary text-white',
            'project' => 'bg-warning text-dark',
            'commercial' => 'bg-info text-dark',
            'sustainability' => 'bg-secondary text-white',
            'energy' => 'bg-danger text-white',
            default => 'bg-light text-dark border',
        };
    }

    private function contractNote($contract): string
    {
        foreach (['notes', 'note'] as $field) {
            $note = trim((string) ($contract->{$field} ?? ''));

            if ($note !== '') {
                return $note;
            }
        }

        return '';
    }

    private function serviceCategoryOptions(): array
    {
        $contractSources = $this->contractSources();
        $sources = $this->filterContractType === 'all'
            ? $contractSources
            : [$this->filterContractType => $contractSources[$this->filterContractType] ?? null];

        $options = [];

        foreach ($sources as $source) {
            if (! $source) {
                continue;
            }

            [$modelClass] = $source;

            if (defined("$modelClass::SERVICE_TYPES")) {
                $options = array_merge($options, $modelClass::SERVICE_TYPES);
            }

            if ($this->modelHasColumn($modelClass, 'loai_dich_vu')) {
                $options = array_merge(
                    $options,
                    $modelClass::query()
                        ->whereNotNull('loai_dich_vu')
                        ->where('loai_dich_vu', '!=', '')
                        ->distinct()
                        ->pluck('loai_dich_vu')
                        ->toArray()
                );
            }
        }

        $options = array_values(array_unique(array_filter(array_map(
            static fn ($option) => trim((string) $option),
            $options
        ))));

        sort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return $options;
    }

    public function updateBaoChauInvoiceNumber(string $sourceKey, int $contractId, mixed $invoiceNumber): void
    {
        abort_unless($this->isAccountant(), 403);
        $contractSources = $this->contractSources();
        abort_unless(array_key_exists($sourceKey, $contractSources), 404);

        [$modelClass] = $contractSources[$sourceKey];
        $value = substr(trim((string) $invoiceNumber), 0, 255);
        $stateKey = $this->sheetStateKey($sourceKey, $contractId);

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

    public function updateInvoiceDate(string $sourceKey, int $contractId): void
    {
        abort_unless($this->isAccountant(), 403);
        $contractSources = $this->contractSources();
        abort_unless(array_key_exists($sourceKey, $contractSources), 404);

        $stateKey = $this->sheetStateKey($sourceKey, $contractId);
        $raw = trim((string) ($this->invoiceDates[$stateKey] ?? ''));

        [$modelClass] = $contractSources[$sourceKey];
        $contract = $modelClass::query()->findOrFail($contractId);

        if ($raw === '') {
            $contract->forceFill(['submitted_at' => null])->save();
            $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa ngày xuất hóa đơn.']);
            return;
        }

        try {
            $date = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $raw)->startOfDay();
        } catch (\Throwable) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Ngày không hợp lệ.']);
            return;
        }

        $contract->forceFill(['submitted_at' => $date])->save();

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật ngày xuất hóa đơn: ' . $date->format('d/m/Y') . '.']);
    }

    public function updateNccPaymentManual(string $sourceKey, int $contractId): void
    {
        abort_unless($this->isAccountant(), 403);
        $contractSources = $this->contractSources();
        abort_unless(array_key_exists($sourceKey, $contractSources), 404);

        $stateKey = $this->sheetStateKey($sourceKey, $contractId);
        $raw = trim((string) ($this->manualNccAmounts[$stateKey] ?? ''));

        // Allow formatted numbers like "1,500,000" or plain "1500000"
        $raw = str_replace([',', '.', ' '], '', $raw);

        if ($raw === '' || !ctype_digit($raw)) {
            $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'Vui lòng nhập số tiền hợp lệ.']);
            return;
        }

        $amount = (int) $raw;

        [$modelClass] = $contractSources[$sourceKey];
        $contract = $modelClass::query()->findOrFail($contractId);
        $contract->forceFill([
            'ncc_payment'            => $amount,
            'ncc_payment_updated_at' => now(),
        ])->save();

        $this->dispatch('swal:toast', [
            'type'    => 'success',
            'message' => 'Đã cập nhật chi NCC: ' . number_format($amount) . 'đ',
        ]);
    }

    public function updateSubcontractorInvoiceNumber(string $sourceKey, int $contractId, mixed $invoiceNumber): void
    {
        abort_unless($this->isAccountant(), 403);
        $contractSources = $this->contractSources();
        abort_unless(array_key_exists($sourceKey, $contractSources), 404);

        [$modelClass] = $contractSources[$sourceKey];
        $value = substr(trim((string) $invoiceNumber), 0, 255);
        $stateKey = $this->sheetStateKey($sourceKey, $contractId);

        if ($value !== '' && $this->contractColumnValueExists('shd_cxl', $value, $sourceKey, $contractId)) {
            $this->subcontractorInvoiceMessages[$stateKey] = [
                'type' => 'error',
                'text' => 'Số hóa đơn nhà thầu phụ này đã tồn tại.',
            ];

            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => 'Số hóa đơn nhà thầu phụ này đã tồn tại.',
            ]);

            return;
        }

        $contract = $modelClass::query()->findOrFail($contractId);
        $contract->forceFill(['shd_cxl' => $value !== '' ? $value : null])->save();

        $this->subcontractorInvoiceMessages[$stateKey] = [
            'type' => 'success',
            'text' => 'Đã cập nhật số hóa đơn nhà thầu phụ.',
        ];

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'Đã cập nhật số hóa đơn nhà thầu phụ.',
        ]);
    }

    public function importNccPaymentFromSheet(string $sourceKey, int $contractId): void
    {
        abort_unless($this->isAccountant(), 403);
        $contractSources = $this->contractSources();
        abort_unless(array_key_exists($sourceKey, $contractSources), 404);

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

        [$modelClass] = $contractSources[$sourceKey];
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
        abort_unless($this->isAccountant(), 403);

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

                [$modelClass] = $this->contractSources()[$row['source_key']];
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

    public function updateNccPaymentStatus(string $sourceKey, int $contractId): void
    {
        abort_unless($this->isAccountant(), 403);
        $contractSources = $this->contractSources();
        abort_unless(array_key_exists($sourceKey, $contractSources), 404);

        $stateKey = $this->sheetStateKey($sourceKey, $contractId);
        $status = (string) ($this->paymentStatuses[$stateKey] ?? self::PAYMENT_STATUS_UNPAID);
        $paidAtInput = trim((string) ($this->paymentDates[$stateKey] ?? ''));

        if (! in_array($status, [self::PAYMENT_STATUS_UNPAID, self::PAYMENT_STATUS_PAID], true)) {
            $status = self::PAYMENT_STATUS_UNPAID;
        }

        $paidAt = null;
        if ($status === self::PAYMENT_STATUS_PAID) {
            if ($paidAtInput !== '') {
                try {
                    $paidAt = Carbon::createFromFormat('Y-m-d', $paidAtInput)->startOfDay();
                } catch (Throwable) {
                    $this->dispatch('swal:toast', [
                        'type' => 'error',
                        'message' => 'Ngày thanh toán không hợp lệ.',
                    ]);

                    return;
                }
            }
        }

        [$modelClass] = $contractSources[$sourceKey];
        $contract = $modelClass::query()->findOrFail($contractId);
        $contract->forceFill([
            'ncc_payment_status' => $status,
            'ncc_payment_paid_at' => $paidAt,
        ])->save();

        $this->paymentStatuses[$stateKey] = $status;
        $this->paymentDates[$stateKey] = $paidAt?->format('Y-m-d') ?? '';

        if ($status === self::PAYMENT_STATUS_PAID && $paidAt !== null) {
            $message = 'Đã cập nhật: Đã thanh toán (' . $paidAt->format('d/m/Y') . ').';
        } elseif ($status === self::PAYMENT_STATUS_PAID) {
            $message = 'Đã cập nhật: Đã thanh toán.';
        } else {
            $message = 'Đã cập nhật: Chưa thanh toán.';
        }

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    private function contractColumnValueExists(string $column, string $value, string $currentSourceKey, int $currentContractId): bool
    {
        foreach ($this->contractSources() as $sourceKey => [$modelClass]) {
            $model = new $modelClass;

            if (! Schema::hasColumn($model->getTable(), $column)) {
                continue;
            }

            $query = $modelClass::query()->where($column, $value);

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

    public function stateKey(string $sourceKey, int $contractId): string
    {
        return $this->sheetStateKey($sourceKey, $contractId);
    }

    public function collapseId(string $sourceKey, int $contractId): string
    {
        return 'sheetEditor_' . $this->stateKey($sourceKey, $contractId);
    }

    public function baoChauMessageFor(string $stateKey): ?array
    {
        return $this->baoChauInvoiceMessages[$stateKey] ?? null;
    }

    public function subcontractorMessageFor(string $stateKey): ?array
    {
        return $this->subcontractorInvoiceMessages[$stateKey] ?? null;
    }

    public function selectedPaymentStatus(string $stateKey, array $row): string
    {
        return $this->paymentStatuses[$stateKey] ?? ($row['ncc_payment_status'] ?? self::PAYMENT_STATUS_UNPAID);
    }

    public function isTdxRow(array $row): bool
    {
        return !empty($row['is_tdx_handler']);
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
        $this->primePaymentStates($allRows);
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
            'contractTypes' => array_map(fn ($s) => $s[1], $this->contractSources()),
            'serviceCategoryOptions' => $this->serviceCategoryOptions(),
            'availableYears' => range((int) now()->format('Y'), 2024),
            'filterHandlerType' => $this->filterHandlerType,
            'canEditBaoChauInvoice' => $this->isAccountant(),
            'canManageNccPayment' => $this->isAccountant(),
            'canEditInvoiceDate' => $this->isAccountant(),
        ])->layout('admin.layouts.app', ['title' => 'Dòng tiền']);
    }

    private function isAccountant(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            Role::KE_TOAN->value,
            'ketoan',
            'ke_toan',
        ]);
    }

    private function sheetStateKey(string $sourceKey, int $contractId): string
    {
        return $sourceKey . '_' . $contractId;
    }

    private function isTdxHandler(?string $handlerName): bool
    {
        if ($handlerName === null) {
            return false;
        }

        $lower = mb_strtolower($handlerName, 'UTF-8');

        return str_contains($lower, 'trái đất xanh')
            || str_contains($lower, 'trai dat xanh');
    }

    private function extractAmountFromSheetUrl(string $sheetUrl, bool $forceRefresh = false): int
    {
        return app(GoogleSheetTotalExtractor::class)->extractTotalFromUrl($sheetUrl, $forceRefresh);
    }

    private function modelHasColumn(string $modelClass, string $column): bool
    {
        return Schema::hasColumn((new $modelClass)->getTable(), $column);
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

    private function primePaymentStates(array $rows): void
    {
        foreach ($rows as $row) {
            $stateKey = $this->sheetStateKey($row['source_key'], $row['id']);

            if (! array_key_exists($stateKey, $this->paymentStatuses)) {
                $this->paymentStatuses[$stateKey] = $row['ncc_payment_status'] ?? self::PAYMENT_STATUS_UNPAID;
            }

            if (! array_key_exists($stateKey, $this->paymentDates)) {
                $this->paymentDates[$stateKey] = $row['ncc_payment_paid_at_input'] ?? '';
            }

            if (! array_key_exists($stateKey, $this->invoiceDates)) {
                $this->invoiceDates[$stateKey] = $row['submitted_at_input'] ?? '';
            }
        }
    }
}
