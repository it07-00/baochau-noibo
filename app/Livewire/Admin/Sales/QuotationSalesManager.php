<?php

namespace App\Livewire\Admin\Sales;

use App\Models\QuotationSales;
use App\Models\User;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class QuotationSalesManager extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_staff = '';
    public $filter_service = '';
    public $filter_province = '';
    public $filter_department = '';
    public $filter_source = '';
    public $date_from = '';
    public $date_to = '';
    public $follow_up_from = '';
    public $follow_up_to = '';
    
    // Form fields for create/edit
    public $isEditing = false;
    public $selectedId = null;
    public $quotation_number, $staff_id, $sales_month, $service, $info_source, $quotation_date, $follow_up_date;
    public $value_ext_vat = 0, $commission = 0, $sales_percentage = 0, $sales_amount = 0;
    public $company_name, $address, $province, $content, $customer_name, $customer_phone, $customer_email, $total_workers, $status, $notes;

    protected $queryString = ['search', 'filter_staff', 'filter_service', 'filter_province', 'filter_department', 'filter_source'];

    public function updatingSearch() { $this->resetPage(); }

    public function calculateSales()
    {
        $this->sales_amount = ($this->value_ext_vat * $this->sales_percentage) / 100;
    }

    public function render()
    {
        $query = QuotationSales::query()
            ->with(['staff', 'creator'])
            ->when($this->search, function($q) {
                $q->where('quotation_number', 'like', '%' . $this->search . '%')
                  ->orWhere('company_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filter_staff, fn($q) => $q->where('staff_id', $this->filter_staff))
            ->when($this->filter_service, fn($q) => $q->where('service', 'like', '%' . $this->filter_service . '%'))
            ->when($this->filter_province, fn($q) => $q->where('province', 'like', '%' . $this->filter_province . '%'))
            ->when($this->filter_source, fn($q) => $q->where('info_source', 'like', '%' . $this->filter_source . '%'))
            ->when($this->date_from, fn($q) => $q->whereDate('quotation_date', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('quotation_date', '<=', $this->date_to))
            ->orderBy('created_at', 'desc');

        return view('livewire.admin.sales.quotation-sales-manager', [
            'items' => $query->paginate(15),
            'staffs' => User::all(),
            'departments' => Department::all(),
            'provinces' => QuotationSales::distinct()->pluck('province')->filter()->values(),
            'services' => QuotationSales::distinct()->pluck('service')->filter()->values(),
            'sources' => QuotationSales::distinct()->pluck('info_source')->filter()->values(),
        ])->layout('admin.layouts.app');
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->dispatch('open-modal', 'quotation-modal');
    }

    public function resetForm()
    {
        $this->reset(['selectedId', 'quotation_number', 'staff_id', 'sales_month', 'service', 'info_source', 'quotation_date', 'follow_up_date', 'value_ext_vat', 'commission', 'sales_percentage', 'sales_amount', 'company_name', 'address', 'province', 'content', 'customer_name', 'customer_phone', 'customer_email', 'total_workers', 'status', 'notes']);
        $this->sales_month = now()->format('Y-m');
        $this->quotation_date = now()->format('Y-m-d');
        $this->staff_id = auth()->id();
    }

    public function save()
    {
        $this->validate([
            'quotation_number' => 'required',
            'staff_id' => 'required',
            'sales_month' => 'required',
            'company_name' => 'required',
        ]);

        $data = [
            'quotation_number' => $this->quotation_number,
            'staff_id' => $this->staff_id,
            'sales_month' => $this->sales_month . '-01',
            'service' => $this->service,
            'info_source' => $this->info_source,
            'quotation_date' => $this->quotation_date,
            'follow_up_date' => $this->follow_up_date,
            'value_ext_vat' => $this->value_ext_vat,
            'commission' => $this->commission,
            'sales_percentage' => $this->sales_percentage,
            'sales_amount' => $this->sales_amount,
            'company_name' => $this->company_name,
            'address' => $this->address,
            'province' => $this->province,
            'content' => $this->content,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'total_workers' => $this->total_workers ?: 0,
            'status' => $this->status,
            'notes' => $this->notes,
            'user_id' => auth()->id(),
        ];

        if ($this->selectedId) {
            QuotationSales::find($this->selectedId)->update($data);
            $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Cập nhật thành công!']]);
        } else {
            QuotationSales::create($data);
            $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Lưu thành công!']]);
        }

        $this->dispatch('close-modal', 'quotation-modal');
        $this->resetPage();
    }

    public function edit($id)
    {
        $item = QuotationSales::findOrFail($id);
        $this->selectedId = $id;
        $this->quotation_number = $item->quotation_number;
        $this->staff_id = $item->staff_id;
        $this->sales_month = $item->sales_month->format('Y-m');
        $this->service = $item->service;
        $this->info_source = $item->info_source;
        $this->quotation_date = $item->quotation_date->format('Y-m-d');
        $this->follow_up_date = $item->follow_up_date?->format('Y-m-d');
        $this->value_ext_vat = $item->value_ext_vat;
        $this->commission = $item->commission;
        $this->sales_percentage = $item->sales_percentage;
        $this->sales_amount = $item->sales_amount;
        $this->company_name = $item->company_name;
        $this->address = $item->address;
        $this->province = $item->province;
        $this->content = $item->content;
        $this->customer_name = $item->customer_name;
        $this->customer_phone = $item->customer_phone;
        $this->customer_email = $item->customer_email;
        $this->total_workers = $item->total_workers;
        $this->status = $item->status;
        $this->notes = $item->notes;
        
        $this->isEditing = true;
        $this->dispatch('open-modal', 'quotation-modal');
    }

    public function delete($id)
    {
        QuotationSales::find($id)->delete();
        $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Xóa thành công!']]);
    }
}
