<?php

namespace App\Livewire\Admin\Quotations;

use App\Models\Quotation;
use App\Models\User;
use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Livewire\Concerns\CleanMoneyInput;
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

    public $showModal = false;
    public $isEditing = false;
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
        'value_inc_vat'     => 'Giá có VAT',
        'commission_value'  => 'Hoa hồng KH',
        'commission_tax'    => 'Thuế HH',
        'total_value'       => 'Giá trị HĐ (chưa VAT)',
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
        'value_inc_vat' => 0,    // Giá có VAT
        'commission_value' => 0, // Hoa hồng KH
        'commission_tax' => 0,   // Thuế HH
        'total_value' => 0,      // Giá trị HĐ (chưa VAT)
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

    public function mount()
    {
        $this->formData['date'] = now()->format('Y-m-d');
        $this->formData['staff_id'] = auth()->id();
    }

    private array $moneyFields = ['original_value', 'value_inc_vat', 'commission_value', 'commission_tax', 'total_value'];

    public function updatedFormDataOriginalValue() { $this->cleanMoneyFields($this->formData, $this->moneyFields); $this->calculateByExtVat(); }
    public function updatedFormDataCommissionValue() { $this->cleanMoneyFields($this->formData, $this->moneyFields); $this->calculateTotal(); }
    public function updatedFormDataCommissionTax() { $this->cleanMoneyFields($this->formData, $this->moneyFields); $this->calculateByVatAmount(); }
    public function updatedFormDataValueIncVat() { $this->cleanMoneyFields($this->formData, $this->moneyFields); $this->calculateByIncVat(); }

    public function calculateByExtVat()
    {
        $val = (float)$this->formData['original_value'];
        $this->formData['commission_tax'] = $val * 0.1;
        $this->formData['value_inc_vat'] = $val + $this->formData['commission_tax'];
        $this->calculateTotal();
    }

    public function calculateByVatAmount()
    {
        $val = (float)$this->formData['original_value'];
        $this->formData['value_inc_vat'] = $val + (float)$this->formData['commission_tax'];
        $this->calculateTotal();
    }

    public function calculateByIncVat()
    {
        $inc = (float)$this->formData['value_inc_vat'];
        $this->formData['original_value'] = round($inc / 1.1);
        $this->formData['commission_tax'] = $inc - $this->formData['original_value'];
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->formData['total_value'] = (float)$this->formData['value_inc_vat'] -
                                         (float)$this->formData['commission_value'];
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->dispatch('open-quotation-modal');
    }

    public function edit($id)
    {
        $quotation = Quotation::findOrFail($id);
        $this->selectedId = $id;
        $this->formData = $quotation->toArray();
        $this->formData['date'] = $quotation->date ? $quotation->date->format('Y-m-d') : '';
        $this->isEditing = true;
        $this->dispatch('open-quotation-modal');
    }

    public function viewDetail($id)
    {
        $this->selectedQuotation = Quotation::with('staff')->findOrFail($id);
        $this->dispatch('open-detail-modal');
    }

    public function selectContractType($id)
    {
        $this->convertingQuotation = Quotation::findOrFail($id);
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
        abort_unless(
            auth()->user()->can($this->isEditing ? 'quotations.edit' : 'quotations.create'),
            403
        );

        $this->cleanMoneyFields($this->formData, $this->moneyFields);

        $this->validate();

        if ($this->isEditing) {
            Quotation::find($this->selectedId)->update($this->formData);
            $msg = 'Cập nhật thành công';
        } else {
            Quotation::create($this->formData);
            $msg = 'Tạo mới thành công';
        }

        $this->dispatch('close-quotation-modal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => $msg]);
        $this->resetForm();
    }

    public function delete($id)
    {
        abort_unless(auth()->user()->can('quotations.delete'), 403);

        Quotation::findOrFail($id)->delete();
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
        'giá chưa vat'             => 'original_value',
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
                $normalized = mb_strtolower(trim((string)$header));
                $this->importColumnMap[$colIdx] = $this->headerMap[$normalized] ?? '';
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
        abort_unless(auth()->user()->can('quotations.create'), 403);

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
            $skipped = 0;

            \Illuminate\Support\Facades\DB::transaction(function () use ($rows, $headerRowIdx, $staffLookup, &$imported, &$skipped) {
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
                        if (is_numeric($val)) {
                            $data['date'] = ExcelDate::excelToDateTimeObject($val)->format('Y-m-d');
                        } else {
                            $parsed = \DateTime::createFromFormat('d/m/Y', (string)$val)
                                   ?: \DateTime::createFromFormat('Y-m-d', (string)$val)
                                   ?: \DateTime::createFromFormat('d-m-Y', (string)$val);
                            $data['date'] = $parsed ? $parsed->format('Y-m-d') : null;
                        }
                    } elseif ($field === 'staff_name') {
                        $staffName = trim((string)$val);
                        $data['staff_id'] = $staffLookup[$staffName] ?? auth()->id();
                    } elseif (in_array($field, ['original_value', 'value_inc_vat', 'commission_tax', 'commission_value', 'total_value'])) {
                        $data[$field] = (float) str_replace([',', '.', ' '], ['', '', ''], (string)$val);
                    } else {
                        $data[$field] = $val !== null ? trim((string)$val) : null;
                    }
                }

                if (empty($data['company_name'])) { $skipped++; continue; }
                if (empty($data['date'])) $data['date'] = now()->format('Y-m-d');

                Quotation::create($data);
                $imported++;
            }
            });

            $this->importFile = null;
            $this->importPreview = [];
            $this->importHeaders = [];
            $this->importColumnMap = [];
            $this->importSuccess = "Import thành công {$imported} dòng" . ($skipped ? ", bỏ qua {$skipped} dòng trống/thiếu tên công ty." : '.');
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
        $query = Quotation::with('staff')
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
            'quotations' => $query->latest()->paginate(15),
            'staffs' => User::all(),
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
