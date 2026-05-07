<?php

namespace App\Livewire\Admin\Quotations;

use App\Enums\Permission;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Livewire\Concerns\CleanMoneyInput;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class QuotationManager extends Component
{
    use WithPagination, CleanMoneyInput, WithFileUploads;

    public $search = '';
    public $filter_staff = '';
    public $filter_status = '';
    public $date_from = '';
    public $date_to = '';
    public $sortDirection = 'desc';

    public $showModal = false;
    public $isEditing = false;
    public $isDuplicating = false;
    public $selectedId = null;
    public $selectedQuotation = null;
    public $convertingQuotation = null;

    // Import
    public $importFile = null;
    public $importPreview = [];
    public $importHeaders = [];
    public $importColumnMap = [];
    public $importErrors = [];
    public $importSuccess = null;

    private array $availableFields = [
        ''                  => '-- Bỏ qua --',
        'date'              => 'Ngày',
        'staff_name'        => 'Nhân viên sale',
        'source'            => 'Nguồn',
        'company_name'      => 'Công ty',
        'address'           => 'Địa chỉ XHĐ',
        'work_address'      => 'Địa chỉ làm',
        'province'          => 'Tỉnh thành',
        'industry'          => 'Ngành nghề',
        'service'           => 'Dịch vụ',
        'contact_person'    => 'Khách hàng',
        'work_description'  => 'Tình hình làm việc',
        'status'            => 'Tình hình',
        'original_value'    => 'Giá trị gốc',
        'value_inc_vat'     => 'Giá trị chưa VAT',
        'commission_value'  => 'Hoa hồng KH',
        'commission_tax'    => 'Thuế HH',
        'total_value'       => 'Giá trị HĐ (có VAT)',
        'notes'             => 'Ghi chú',
    ];

    public $formData = [
        'date' => '',
        'staff_id' => '',
        'source' => '',
        'company_name' => '',
        'address' => '',
        'work_address' => '',
        'province' => '',
        'industry' => '',
        'service' => '',
        'contact_person' => '',
        'work_description' => '',
        'status' => 'Đang theo dõi',
        'original_value' => 0,   // GIÁ TRỊ GÓC
        'value_inc_vat' => 0,    // Giá trị chưa VAT
        'commission_value' => 0, // Hoa hồng KH
        'commission_tax' => 0,   // Thuế HH
        'total_value' => 0,      // Giá trị HĐ (có VAT)
        'notes' => '',
    ];

    protected $rules = [
        'formData.date' => 'required|date',
        'formData.staff_id' => 'required|exists:users,id',
        'formData.company_name' => 'required|string|max:255',
        'formData.status' => 'required|string|max:100',
        'formData.source' => 'nullable|string|max:255',
        'formData.address' => 'nullable|string|max:500',
        'formData.work_address' => 'nullable|string|max:500',
        'formData.province' => 'nullable|string|max:100',
        'formData.industry' => 'nullable|string|max:255',
        'formData.service' => 'nullable|string|max:255',
        'formData.contact_person' => 'nullable|string|max:255',
        'formData.work_description' => 'nullable|string|max:2000',
        'formData.original_value' => 'nullable|numeric|min:0',
        'formData.value_inc_vat' => 'nullable|numeric|min:0',
        'formData.commission_value' => 'nullable|numeric|min:0',
        'formData.commission_tax' => 'nullable|numeric|min:0',
        'formData.total_value' => 'nullable|numeric|min:0',
        'formData.notes' => 'nullable|string|max:2000',
    ];

    protected function quotationValidationMessages(): array
    {
        return [
            'formData.date.required' => 'Vui lòng chọn ngày báo giá.',
            'formData.date.date' => 'Ngày báo giá không hợp lệ.',
            'formData.staff_id.required' => 'Vui lòng chọn nhân viên sale.',
            'formData.staff_id.exists' => 'Nhân viên sale không tồn tại.',
            'formData.company_name.required' => 'Vui lòng nhập tên công ty.',
            'formData.company_name.string' => 'Tên công ty không hợp lệ.',
            'formData.company_name.max' => 'Tên công ty không được vượt quá 255 ký tự.',
            'formData.status.required' => 'Vui lòng chọn tình hình.',
            'formData.status.string' => 'Tình hình không hợp lệ.',
            'formData.status.max' => 'Tình hình không được vượt quá 100 ký tự.',
            'formData.source.string' => 'Nguồn không hợp lệ.',
            'formData.source.max' => 'Nguồn không được vượt quá 255 ký tự.',
            'formData.address.string' => 'Địa chỉ xuất hóa đơn không hợp lệ.',
            'formData.address.max' => 'Địa chỉ xuất hóa đơn không được vượt quá 500 ký tự.',
            'formData.work_address.string' => 'Địa chỉ làm việc không hợp lệ.',
            'formData.work_address.max' => 'Địa chỉ làm việc không được vượt quá 500 ký tự.',
            'formData.province.string' => 'Tỉnh thành không hợp lệ.',
            'formData.province.max' => 'Tỉnh thành không được vượt quá 100 ký tự.',
            'formData.industry.string' => 'Ngành nghề không hợp lệ.',
            'formData.industry.max' => 'Ngành nghề không được vượt quá 255 ký tự.',
            'formData.service.string' => 'Dịch vụ không hợp lệ.',
            'formData.service.max' => 'Dịch vụ không được vượt quá 255 ký tự.',
            'formData.contact_person.string' => 'Khách hàng không hợp lệ.',
            'formData.contact_person.max' => 'Tên khách hàng không được vượt quá 255 ký tự.',
            'formData.work_description.string' => 'Nội dung làm việc không hợp lệ.',
            'formData.work_description.max' => 'Nội dung làm việc không được vượt quá 2000 ký tự.',
            'formData.original_value.numeric' => 'Giá trị gốc phải là số.',
            'formData.original_value.min' => 'Giá trị gốc không được âm.',
            'formData.value_inc_vat.numeric' => 'Giá trị chưa VAT phải là số.',
            'formData.value_inc_vat.min' => 'Giá trị chưa VAT không được âm.',
            'formData.commission_value.numeric' => 'Hoa hồng khách hàng phải là số.',
            'formData.commission_value.min' => 'Hoa hồng khách hàng không được âm.',
            'formData.commission_tax.numeric' => 'Thuế hoa hồng phải là số.',
            'formData.commission_tax.min' => 'Thuế hoa hồng không được âm.',
            'formData.total_value.numeric' => 'Giá trị hợp đồng có VAT phải là số.',
            'formData.total_value.min' => 'Giá trị hợp đồng có VAT không được âm.',
            'formData.notes.string' => 'Ghi chú không hợp lệ.',
            'formData.notes.max' => 'Ghi chú không được vượt quá 2000 ký tự.',
        ];
    }

    protected function quotationValidationAttributes(): array
    {
        return [
            'formData.date' => 'ngày báo giá',
            'formData.staff_id' => 'nhân viên sale',
            'formData.company_name' => 'công ty',
            'formData.status' => 'tình hình',
            'formData.source' => 'nguồn',
            'formData.address' => 'địa chỉ xuất hóa đơn',
            'formData.work_address' => 'địa chỉ làm việc',
            'formData.province' => 'tỉnh thành',
            'formData.industry' => 'ngành nghề',
            'formData.service' => 'dịch vụ',
            'formData.contact_person' => 'khách hàng',
            'formData.work_description' => 'tình hình làm việc',
            'formData.original_value' => 'giá trị gốc',
            'formData.value_inc_vat' => 'giá trị chưa VAT',
            'formData.commission_value' => 'hoa hồng khách hàng',
            'formData.commission_tax' => 'thuế hoa hồng',
            'formData.total_value' => 'giá trị hợp đồng có VAT',
            'formData.notes' => 'ghi chú',
        ];
    }

    public function mount()
    {
        abort_unless(
            auth()->user()->hasAnyRole(['kinh-doanh', 'tp-kinh-doanh', 'giam-doc']),
            403,
            'Bạn không có quyền truy cập chức năng này.'
        );

        $this->formData['date'] = now()->format('Y-m-d');
        $this->formData['staff_id'] = auth()->id();

        if ($this->isKinhDoanh()) {
            $this->filter_staff = (string) auth()->id();
        }
    }

    private function isKinhDoanh(): bool
    {
        return auth()->user()->hasRole('kinh-doanh');
    }

    private function authorizeQuotationAccess(Quotation $quotation): void
    {
        if ($this->isKinhDoanh()) {
            abort_unless((int) $quotation->staff_id === (int) auth()->id(), 403);
        }
    }

    private array $moneyFields = ['original_value', 'value_inc_vat', 'commission_value', 'commission_tax', 'total_value'];

    public function updatedFormDataOriginalValue() { $this->recalculateTotals(); }
    public function updatedFormDataCommissionValue() { $this->recalculateTotals(); }
    public function updatedFormDataCommissionTax() { $this->recalculateTotals(); }

    public function updatedSortDirection($value): void
    {
        $this->sortDirection = $value === 'asc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filter_status = '';
        $this->date_from = '';
        $this->date_to = '';
        $this->sortDirection = 'desc';

        if ($this->isKinhDoanh()) {
            $this->filter_staff = (string) auth()->id();
        } else {
            $this->filter_staff = '';
        }

        $this->resetPage();
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

    public function recalculateTotals(): void
    {
        $preVatValue = $this->parseMoneyValue($this->formData['original_value'] ?? 0)
            + $this->parseMoneyValue($this->formData['commission_value'] ?? 0)
            + $this->parseMoneyValue($this->formData['commission_tax'] ?? 0);

        $this->formData['value_inc_vat'] = round($preVatValue);
        $this->formData['total_value'] = round($preVatValue * 1.08);
    }

    private function normalizeImportHeader(string $header): string
    {
        return Str::of($header)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();
    }

    private function parseImportedDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            $raw = trim((string) $value);
            $digits = preg_replace('/\D+/', '', $raw) ?? '';

            if (strlen($digits) === 8) {
                $ymd = $this->parseDateByFormatStrict('Ymd', $digits);
                if ($ymd) {
                    return $ymd->format('Y-m-d');
                }

                $dmy = $this->parseDateByFormatStrict('dmY', $digits);
                if ($dmy) {
                    return $dmy->format('Y-m-d');
                }

                $mdy = $this->parseDateByFormatStrict('mdY', $digits);
                if ($mdy) {
                    return $mdy->format('Y-m-d');
                }
            }

            $serial = (float) $value;
            if ($serial >= 1 && $serial <= 100000) {
                try {
                    return ExcelDate::excelToDateTimeObject($serial)->format('Y-m-d');
                } catch (\Throwable $e) {
                    // Fall through to string parsing below.
                }
            }
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $formats = [
            'd/m/Y',
            'j/n/Y',
            'd-m-Y',
            'j-n-Y',
            'd.m.Y',
            'j.n.Y',
            'm/d/Y',
            'n/j/Y',
            'm-d-Y',
            'n-j-Y',
            'm.d.Y',
            'n.j.Y',
            'Y-m-d',
            'Y/m/d',
            'Y.m.d',
            'd/m/y',
            'j/n/y',
            'd-m-y',
            'j-n-y',
            'd.m.y',
            'j.n.y',
            'm/d/y',
            'n/j/y',
            'm-d-y',
            'n-j-y',
            'm.d.y',
            'n.j.y',
            'd/m/Y H:i',
            'd/m/Y H:i:s',
            'd-m-Y H:i',
            'd-m-Y H:i:s',
            'm/d/Y H:i',
            'm/d/Y H:i:s',
            'n/j/Y H:i',
            'n/j/Y H:i:s',
            'Y-m-d H:i',
            'Y-m-d H:i:s',
            'Y/m/d H:i',
            'Y/m/d H:i:s',
        ];

        foreach ($formats as $format) {
            $parsed = $this->parseDateByFormatStrict($format, $raw);
            if ($parsed instanceof \DateTimeInterface) {
                return $parsed->format('Y-m-d');
            }
        }

        $timestamp = strtotime($raw);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    private function parseDateByFormatStrict(string $format, string $value): ?\DateTimeInterface
    {
        $parsed = \DateTime::createFromFormat('!' . $format, $value);
        if (!$parsed) {
            return null;
        }

        $errors = \DateTime::getLastErrors();
        if (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            return null;
        }

        return $parsed;
    }

    private function isDuplicateImportedQuotation(array $data): bool
    {
        $companyName = trim((string) ($data['company_name'] ?? ''));
        $contactPerson = trim((string) ($data['contact_person'] ?? ''));
        $service = trim((string) ($data['service'] ?? ''));

        return Quotation::query()
            ->whereDate('date', $data['date'])
            ->where('staff_id', (int) ($data['staff_id'] ?? 0))
            ->whereRaw('TRIM(COALESCE(company_name, "")) = ?', [$companyName])
            ->whereRaw('TRIM(COALESCE(contact_person, "")) = ?', [$contactPerson])
            ->whereRaw('TRIM(COALESCE(service, "")) = ?', [$service])
            ->where('original_value', round((float) ($data['original_value'] ?? 0)))
            ->where('commission_value', round((float) ($data['commission_value'] ?? 0)))
            ->where('commission_tax', round((float) ($data['commission_tax'] ?? 0)))
            ->exists();
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->isDuplicating = false;
        $this->dispatch('open-quotation-modal');
    }

    public function edit($id)
    {
        $quotation = Quotation::findOrFail($id);
        $this->authorizeQuotationAccess($quotation);

        $this->selectedId = $id;
        $this->formData = $quotation->toArray();
        $this->formData['date'] = $quotation->date ? $quotation->date->format('Y-m-d') : '';
        $this->recalculateTotals();
        $this->isEditing = true;
        $this->isDuplicating = false;
        $this->dispatch('open-quotation-modal');
    }

    public function duplicate($id)
    {
        $quotation = Quotation::findOrFail($id);
        $this->authorizeQuotationAccess($quotation);

        $this->resetForm();
        $this->formData = $quotation->toArray();
        $this->formData['date'] = now()->format('Y-m-d');
        $this->isEditing = false;
        $this->isDuplicating = true;
        $this->selectedId = null;
        unset($this->formData['id'], $this->formData['created_at'], $this->formData['updated_at'], $this->formData['deleted_at']);
        $this->recalculateTotals();
        $this->dispatch('open-quotation-modal');
    }

    public function viewDetail($id)
    {
        $quotation = Quotation::with('staff')->findOrFail($id);
        $this->authorizeQuotationAccess($quotation);

        $this->selectedQuotation = $quotation;
        $this->dispatch('open-detail-modal');
    }

    public function selectContractType($id)
    {
        $quotation = Quotation::findOrFail($id);
        $this->authorizeQuotationAccess($quotation);

        $this->convertingQuotation = $quotation;
        $this->dispatch('open-convert-modal');
    }

    public function convertTo($type)
    {
        $route = match($type) {
            'waste'          => 'app.contracts.waste.index',
            'consulting'     => 'app.contracts.consulting.index',
            'project'        => 'app.contracts.project.index',
            'commercial'     => 'app.contracts.commercial.index',
            'sustainability' => 'app.contracts.sustainability.index',
            'energy'         => 'app.contracts.energy.index',
            default          => 'app.contracts.waste.index',
        };

        return redirect()->route($route, ['quotation_id' => $this->convertingQuotation->id]);
    }

    public function save()
    {
        $user = auth()->user();

        if (!$user->can($this->isEditing ? 'quotation-tracking.edit' : 'quotation-tracking.create')) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền lưu báo giá này.']);
            return;
        }

        if ($this->isEditing) {
            $quotation = Quotation::findOrFail($this->selectedId);
            if ($this->isKinhDoanh() && (int) $quotation->staff_id !== (int) $user->id) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn chỉ được cập nhật báo giá do bạn phụ trách.']);
                return;
            }
        }

        $this->cleanMoneyFields($this->formData, $this->moneyFields);
        $this->recalculateTotals();

        try {
            $this->validate($this->rules, $this->quotationValidationMessages(), $this->quotationValidationAttributes());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            if ($firstError) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => $firstError]);
            }
            throw $e;
        }

        if ($this->isEditing) {
            $quotation = Quotation::findOrFail($this->selectedId);

            $quotation->update($this->formData);
            $msg = 'Cập nhật thành công';
        } else {
            if ($this->isKinhDoanh()) {
                $this->formData['staff_id'] = auth()->id();
            }

            Quotation::create($this->formData);
            $msg = 'Tạo mới thành công';
        }

        $this->dispatch('close-quotation-modal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => $msg]);
        $this->resetForm();
    }

    public function delete($id)
    {
        abort_unless(auth()->user()->can(Permission::QUOTATION_TRACKING_DELETE->value), 403);

        $quotation = Quotation::findOrFail($id);
        $this->authorizeQuotationAccess($quotation);

        $quotation->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa báo giá']);
    }

    private function resetForm()
    {
        $this->formData = [
            'date' => now()->format('Y-m-d'),
            'staff_id' => auth()->id(),
            'source' => '',
            'company_name' => '',
            'address' => '',
            'work_address' => '',
            'province' => '',
            'industry' => '',
            'service' => '',
            'contact_person' => '',
            'work_description' => '',
            'status' => 'Đang theo dõi',
            'original_value' => 0,
            'value_inc_vat' => 0,
            'commission_value' => 0,
            'commission_tax' => 0,
            'total_value' => 0,
            'notes' => '',
        ];
        $this->selectedId = null;
    }

    // ── IMPORT ──────────────────────────────────────────────────────────────

    private array $headerMap = [
        'ngày'                      => 'date',
        'ngay'                      => 'date',
        'ngày báo giá'              => 'date',
        'ngay bao gia'              => 'date',
        'ngày tạo'                  => 'date',
        'ngay tao'                  => 'date',
        'date'                      => 'date',
        'quotation date'            => 'date',
        'nhân viên'                 => 'staff_name',
        'nhan vien'                 => 'staff_name',
        'nhân viên sale'            => 'staff_name',
        'sale'                      => 'staff_name',
        'nguồn'                     => 'source',
        'nguon'                     => 'source',
        'công ty'                   => 'company_name',
        'cong ty'                   => 'company_name',
        'tên công ty'               => 'company_name',
        'ten cong ty'               => 'company_name',
        'địa chỉ xhđ'              => 'address',
        'địa chỉ xuất hóa đơn'     => 'address',
        'dia chi xhd'               => 'address',
        'địa chỉ làm'              => 'work_address',
        'dia chi lam'               => 'work_address',
        'địa chỉ làm việc'         => 'work_address',
        'tỉnh thành'               => 'province',
        'tinh thanh'                => 'province',
        'tỉnh/thành'               => 'province',
        'ngành nghề'               => 'industry',
        'nganh nghe'                => 'industry',
        'ngành'                    => 'industry',
        'dịch vụ'                  => 'service',
        'dich vu'                   => 'service',
        'khách hàng'               => 'contact_person',
        'khach hang'                => 'contact_person',
        'người liên hệ'            => 'contact_person',
        'nguoi lien he'             => 'contact_person',
        'tình hình làm việc'       => 'work_description',
        'tinh hinh lam viec'        => 'work_description',
        'nội dung công việc'       => 'work_description',
        'noi dung'                  => 'work_description',
        'tình hình'                => 'status',
        'tinh hinh'                 => 'status',
        'tình trạng'               => 'status',
        'tinh trang'                => 'status',
        'giá trị gốc'              => 'original_value',
        'gia tri goc'               => 'original_value',
        'giá chưa vat'             => 'value_inc_vat',
        'giá trị chưa vat'         => 'value_inc_vat',
        'gia tri chua vat'          => 'value_inc_vat',
        'giá có vat'               => 'value_inc_vat',
        'gia co vat'                => 'value_inc_vat',
        'hoa hồng kh'              => 'commission_value',
        'hoa hong kh'               => 'commission_value',
        'hoa hồng'                 => 'commission_value',
        'thuế hh'                  => 'commission_tax',
        'thue hh'                   => 'commission_tax',
        'tiền thuế'                => 'commission_tax',
        'giá trị hđ (chưa vat)'   => 'total_value',
        'giá trị hd (chua vat)'    => 'total_value',
        'giá trị hđ (có vat)'     => 'total_value',
        'giá trị hd (co vat)'      => 'total_value',
        'giá trị hđ có vat'       => 'total_value',
        'gia tri hd co vat'        => 'total_value',
        'giá trị hđ'               => 'total_value',
        'tổng tiền'                => 'total_value',
        'tong tien'                 => 'total_value',
        'ghi chú'                  => 'notes',
        'ghi chu'                   => 'notes',
    ];

    public function updatedImportFile()
    {
        $this->importErrors = [];
        $this->importSuccess = null;
        $this->importPreview = [];
        $this->importHeaders = [];
        $this->importColumnMap = [];

        if (!$this->importFile) return;

        $this->validate(['importFile' => 'file|mimes:xlsx,xls,csv|max:5120']);

        try {
            $path = $this->importFile->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);

            // First non-empty row = headers
            $headerRow = null;
            $dataRows = [];
            foreach ($rows as $i => $row) {
                $nonEmpty = array_filter($row, fn($v) => $v !== null && $v !== '');
                if (empty($nonEmpty)) continue;
                if ($headerRow === null) {
                    $headerRow = $row;
                } else {
                    $dataRows[] = $row;
                    if (count($dataRows) >= 5) break;
                }
            }

            if (!$headerRow) {
                $this->importErrors[] = 'File không có dữ liệu.';
                return;
            }

            $this->importHeaders = array_values(array_filter($headerRow, fn($v) => $v !== null && $v !== ''));
            $numCols = count($headerRow); // full column count incl. empty

            // Auto-map headers
            foreach ($headerRow as $colIdx => $header) {
                if ($header === null || $header === '') continue;
                $normalized = mb_strtolower(trim((string) $header));
                $normalizedAscii = $this->normalizeImportHeader((string) $header);
                $this->importColumnMap[$colIdx] = $this->headerMap[$normalized]
                    ?? $this->headerMap[$normalizedAscii]
                    ?? '';
            }

            // Preview rows
            foreach ($dataRows as $row) {
                $this->importPreview[] = array_slice($row, 0, $numCols);
            }
        } catch (\Throwable $e) {
            $this->importErrors[] = 'Không thể đọc file: ' . $e->getMessage();
        }
    }

    public function runImport()
    {
        abort_unless(auth()->user()->can(Permission::QUOTATION_TRACKING_CREATE->value), 403);

        $this->importErrors = [];
        $this->importSuccess = null;

        if (!$this->importFile) {
            $this->importErrors[] = 'Chưa chọn file.';
            return;
        }

        try {
            $path = $this->importFile->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);

            // Find header row index
            $headerRowIdx = null;
            foreach ($rows as $i => $row) {
                $nonEmpty = array_filter($row, fn($v) => $v !== null && $v !== '');
                if (!empty($nonEmpty)) { $headerRowIdx = $i; break; }
            }

            if ($headerRowIdx === null) {
                $this->importErrors[] = 'File trống.';
                return;
            }

            $staffLookup = User::pluck('id', 'name')->toArray();
            $imported = 0;
            $skippedMissingCompany = 0;
            $skippedDuplicates = 0;

            \Illuminate\Support\Facades\DB::transaction(function () use ($rows, $headerRowIdx, $staffLookup, &$imported, &$skippedMissingCompany, &$skippedDuplicates) {
            foreach ($rows as $i => $row) {
                if ($i <= $headerRowIdx) continue;
                $nonEmpty = array_filter($row, fn($v) => $v !== null && $v !== '');
                if (empty($nonEmpty)) continue;

                $data = [
                    'status' => 'Đang theo dõi',
                    'staff_id' => auth()->id(),
                    'source' => null,
                    'service' => null,
                    'work_address' => null,
                    'original_value' => 0,
                    'value_inc_vat' => 0,
                    'commission_tax' => 0,
                    'commission_value' => 0,
                    'total_value' => 0,
                ];

                foreach ($this->importColumnMap as $colIdx => $field) {
                    if ($field === '' || !isset($row[$colIdx])) continue;
                    $val = $row[$colIdx];

                    if ($field === 'date') {
                        $data['date'] = $this->parseImportedDate($val);
                    } elseif ($field === 'staff_name') {
                        $staffName = trim((string)$val);
                        $data['staff_id'] = $staffLookup[$staffName] ?? auth()->id();
                    } elseif (in_array($field, ['original_value', 'value_inc_vat', 'commission_tax', 'commission_value', 'total_value'])) {
                        $data[$field] = (float) str_replace([',', '.', ' '], ['', '', ''], (string)$val);
                    } else {
                        $cleanValue = $val !== null ? trim((string) $val) : null;
                        $data[$field] = $cleanValue === '' ? null : $cleanValue;
                    }
                }

                if (empty($data['company_name'])) { $skippedMissingCompany++; continue; }
                if (empty($data['date'])) $data['date'] = now()->format('Y-m-d');

                $originalValue = round((float) ($data['original_value'] ?? 0));
                $commissionValue = round((float) ($data['commission_value'] ?? 0));
                $commissionTax = round((float) ($data['commission_tax'] ?? 0));
                $preVatValue = round($originalValue + $commissionValue + $commissionTax);

                $data['original_value'] = $originalValue;
                $data['commission_value'] = $commissionValue;
                $data['commission_tax'] = $commissionTax;
                $data['value_inc_vat'] = round((float) ($data['value_inc_vat'] ?? 0));
                $data['total_value'] = round((float) ($data['total_value'] ?? 0));

                if ($data['value_inc_vat'] <= 0 && $preVatValue > 0) {
                    $data['value_inc_vat'] = $preVatValue;
                }
                if ($data['total_value'] <= 0 && $preVatValue > 0) {
                    $data['total_value'] = round($preVatValue * 1.08);
                }

                if ($this->isDuplicateImportedQuotation($data)) {
                    $skippedDuplicates++;
                    continue;
                }

                Quotation::create($data);
                $imported++;
            }
            });

            $this->importFile = null;
            $this->importPreview = [];
            $this->importHeaders = [];
            $this->importColumnMap = [];

            $messageParts = ["Import thành công {$imported} dòng"];
            if ($skippedMissingCompany > 0) {
                $messageParts[] = "bỏ qua {$skippedMissingCompany} dòng thiếu tên công ty";
            }
            if ($skippedDuplicates > 0) {
                $messageParts[] = "bỏ qua {$skippedDuplicates} dòng trùng dữ liệu";
            }
            $this->importSuccess = implode(', ', $messageParts) . '.';

            $this->dispatch('close-import-modal');
            $this->dispatch('swal:toast', ['type' => 'success', 'message' => $this->importSuccess]);
        } catch (\Throwable $e) {
            $this->importErrors[] = 'Lỗi khi import: ' . $e->getMessage();
        }
    }

    public function resetImport()
    {
        $this->importFile = null;
        $this->importPreview = [];
        $this->importHeaders = [];
        $this->importColumnMap = [];
        $this->importErrors = [];
        $this->importSuccess = null;
    }

    public function render()
    {
        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $query = Quotation::with('staff')
            ->when($this->isKinhDoanh(), fn($q) => $q->where('staff_id', auth()->id()))
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('company_name', 'like', '%'.$this->search.'%')
                      ->orWhere('contact_person', 'like', '%'.$this->search.'%')
                      ->orWhere('industry', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filter_staff, fn($q) => $q->where('staff_id', $this->filter_staff))
            ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
            ->when($this->date_from, fn($q) => $q->whereDate('date', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('date', '<=', $this->date_to));

        return view('livewire.admin.quotations.quotation-manager', [
            'quotations' => $query->orderBy('date', $orderDirection)->orderBy('id', $orderDirection)->paginate(15),
            'staffs' => $this->isKinhDoanh()
                ? User::role(['kinh-doanh', 'tp-kinh-doanh'])->where('id', auth()->id())->orderBy('name')->get()
                : User::role(['kinh-doanh', 'tp-kinh-doanh'])->orderBy('name')->get(),
            'statuses' => [
                'hẹn báo giá thời gian sau',
                'Đang theo dõi',
                'Rớt báo giá',
                'Ký hợp đồng',
                'Tham khảo'
            ],
            'availableFields' => $this->availableFields,
        ])->layout('admin.layouts.app', ['title' => 'Theo dõi Báo giá']);
    }
}
