<?php

namespace App\Livewire\Admin\Quotations;

use App\Actions\Quotations\UpsertQuotationAction;
use App\Enums\Permission;
use App\Enums\QuotationStatus;
use App\Enums\Role;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Customer;
use App\Services\Quotations\QuotationImportService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Livewire\Concerns\CleanMoneyInput;

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

    public array $availableFields = [];

    public $formData = [
        'date' => '',
        'quotation_number' => '',
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
        'status' => QuotationStatus::DANG_THEO_DOI->value,
        'original_value' => 0,   // GIÁ TRỊ GÓC
        'value_inc_vat' => 0,    // Giá trị chưa VAT
        'commission_value' => 0, // Hoa hồng KH
        'commission_tax' => 0,   // Thuế HH
        'total_value' => 0,      // Giá trị HĐ (có VAT)
        'notes' => '',
    ];

    protected $rules = [
        'formData.date' => 'required|date',
        'formData.quotation_number' => 'nullable|string|max:100',
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
            'formData.quotation_number.max' => 'Số báo giá không vượt quá 100 ký tự.',
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
            'formData.quotation_number' => 'số báo giá',
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
            auth()->user()->hasAnyRole([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value, Role::GIAM_DOC->value]),
            403,
            'Bạn không có quyền truy cập chức năng này.'
        );

        $this->formData['date'] = now()->format('Y-m-d');
        $this->formData['staff_id'] = auth()->id();
        $this->availableFields = app(QuotationImportService::class)->getAvailableFields();

        if ($this->isKinhDoanh()) {
            $this->filter_staff = (string) auth()->id();
        }
    }

    private function isKinhDoanh(): bool
    {
        return auth()->user()->hasRole(Role::KINH_DOANH->value);
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

        if (!$user->can($this->isEditing ? Permission::QUOTATION_TRACKING_EDIT->value : Permission::QUOTATION_TRACKING_CREATE->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền lưu báo giá này.']);
            return;
        }

        $existing = null;
        if ($this->isEditing) {
            $existing = Quotation::findOrFail($this->selectedId);
            if ($this->isKinhDoanh() && (int) $existing->staff_id !== (int) $user->id) {
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

        [$_, $msg] = app(UpsertQuotationAction::class)->execute($this->formData, $user, $existing);

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
            'status' => QuotationStatus::DANG_THEO_DOI->value,
            'original_value' => 0,
            'value_inc_vat' => 0,
            'commission_value' => 0,
            'commission_tax' => 0,
            'total_value' => 0,
            'notes' => '',
        ];
        $this->selectedId = null;
    }

    // ── IMPORT ────────────────────────────────────────────────────────────────────

    public function updatedImportFile()
    {
        $this->importErrors = [];
        $this->importSuccess = null;
        $this->importPreview = [];
        $this->importHeaders = [];
        $this->importColumnMap = [];

        if (! $this->importFile) {
            return;
        }

        $this->validate(['importFile' => 'file|mimes:xlsx,xls,csv|max:5120']);

        $result = app(QuotationImportService::class)->previewFile($this->importFile);

        $this->importErrors    = $result['errors'];
        $this->importHeaders   = $result['headers'];
        $this->importColumnMap = $result['columnMap'];
        $this->importPreview   = $result['preview'];
    }

    public function runImport()
    {
        abort_unless(auth()->user()->can(Permission::QUOTATION_TRACKING_CREATE->value), 403);

        $this->importErrors = [];
        $this->importSuccess = null;

        if (! $this->importFile) {
            $this->importErrors[] = 'Chưa chọn file.';
            return;
        }

        try {
            $result = app(QuotationImportService::class)->runImport($this->importColumnMap, $this->importFile);

            $this->importFile = null;
            $this->importPreview = [];
            $this->importHeaders = [];
            $this->importColumnMap = [];

            $messageParts = ["Import thành công {$result['imported']} dòng"];
            if ($result['skippedMissingCompany'] > 0) {
                $messageParts[] = "bỏ qua {$result['skippedMissingCompany']} dòng thiếu tên công ty";
            }
            if ($result['skippedDuplicates'] > 0) {
                $messageParts[] = "bỏ qua {$result['skippedDuplicates']} dòng trùng dữ liệu";
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
            'statuses' => QuotationStatus::values(),
            'availableFields' => $this->availableFields,
        ])->layout('admin.layouts.app', ['title' => 'Theo dõi Báo giá']);
    }
}
