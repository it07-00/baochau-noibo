<?php

namespace App\Livewire\Admin\QuotationDocuments;

use App\Enums\Permission;
use App\Enums\QuotationStatus;
use App\Enums\Role;
use App\Livewire\Concerns\CleanMoneyInput;
use App\Models\ContractLegal;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationDocument;
use App\Models\User;
use App\Support\Quotations\QuotationTemplateCatalog;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationDocumentManager extends Component
{
    use CleanMoneyInput, WithPagination;

    // Filters
    public $search = '';

    public $filter_staff = '';

    public $date_from = '';

    public $date_to = '';

    // Modal state
    public $showModal = false;

    public $isEditing = false;

    public $selectedId = null;

    public $selectedDoc = null;

    public $selectedCustomerId = '';

    // Form data
    public array $formData = [];

    public array $summaryItems = [];

    public array $detailItems = [];

    public array $matrixRows = [];

    public int $vatRate = 8;

    public int $detailTotal = 0;

    public int $matrixTotal = 0;

    // Default terms
    private const DEFAULT_TERMS = "Kết quả thực hiện: Báo cáo Quan trắc môi trường lao động\nThời gian có cuốn báo cáo QTMTLĐ: 10-15 ngày kể từ ngày quan trắc và có đầy đủ thông tin khách hàng cung cấp (không tính ngày lễ, thứ 7, chủ nhật);\nChi phí trên đã bao gồm VAT tại thời điểm xuất hóa đơn.\nPhương thức thanh toán:\n• 50% sau khi ký hợp đồng\n• 50% sau khi hoàn thành báo cáo Quan trắc môi trường lao động\nHình thức: chuyển khoản\nChúng tôi xin cam kết sẽ tiến hành và hoàn thành công việc theo đúng nội dung được nêu trong báo giá!";

    public const PREDEFINED_GROUPS = [
        'I. YẾU TỐ VI KHÍ HẬU',
        'II. YẾU TỐ VẬT LÝ',
        'III. YẾU TỐ TIẾP XÚC',
        'IV. YẾU TỐ TÂM SINH LÝ VÀ ECGONOMI',
        'V. YẾU TỐ BỤI CÁC LOẠI',
        'VI. YẾU TỐ HÓA HỌC',
        'VII. CHI PHÍ KHÁC',
    ];

    private array $moneyFields = ['discount'];

    protected function rules(): array
    {
        return [
            'formData.document_number' => 'required|string|max:100',
            'formData.date' => 'required|date',
            'formData.customer_name' => 'required|string|max:255',
            'formData.customer_address' => 'nullable|string|max:500',
            'formData.customer_phone' => 'nullable|string|max:50',
            'formData.customer_contact' => 'nullable|string|max:255',
            'formData.customer_email' => 'nullable|email|max:255',
            'formData.customer_tax_code' => 'nullable|string|max:50',
            'formData.service_type' => 'nullable|string|max:255',
            'formData.template_key' => 'required|string|max:80',
            'formData.work_location' => 'nullable|string|max:500',
            'formData.valid_until' => 'nullable|date|after_or_equal:formData.date',
            'formData.notes' => 'nullable|string|max:5000',
            'formData.terms' => 'nullable|string|max:5000',
            'formData.discount' => 'nullable|numeric|min:0',
            'vatRate' => 'required|integer|min:0|max:100',
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.document_number.required' => 'Vui lòng nhập số báo giá.',
            'formData.date.required' => 'Vui lòng chọn ngày báo giá.',
            'formData.customer_name.required' => 'Vui lòng nhập tên khách hàng/công ty.',
            'formData.customer_email.email' => 'Email không hợp lệ.',
            'formData.valid_until.after_or_equal' => 'Ngày hiệu lực phải sau ngày báo giá.',
        ];
    }

    public function mount(): void
    {
        $this->authorizePermission(Permission::QUOTATION_TRACKING_VIEW);
        $this->resetForm();
    }

    private function authorizePermission(Permission $permission): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        if ($user->hasRole(Role::IT->value)) {
            return;
        }

        abort_unless($user->can($permission->value), 403);
    }

    private function authorizeDocumentOwner(QuotationDocument $doc): void
    {
        if ($this->isKinhDoanhUser() && (int) $doc->staff_id !== (int) auth()->id()) {
            abort(403, 'Bạn chỉ được thao tác trên báo giá do bạn phụ trách.');
        }
    }

    // ── SUMMARY ITEMS MANAGEMENT (Bảng 01) ──

    public function addSummaryItem(): void
    {
        $this->summaryItems[] = [
            'description' => '',
            'unit' => 'Hồ sơ',
            'quantity' => 1,
            'unit_price' => 0,
            'amount' => 0,
            'note' => '',
        ];
        $this->recalculate();
    }

    public function removeSummaryItem(int $index): void
    {
        unset($this->summaryItems[$index]);
        $this->summaryItems = array_values($this->summaryItems);
        $this->recalculate();
    }

    public function moveSummaryItemUp(int $index): void
    {
        if ($index <= 0) {
            return;
        }
        [$this->summaryItems[$index - 1], $this->summaryItems[$index]] = [$this->summaryItems[$index], $this->summaryItems[$index - 1]];
    }

    public function moveSummaryItemDown(int $index): void
    {
        if ($index >= count($this->summaryItems) - 1) {
            return;
        }
        [$this->summaryItems[$index + 1], $this->summaryItems[$index]] = [$this->summaryItems[$index], $this->summaryItems[$index + 1]];
    }

    // ── DETAIL ITEMS MANAGEMENT (Bảng 02) ──

    public function addDetailItem(): void
    {
        // Default to the last group used, or the first predefined group
        $groupOptions = $this->groupOptions();
        $lastGroup = count($this->detailItems) > 0 ? end($this->detailItems)['group_name'] : ($groupOptions[0] ?? self::PREDEFINED_GROUPS[0]);

        $this->detailItems[] = [
            'group_name' => $lastGroup,
            'description' => '',
            'unit' => 'Mẫu',
            'quantity' => 1,
            'unit_price' => 0,
            'amount' => 0,
            'note' => '',
        ];
        $this->recalculate();
    }

    public function removeDetailItem(int $index): void
    {
        unset($this->detailItems[$index]);
        $this->detailItems = array_values($this->detailItems);
        $this->recalculate();
    }

    public function moveDetailItemUp(int $index): void
    {
        if ($index <= 0) {
            return;
        }
        [$this->detailItems[$index - 1], $this->detailItems[$index]] = [$this->detailItems[$index], $this->detailItems[$index - 1]];
    }

    public function moveDetailItemDown(int $index): void
    {
        if ($index >= count($this->detailItems) - 1) {
            return;
        }
        [$this->detailItems[$index + 1], $this->detailItems[$index]] = [$this->detailItems[$index], $this->detailItems[$index + 1]];
    }

    // ── SYNC DETAIL TO SUMMARY ──

    // PLLĐ matrix management

    public function addMatrixRow(): void
    {
        $this->matrixRows[] = $this->defaultMatrixRow();
        $this->recalculate();
    }

    public function removeMatrixRow(int $index): void
    {
        unset($this->matrixRows[$index]);
        $this->matrixRows = array_values($this->matrixRows);
        $this->recalculate();
    }

    public function moveMatrixRowUp(int $index): void
    {
        if ($index <= 0) {
            return;
        }
        [$this->matrixRows[$index - 1], $this->matrixRows[$index]] = [$this->matrixRows[$index], $this->matrixRows[$index - 1]];
    }

    public function moveMatrixRowDown(int $index): void
    {
        if ($index >= count($this->matrixRows) - 1) {
            return;
        }
        [$this->matrixRows[$index + 1], $this->matrixRows[$index]] = [$this->matrixRows[$index], $this->matrixRows[$index + 1]];
    }

    public function plldMetricColumns(): array
    {
        return [
            'microclimate' => 'Vi khí hậu',
            'noise' => 'Tiếng ồn',
            'dust' => 'Bụi',
            'vocs' => 'VOCs',
            'co2' => 'CO2',
            'reaction_time' => 'Phản xạ',
            'muscle_load' => 'Cơ bắp',
            'work_characteristics' => 'Đặc điểm CV',
            'visual_stress' => 'Thị giác',
            'posture' => 'Tư thế',
            'responsibility' => 'Trách nhiệm',
        ];
    }

    private function defaultMatrixRow(): array
    {
        return [
            'job_title' => '',
            'employee_count' => 0,
            'assessment_count' => 0,
            'microclimate' => 0,
            'noise' => 0,
            'dust' => 0,
            'vocs' => 0,
            'co2' => 0,
            'reaction_time' => 0,
            'muscle_load' => 0,
            'work_characteristics' => 0,
            'visual_stress' => 0,
            'posture' => 0,
            'responsibility' => 0,
            'total' => 0,
        ];
    }

    public function syncDetailToSummary(): void
    {
        $this->recalculate();

        $customerName = trim($this->formData['customer_name'] ?? '');
        $serviceType = trim($this->formData['service_type'] ?? 'Quan trắc môi trường lao động');
        if ($serviceType === 'Khác' || empty($serviceType)) {
            $serviceType = 'Quan trắc môi trường lao động';
        }
        $year = $this->formData['date'] ? date('Y', strtotime($this->formData['date'])) : date('Y');
        $location = trim($this->formData['work_location'] ?? '');

        $description = "Thực hiện {$serviceType} {$year}";
        if ($customerName) {
            $description .= ' tại '.$customerName;
        }
        if ($location) {
            $description .= ', '.$location;
        }

        $this->summaryItems = [
            [
                'description' => $description,
                'unit' => 'Hồ sơ',
                'quantity' => 1,
                'unit_price' => $this->detailTotal,
                'amount' => $this->detailTotal,
                'note' => '',
            ],
        ];

        $this->recalculate();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã đồng bộ tổng chi phí chi tiết sang Bảng 01.']);
    }

    public function updatedSummaryItems(): void
    {
        $this->recalculate();
    }

    public function updatedDetailItems(mixed $value = null, ?string $key = null): void
    {
        if (is_string($key) && preg_match('/^(\d+)\.description$/', $key, $matches) === 1) {
            $this->applyLaborMonitoringPriceToDetailItem((int) $matches[1], (string) $value);

            return;
        }

        if (is_string($key) && preg_match('/^(\d+)\.group_name$/', $key, $matches) === 1) {
            $this->resetDetailDescriptionWhenGroupChanges((int) $matches[1]);
        }

        $this->recalculate();
    }

    public function updatedMatrixRows(): void
    {
        $this->recalculate();
    }

    public function updatedVatRate(): void
    {
        $this->recalculate();
    }

    public function updatedFormDataDiscount(): void
    {
        $this->recalculate();
    }

    public function updatedFormDataTemplateKey(): void
    {
        $this->applyTemplatePreset(false);
    }

    public function updatedSelectedCustomerId($value): void
    {
        $customer = Customer::find($value);
        if (! $customer) {
            return;
        }

        $this->formData['customer_name'] = $customer->name;
        $this->formData['customer_address'] = $customer->address ?? '';
        $this->formData['customer_contact'] = $customer->representative ?? '';
        $this->formData['customer_tax_code'] = $customer->tax_code ?? '';
    }

    public function applySelectedTemplatePreset(): void
    {
        $this->applyTemplatePreset(true);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã áp dụng mẫu báo giá.']);
    }

    private function applyTemplatePreset(bool $replaceRows): void
    {
        $template = QuotationTemplateCatalog::find($this->formData['template_key'] ?? null);

        $this->formData['template_key'] = $template['key'];

        if ($replaceRows || trim((string) ($this->formData['service_type'] ?? '')) === '') {
            $this->formData['service_type'] = $template['service_type'] ?? '';
        }

        if ($replaceRows || empty($this->summaryItems)) {
            $this->summaryItems = [QuotationTemplateCatalog::defaultSummaryItem($template['key'])];
        }

        if ($replaceRows) {
            $this->detailItems = [];
            $this->matrixRows = $template['key'] === 'plld' ? [$this->defaultMatrixRow()] : [];
            $this->vatRate = (int) ($template['vat_rate'] ?? $this->vatRate);
        }

        $this->recalculate();
    }

    public function templateLabels(): array
    {
        return QuotationTemplateCatalog::labels();
    }

    public function selectedTemplateLabel(?string $key = null): string
    {
        return QuotationTemplateCatalog::find($key ?? ($this->formData['template_key'] ?? null))['label'];
    }

    public function groupOptions(): array
    {
        $groups = QuotationTemplateCatalog::detailGroups($this->formData['template_key'] ?? null);

        return $groups !== [] ? $groups : self::PREDEFINED_GROUPS;
    }

    public function detailPriceCatalogForGroup(?string $groupName): array
    {
        return QuotationTemplateCatalog::detailPriceCatalog($this->formData['template_key'] ?? null, $groupName);
    }

    public function isLaborMonitoringTemplate(): bool
    {
        return ($this->formData['template_key'] ?? QuotationTemplateCatalog::DEFAULT_KEY) === QuotationTemplateCatalog::DEFAULT_KEY;
    }

    public function isLaborMonitoringDocument(?QuotationDocument $doc): bool
    {
        return ($doc?->template_key ?? QuotationTemplateCatalog::DEFAULT_KEY) === QuotationTemplateCatalog::DEFAULT_KEY;
    }

    private function usesLaborMonitoringTemplate(): bool
    {
        return $this->isLaborMonitoringTemplate();
    }

    private function applyLaborMonitoringPriceToDetailItem(int $index, string $description): void
    {
        if (! $this->usesLaborMonitoringTemplate() || ! isset($this->detailItems[$index])) {
            return;
        }

        $catalogItem = QuotationTemplateCatalog::findDetailPriceItem($this->formData['template_key'] ?? null, $description);
        if (! $catalogItem) {
            return;
        }

        $quantity = $this->parseIntegerQuantity($this->detailItems[$index]['quantity'] ?? 1);
        $this->detailItems[$index] = array_merge(
            $this->detailItems[$index],
            QuotationTemplateCatalog::catalogDetailItem($this->formData['template_key'] ?? null, $catalogItem, $quantity)
        );
        $this->detailItems[$index]['unit_price'] = $this->formatMoneyInputValue($this->detailItems[$index]['unit_price']);

        $this->recalculate();
    }

    private function resetDetailDescriptionWhenGroupChanges(int $index): void
    {
        if (! $this->usesLaborMonitoringTemplate() || ! isset($this->detailItems[$index])) {
            return;
        }

        $description = $this->detailItems[$index]['description'] ?? '';
        if ($description === '') {
            return;
        }

        $catalogItem = QuotationTemplateCatalog::findDetailPriceItem($this->formData['template_key'] ?? null, $description);
        if (
            ! $catalogItem
            || $catalogItem['group_name'] === ($this->detailItems[$index]['group_name'] ?? null)
        ) {
            return;
        }

        $this->detailItems[$index]['description'] = '';
        $this->detailItems[$index]['unit_price'] = 0;
        $this->detailItems[$index]['amount'] = 0;
        $this->detailItems[$index]['note'] = '';
    }

    public function recalculate(): void
    {
        // Recalculate summary items
        foreach ($this->summaryItems as $i => &$item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = $this->parseMoneyValue($item['unit_price'] ?? 0);
            $item['amount'] = (int) round($qty * $price);
        }
        unset($item);

        // Recalculate detail items
        foreach ($this->detailItems as $i => &$item) {
            $qty = $this->parseIntegerQuantity($item['quantity'] ?? 0);
            $item['quantity'] = $qty;
            $price = $this->parseMoneyValue($item['unit_price'] ?? 0);
            $item['amount'] = (int) round($qty * $price);
        }
        unset($item);

        $this->detailTotal = array_sum(array_column($this->detailItems, 'amount'));
        $subtotal = $this->usesLaborMonitoringTemplate()
            ? $this->detailTotal
            : array_sum(array_column($this->summaryItems, 'amount'));

        $this->matrixTotal = 0;
        foreach ($this->matrixRows as &$row) {
            $total = 0;
            foreach (array_keys($this->plldMetricColumns()) as $key) {
                $total += (int) ($row[$key] ?? 0);
            }
            $row['total'] = $total;
            $this->matrixTotal += $total;
        }
        unset($row);

        $discount = $this->parseMoneyValue($this->formData['discount'] ?? 0);
        $afterDiscount = max(0, $subtotal - $discount);
        $vatAmount = (int) round($afterDiscount * $this->vatRate / 100);

        $this->formData['subtotal'] = $subtotal;
        $this->formData['vat_amount'] = $vatAmount;
        $this->formData['total'] = $afterDiscount + $vatAmount;
    }

    private function parseMoneyValue(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (is_string($value)) {
            $normalized = preg_replace('/\D+/', '', $value);

            return $normalized !== '' ? (float) $normalized : 0.0;
        }

        return 0.0;
    }

    private function parseIntegerQuantity(mixed $value): int
    {
        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }

        $number = is_numeric($value) ? (float) $value : 0.0;

        return max(0, (int) round($number));
    }

    private function formatMoneyInputValue(mixed $value): string
    {
        $amount = (int) $this->parseMoneyValue($value);

        return number_format($amount, 0, ',', '.');
    }

    // ── CRUD ──

    public function transferToTracking(int $id)
    {
        $this->authorizePermission(Permission::QUOTATION_TRACKING_VIEW);

        $doc = QuotationDocument::with(['quotation', 'summaryItems', 'detailItems'])->findOrFail($id);
        $this->authorizeDocumentOwner($doc);

        $quotation = $this->syncTrackingFromDocument($doc);

        if (! $quotation) {
            return null;
        }

        return redirect()->route('app.quotation-tracking.index', [
            'search' => $quotation->quotation_number ?: $quotation->company_name,
        ]);
    }

    private function syncTrackingFromDocument(QuotationDocument $doc, bool $showErrors = true): ?Quotation
    {
        $doc->load(['quotation', 'summaryItems', 'detailItems']);
        $existing = $doc->quotation ?: Quotation::where('quotation_number', $doc->document_number)->first();
        $permission = $existing ? Permission::QUOTATION_TRACKING_EDIT : Permission::QUOTATION_TRACKING_CREATE;

        if (! auth()->user()->can($permission->value)) {
            if ($showErrors) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền chuyển báo giá sang bảng theo dõi.']);
            }

            return null;
        }

        if ($existing && $this->isKinhDoanhUser() && (int) $existing->staff_id !== (int) auth()->id()) {
            if ($showErrors) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn chỉ được cập nhật báo giá do bạn phụ trách.']);
            }

            return null;
        }

        $staffId = $this->trackingStaffId($doc);
        $data = $this->trackingDataFromDocument($doc, $staffId, $existing);

        if ($existing) {
            $existing->update($data);
            $quotation = $existing->refresh();
        } else {
            $quotation = Quotation::create($data);
        }

        if ((int) $doc->quotation_id !== (int) $quotation->id) {
            $doc->update(['quotation_id' => $quotation->id]);
        }

        return $quotation;
    }

    private function isKinhDoanhUser(): bool
    {
        return auth()->user()->hasRole(Role::KINH_DOANH->value);
    }

    private function trackingStaffId(QuotationDocument $doc): int
    {
        if ($this->isKinhDoanhUser()) {
            return (int) auth()->id();
        }

        return (int) ($doc->staff_id ?: auth()->id());
    }

    private function trackingDataFromDocument(QuotationDocument $doc, int $staffId, ?Quotation $existing = null): array
    {
        $preVat = max(0, (int) $doc->subtotal - (int) $doc->discount);
        if ($preVat === 0 && (int) $doc->total > 0) {
            $preVat = (int) round((int) $doc->total / (1 + ((int) $doc->vat_rate / 100)));
        }

        $commissionValue = (int) ($existing?->commission_value ?? 0);
        $commissionTax = (int) ($existing?->commission_tax ?? 0);
        $valueIncVat = $preVat + $commissionValue + $commissionTax;
        $totalValue = ($commissionValue > 0 || $commissionTax > 0)
            ? (int) round($valueIncVat * (1 + ((int) $doc->vat_rate / 100)))
            : (int) $doc->total;

        return [
            'date' => $doc->date?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'quotation_number' => $doc->document_number,
            'staff_id' => $staffId,
            'source' => $existing?->source ?: 'Tạo báo giá',
            'company_name' => $doc->customer_name,
            'address' => $doc->customer_address,
            'work_address' => $doc->work_location ?: $doc->customer_address,
            'province' => $existing?->province,
            'industry' => $existing?->industry,
            'service' => $doc->service_type,
            'contact_person' => $doc->customer_contact,
            'work_description' => $this->trackingWorkDescription($doc),
            'status' => $existing?->status ?: QuotationStatus::DANG_THEO_DOI->value,
            'original_value' => $preVat,
            'value_inc_vat' => $valueIncVat,
            'commission_value' => $commissionValue,
            'commission_tax' => $commissionTax,
            'total_value' => $totalValue,
            'notes' => $existing?->notes ?: $this->trackingNotes($doc),
        ];
    }

    private function trackingWorkDescription(QuotationDocument $doc): ?string
    {
        $summary = $doc->summaryItems
            ->pluck('description')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->implode('; ');

        if ($summary !== '') {
            return $this->limitText($summary, 2000);
        }

        $details = $doc->detailItems
            ->take(8)
            ->pluck('description')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->implode('; ');

        return $this->limitText($details ?: $doc->service_type, 2000);
    }

    private function trackingNotes(QuotationDocument $doc): string
    {
        $parts = ['Chuyển từ báo giá Word/PDF: '.$doc->document_number];
        $notes = trim((string) $doc->notes);

        if ($notes !== '') {
            $parts[] = $notes;
        }

        return implode("\n", $parts);
    }

    private function limitText(?string $value, int $limit): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return mb_strlen($value, 'UTF-8') > $limit
            ? mb_substr($value, 0, $limit, 'UTF-8')
            : $value;
    }

    public function create(): void
    {
        $this->authorizePermission(Permission::QUOTATION_TRACKING_CREATE);

        $this->resetForm();
        $this->isEditing = false;
        $this->dispatch('open-qdoc-modal');
    }

    public function edit(int $id): void
    {
        $this->authorizePermission(Permission::QUOTATION_TRACKING_EDIT);

        $doc = QuotationDocument::with(['items', 'sections.rows'])->findOrFail($id);
        $this->authorizeDocumentOwner($doc);

        $this->selectedId = $id;
        $this->isEditing = true;
        $this->selectedCustomerId = $this->matchingCustomerId($doc->customer_name);

        $this->formData = $doc->only([
            'document_number', 'date', 'valid_until', 'customer_name', 'customer_address',
            'customer_phone', 'customer_contact', 'customer_email', 'customer_tax_code',
            'service_type', 'template_key', 'work_location', 'subtotal', 'vat_amount', 'total',
            'discount', 'notes', 'terms',
        ]);
        $this->formData['template_key'] = $this->formData['template_key'] ?: QuotationTemplateCatalog::DEFAULT_KEY;
        $this->formData['date'] = $doc->date?->format('Y-m-d') ?? '';
        $this->formData['valid_until'] = $doc->valid_until?->format('Y-m-d') ?? '';

        $this->vatRate = $doc->vat_rate;

        // Split items by type
        $this->summaryItems = $doc->items->where('item_type', 'summary')->map(fn ($item) => [
            'description' => $item->description,
            'unit' => $item->unit,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'amount' => $item->amount,
            'note' => $item->note ?? '',
        ])->values()->toArray();

        $this->detailItems = $doc->items->where('item_type', 'detail')->map(fn ($item) => [
            'group_name' => $item->group_name ?? self::PREDEFINED_GROUPS[0],
            'description' => $item->description,
            'unit' => $item->unit,
            'quantity' => $this->parseIntegerQuantity($item->quantity),
            'unit_price' => $this->formatMoneyInputValue($item->unit_price),
            'amount' => $item->amount,
            'note' => $item->note ?? '',
        ])->values()->toArray();
        $this->matrixRows = $this->matrixRowsFromDocument($doc);

        $this->recalculate();
        $this->dispatch('open-qdoc-modal');
    }

    public function duplicate(int $id): void
    {
        $this->authorizePermission(Permission::QUOTATION_TRACKING_CREATE);

        $doc = QuotationDocument::with(['items', 'sections.rows'])->findOrFail($id);
        $this->authorizeDocumentOwner($doc);

        $this->resetForm();
        $this->isEditing = false;
        $this->selectedId = null;

        $this->formData = array_merge($this->formData, $doc->only([
            'customer_name', 'customer_address', 'customer_phone', 'customer_contact',
            'customer_email', 'customer_tax_code', 'service_type', 'template_key', 'work_location',
            'discount', 'notes', 'terms',
        ]));
        $this->formData['template_key'] = $this->formData['template_key'] ?: QuotationTemplateCatalog::DEFAULT_KEY;
        $this->formData['date'] = now()->format('Y-m-d');
        $this->formData['document_number'] = $this->generateNextNumber();
        $this->selectedCustomerId = $this->matchingCustomerId($this->formData['customer_name'] ?? '');

        $this->vatRate = $doc->vat_rate;

        $this->summaryItems = $doc->items->where('item_type', 'summary')->map(fn ($item) => [
            'description' => $item->description,
            'unit' => $item->unit,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'amount' => $item->amount,
            'note' => $item->note ?? '',
        ])->values()->toArray();

        $this->detailItems = $doc->items->where('item_type', 'detail')->map(fn ($item) => [
            'group_name' => $item->group_name ?? self::PREDEFINED_GROUPS[0],
            'description' => $item->description,
            'unit' => $item->unit,
            'quantity' => $this->parseIntegerQuantity($item->quantity),
            'unit_price' => $this->formatMoneyInputValue($item->unit_price),
            'amount' => $item->amount,
            'note' => $item->note ?? '',
        ])->values()->toArray();
        $this->matrixRows = $this->matrixRowsFromDocument($doc);

        $this->recalculate();
        $this->dispatch('open-qdoc-modal');
    }

    public function viewDetail(int $id): void
    {
        $this->authorizePermission(Permission::QUOTATION_TRACKING_VIEW);

        $doc = QuotationDocument::with([
            'items' => fn ($q) => $q->orderBy('sort_order'),
            'sections.rows',
            'staff',
            'quotation',
        ])->findOrFail($id);
        $this->authorizeDocumentOwner($doc);

        $this->selectedDoc = $doc;
        $this->dispatch('open-qdoc-detail-modal');
    }

    public function summaryItemsForDetail(?QuotationDocument $doc): Collection
    {
        if (! $doc) {
            return collect();
        }

        return $doc->items->where('item_type', 'summary')->values();
    }

    public function mainPriceItemsForDetail(?QuotationDocument $doc): Collection
    {
        if (! $doc) {
            return collect();
        }

        $details = $doc->items->where('item_type', 'detail')->values();

        return $details->isNotEmpty()
            ? $details
            : $doc->items->where('item_type', 'summary')->values();
    }

    public function groupedDetailItemsForDetail(?QuotationDocument $doc): Collection
    {
        if (! $doc) {
            return collect();
        }

        return $doc->items->where('item_type', 'detail')->groupBy('group_name');
    }

    public function detailRowIndexForGroup(?QuotationDocument $doc, string $groupName, int $itemIndex): int
    {
        $groups = $this->groupedDetailItemsForDetail($doc);
        $index = 1;

        foreach ($groups as $name => $items) {
            if ((string) $name === $groupName) {
                return $index + $itemIndex;
            }

            $index += $items->count();
        }

        return $itemIndex + 1;
    }

    public function groupedDetailTotalAmount(?QuotationDocument $doc): int
    {
        return (int) $this->groupedDetailItemsForDetail($doc)
            ->sum(fn ($group) => $group->sum('amount'));
    }

    public function matrixRowsForDetail(?QuotationDocument $doc): Collection
    {
        if (! $doc) {
            return collect();
        }

        return collect($this->matrixRowsFromDocument($doc));
    }

    public function matrixTotalForDetail(?QuotationDocument $doc): int
    {
        return (int) $this->matrixRowsForDetail($doc)->sum('total');
    }

    private function matrixRowsFromDocument(QuotationDocument $doc): array
    {
        $section = $doc->sections
            ->firstWhere('section_key', 'plld_matrix');

        if (! $section) {
            return [];
        }

        return $section->rows->map(function ($row) {
            $data = array_merge($this->defaultMatrixRow(), $row->columns ?? []);
            $data['job_title'] = $row->description ?? ($data['job_title'] ?? '');
            $data['employee_count'] = (int) ($data['employee_count'] ?? 0);
            $data['assessment_count'] = (int) ($data['assessment_count'] ?? 0);

            $total = 0;
            foreach (array_keys($this->plldMetricColumns()) as $key) {
                $data[$key] = (int) ($data[$key] ?? 0);
                $total += $data[$key];
            }
            $data['total'] = $total;

            return $data;
        })->values()->toArray();
    }

    public function save(): void
    {
        $this->authorizePermission(
            $this->isEditing
                ? Permission::QUOTATION_TRACKING_EDIT
                : Permission::QUOTATION_TRACKING_CREATE
        );

        $this->cleanMoneyFields($this->formData, $this->moneyFields);
        $this->recalculate();

        if ($this->usesLaborMonitoringTemplate()) {
            if (empty($this->detailItems)) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bảng báo giá Quan trắc môi trường lao động phải có ít nhất 1 chỉ tiêu.']);

                return;
            }

            $this->syncLaborMonitoringSummaryFromDetail();
        }

        $this->validate();

        if (empty($this->summaryItems)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bảng 01 (Tổng hợp) phải có ít nhất 1 dòng dịch vụ.']);

            return;
        }

        // Validate summary items
        foreach ($this->summaryItems as $i => $item) {
            if (empty(trim($item['description'] ?? ''))) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bảng 01 - Dòng '.($i + 1).': Vui lòng nhập nội dung dịch vụ.']);

                return;
            }
        }

        // Validate detail items if present
        foreach ($this->detailItems as $i => $item) {
            if (empty(trim($item['description'] ?? ''))) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bảng 02 - Dòng '.($i + 1).': Vui lòng nhập chỉ tiêu/nội dung.']);

                return;
            }
            if (empty(trim($item['group_name'] ?? ''))) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bảng 02 - Dòng '.($i + 1).': Vui lòng nhập/chọn nhóm.']);

                return;
            }
        }

        foreach ($this->matrixRows as $i => $row) {
            $hasData = trim((string) ($row['job_title'] ?? '')) !== ''
                || (int) ($row['employee_count'] ?? 0) > 0
                || (int) ($row['assessment_count'] ?? 0) > 0
                || (int) ($row['total'] ?? 0) > 0;

            if ($hasData && trim((string) ($row['job_title'] ?? '')) === '') {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Ma trận PLLĐ - Dòng '.($i + 1).': Vui lòng nhập chức danh công việc.']);

                return;
            }
        }

        $data = array_merge($this->formData, [
            'staff_id' => auth()->id(),
            'vat_rate' => $this->vatRate,
        ]);

        if ($this->isEditing) {
            $doc = QuotationDocument::findOrFail($this->selectedId);
            $this->authorizeDocumentOwner($doc);

            $doc->update($data);
            $doc->items()->delete();
            $doc->sections()->delete();
            $msg = 'Cập nhật báo giá thành công.';
        } else {
            $doc = QuotationDocument::create($data);
            $msg = 'Tạo báo giá thành công.';
        }

        // Save Summary Items
        $sortOrder = 0;
        foreach ($this->summaryItems as $item) {
            $doc->items()->create([
                'item_type' => 'summary',
                'sort_order' => $sortOrder++,
                'group_name' => null,
                'description' => $item['description'],
                'unit' => $item['unit'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $this->parseMoneyValue($item['unit_price'] ?? 0),
                'amount' => $item['amount'] ?? 0,
                'note' => $item['note'] ?? null,
            ]);
        }

        // Save Detail Items
        foreach ($this->detailItems as $item) {
            $doc->items()->create([
                'item_type' => 'detail',
                'sort_order' => $sortOrder++,
                'group_name' => $item['group_name'],
                'description' => $item['description'],
                'unit' => $item['unit'] ?? null,
                'quantity' => $this->parseIntegerQuantity($item['quantity'] ?? 1),
                'unit_price' => $this->parseMoneyValue($item['unit_price'] ?? 0),
                'amount' => $item['amount'] ?? 0,
                'note' => $item['note'] ?? null,
            ]);
        }

        $this->syncSectionsFromItems($doc);

        $tracking = $this->syncTrackingFromDocument($doc, false);
        $msg .= $tracking
            ? ' Đã tự động đồng bộ sang bảng theo dõi.'
            : ' Chưa đồng bộ sang bảng theo dõi do thiếu quyền.';

        $this->dispatch('close-qdoc-modal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => $msg]);
        $this->resetForm();
    }

    private function syncSectionsFromItems(QuotationDocument $doc): void
    {
        $doc->load('items');

        if ($doc->sections()->exists()) {
            $doc->sections()->delete();
        }

        $summarySection = $doc->sections()->create([
            'section_key' => 'summary',
            'section_type' => 'price_summary',
            'sort_order' => 10,
            'title' => 'Bảng 01. Tổng hợp dự toán chi phí thực hiện',
            'columns' => ['stt', 'description', 'unit', 'quantity', 'unit_price', 'amount'],
            'totals' => [
                'subtotal' => (int) $doc->subtotal,
                'discount' => (int) $doc->discount,
                'vat_rate' => (int) $doc->vat_rate,
                'vat_amount' => (int) $doc->vat_amount,
                'total' => (int) $doc->total,
            ],
        ]);

        foreach ($doc->items->where('item_type', 'summary')->values() as $index => $item) {
            $summarySection->rows()->create([
                'sort_order' => $index,
                'row_type' => 'item',
                'description' => $item->description,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'amount' => $item->amount,
                'note' => $item->note,
                'columns' => [
                    'legacy_item_id' => $item->id,
                    'item_type' => $item->item_type,
                ],
            ]);
        }

        $detailItems = $doc->items->where('item_type', 'detail')->values();
        if ($detailItems->isNotEmpty()) {
            $detailSection = $doc->sections()->create([
                'section_key' => 'detail',
                'section_type' => 'grouped_detail',
                'sort_order' => 20,
                'title' => 'Bảng 02. Chi tiết thực hiện',
                'columns' => ['stt', 'group_name', 'description', 'unit', 'quantity', 'unit_price', 'amount'],
                'totals' => [
                    'total' => (int) $detailItems->sum('amount'),
                ],
            ]);

            foreach ($detailItems as $index => $item) {
                $detailSection->rows()->create([
                    'sort_order' => $index,
                    'row_type' => 'item',
                    'group_name' => $item->group_name,
                    'description' => $item->description,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->amount,
                    'note' => $item->note,
                    'columns' => [
                        'legacy_item_id' => $item->id,
                        'item_type' => $item->item_type,
                    ],
                ]);
            }
        }

        $matrixRows = collect($this->matrixRows)
            ->map(function (array $row): array {
                $data = array_merge($this->defaultMatrixRow(), $row);
                $total = 0;
                foreach (array_keys($this->plldMetricColumns()) as $key) {
                    $data[$key] = (int) ($data[$key] ?? 0);
                    $total += $data[$key];
                }
                $data['employee_count'] = (int) ($data['employee_count'] ?? 0);
                $data['assessment_count'] = (int) ($data['assessment_count'] ?? 0);
                $data['total'] = $total;

                return $data;
            })
            ->filter(fn (array $row) => trim((string) ($row['job_title'] ?? '')) !== '')
            ->values();

        if ($matrixRows->isEmpty()) {
            return;
        }

        $matrixSection = $doc->sections()->create([
            'section_key' => 'plld_matrix',
            'section_type' => 'plld_job_matrix',
            'sort_order' => 30,
            'title' => 'Bảng 03. Ma trận chức danh và chỉ tiêu phân loại lao động',
            'columns' => array_merge(
                ['job_title', 'employee_count', 'assessment_count'],
                array_keys($this->plldMetricColumns()),
                ['total']
            ),
            'totals' => [
                'total' => (int) $matrixRows->sum('total'),
            ],
        ]);

        foreach ($matrixRows as $index => $row) {
            $matrixSection->rows()->create([
                'sort_order' => $index,
                'row_type' => 'matrix_row',
                'description' => $row['job_title'],
                'quantity' => $row['assessment_count'],
                'amount' => $row['total'],
                'columns' => $row,
            ]);
        }
    }

    private function syncLaborMonitoringSummaryFromDetail(): void
    {
        $customerName = trim($this->formData['customer_name'] ?? '');
        $serviceType = trim($this->formData['service_type'] ?? 'Quan trắc môi trường lao động');
        if ($serviceType === 'Khác' || $serviceType === '') {
            $serviceType = 'Quan trắc môi trường lao động';
        }

        $year = $this->formData['date'] ? date('Y', strtotime($this->formData['date'])) : date('Y');
        $location = trim($this->formData['work_location'] ?? '');

        $description = "Thực hiện {$serviceType} {$year}";
        if ($customerName !== '') {
            $description .= ' tại '.$customerName;
        }
        if ($location !== '') {
            $description .= ', '.$location;
        }

        $this->summaryItems = [[
            'description' => $description,
            'unit' => 'Hồ sơ',
            'quantity' => 1,
            'unit_price' => $this->detailTotal,
            'amount' => $this->detailTotal,
            'note' => '',
        ]];
    }

    public function delete(int $id): void
    {
        $this->authorizePermission(Permission::QUOTATION_TRACKING_DELETE);

        $doc = QuotationDocument::findOrFail($id);
        $this->authorizeDocumentOwner($doc);

        $doc->items()->delete();
        $doc->delete();

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa báo giá.']);
    }

    private function resetForm(): void
    {
        $nextNumber = $this->generateNextNumber();

        $this->formData = [
            'document_number' => $nextNumber,
            'date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(30)->format('Y-m-d'),
            'customer_name' => '',
            'customer_address' => '',
            'customer_phone' => '',
            'customer_contact' => '',
            'customer_email' => '',
            'customer_tax_code' => '',
            'service_type' => '',
            'template_key' => QuotationTemplateCatalog::DEFAULT_KEY,
            'work_location' => '',
            'subtotal' => 0,
            'vat_amount' => 0,
            'total' => 0,
            'discount' => 0,
            'notes' => '',
            'terms' => self::DEFAULT_TERMS,
        ];

        $this->summaryItems = [QuotationTemplateCatalog::defaultSummaryItem(QuotationTemplateCatalog::DEFAULT_KEY)];

        $this->detailItems = [];
        $this->matrixRows = [];
        $this->detailTotal = 0;
        $this->matrixTotal = 0;

        $this->vatRate = 8;
        $this->selectedId = null;
        $this->selectedDoc = null;
        $this->selectedCustomerId = '';
    }

    private function matchingCustomerId(?string $customerName): string
    {
        $customerName = trim((string) $customerName);
        if ($customerName === '') {
            return '';
        }

        $customer = Customer::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($customerName, 'UTF-8')])
            ->first();

        return $customer ? (string) $customer->id : '';
    }

    private function generateNextNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'BG-'.$year.'-';

        $lastDoc = QuotationDocument::where('document_number', 'like', $prefix.'%')
            ->orderByDesc('document_number')
            ->first();

        if ($lastDoc) {
            $lastNum = (int) str_replace($prefix, '', $lastDoc->document_number);
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix.str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $query = QuotationDocument::with(['staff', 'quotation'])
            ->withCount('items')
            ->when($this->isKinhDoanhUser(), fn ($q) => $q->where('staff_id', auth()->id()))
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('customer_name', 'like', '%'.$this->search.'%')
                        ->orWhere('document_number', 'like', '%'.$this->search.'%')
                        ->orWhere('service_type', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filter_staff, fn ($q) => $q->where('staff_id', $this->filter_staff))
            ->when($this->date_from, fn ($q) => $q->whereDate('date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('date', '<=', $this->date_to));

        $staffs = User::role([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value, Role::GIAM_DOC->value, Role::TU_VAN->value])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $serviceTypes = collect(ContractLegal::SERVICE_TYPES)
            ->merge(QuotationTemplateCatalog::serviceTypes())
            ->filter()
            ->unique()
            ->values();

        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'tax_code', 'address', 'representative']);

        return view('livewire.admin.quotation-documents.quotation-document-manager', [
            'documents' => $query->orderByDesc('date')->orderByDesc('id')->paginate(15),
            'staffs' => $staffs,
            'serviceTypes' => $serviceTypes,
            'templatePresets' => QuotationTemplateCatalog::all(),
            'groupOptions' => $this->groupOptions(),
            'customers' => $customers,
        ])->layout('admin.layouts.app', ['title' => 'Tạo Báo giá']);
    }
}
