<?php

namespace App\Livewire\Admin\Quotations;

use App\Models\Quotation;
use App\Models\User;
use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationManager extends Component
{
    use WithPagination;

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

    public $formData = [
        'date' => '',
        'staff_id' => '',
        'company_name' => '',
        'address' => '',
        'industry' => '',
        'contact_person' => '',
        'work_description' => '',
        'status' => 'Đang theo dõi',
        'original_value' => 0,   // Giá chưa VAT
        'value_inc_vat' => 0,    // Giá có VAT
        'commission_value' => 0, // Tiền hoa hồng
        'commission_tax' => 0,   // Tiền thuế
        'total_value' => 0,      // Tổng cộng
        'notes' => '',
    ];

    protected $rules = [
        'formData.date' => 'required|date',
        'formData.staff_id' => 'required',
        'formData.company_name' => 'required',
        'formData.status' => 'required',
    ];

    public function mount()
    {
        $this->formData['date'] = now()->format('Y-m-d');
        $this->formData['staff_id'] = auth()->id();
    }

    public function updatedFormDataOriginalValue() { $this->calculateByExtVat(); }
    public function updatedFormDataCommissionValue() { $this->calculateTotal(); }
    public function updatedFormDataCommissionTax() { $this->calculateByVatAmount(); }
    public function updatedFormDataValueIncVat() { $this->calculateByIncVat(); }

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
            'waste' => 'app.contracts.waste.index',
            'consulting' => 'app.contracts.consulting.index',
            'project' => 'app.contracts.project.index',
            'commercial' => 'app.contracts.commercial.index',
            default => 'app.contracts.waste.index',
        };

        return redirect()->route($route, ['quotation_id' => $this->convertingQuotation->id]);
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            Quotation::find($this->selectedId)->update($this->formData);
            $msg = 'Cập nhật thành công';
        } else {
            Quotation::create($this->formData);
            $msg = 'Tạo mới thành công';
        }

        $this->dispatch('close-quotation-modal');
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $msg]);
        $this->resetForm();
    }

    public function delete($id)
    {
        Quotation::find($id)->delete();
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => 'Đã xóa báo giá']);
    }

    private function resetForm()
    {
        $this->formData = [
            'date' => now()->format('Y-m-d'),
            'staff_id' => auth()->id(),
            'company_name' => '',
            'address' => '',
            'industry' => '',
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
            ]
        ])->layout('admin.layouts.app', ['title' => 'Theo dõi Báo giá']);
    }
}
