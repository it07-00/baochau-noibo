<?php

namespace App\Livewire\Admin\Invoices;

use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\InvoiceBaoChau;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceBaoChauManager extends Component
{
    use WithPagination;

    public int $year;
    public string $filter_month = '';
    public string $filter_status = '';
    public int|string $filter_customer = '';
    public array $years = [];

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public array $form = [
        'contract_waste_id'   => '',
        'customer_id'         => '',
        'invoice_number'      => '',
        'issue_date'          => '',
        'due_date'            => '',
        'amount'              => '',
        'vat_percent'         => 10,
        'vat_amount'          => 0,
        'total_amount'        => 0,
        'status'              => 'unpaid',
        'paid_amount'         => 0,
        'paid_at'             => '',
        'service_description' => '',
        'notes'               => '',
    ];

    protected function rules(): array
    {
        return [
            'form.customer_id'  => 'required|exists:customers,id',
            'form.issue_date'   => 'nullable|date',
            'form.due_date'     => 'nullable|date',
            'form.amount'       => 'required|numeric|min:0',
            'form.vat_percent'  => 'required|integer|min:0|max:100',
            'form.status'       => 'required|string',
            'form.paid_amount'  => 'nullable|numeric|min:0',
        ];
    }

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year + 1, now()->year - 3);
    }

    public function updatedYear(): void          { $this->resetPage(); }
    public function updatedFilterMonth(): void   { $this->resetPage(); }
    public function updatedFilterStatus(): void  { $this->resetPage(); }
    public function updatedFilterCustomer(): void { $this->resetPage(); }

    public function updatedFormAmount(): void      { $this->recalcVat(); }
    public function updatedFormVatPercent(): void  { $this->recalcVat(); }

    private function recalcVat(): void
    {
        $amount  = (float) ($this->form['amount'] ?? 0);
        $vatPct  = (float) ($this->form['vat_percent'] ?? 10);
        $vatAmt  = round($amount * $vatPct / 100);
        $this->form['vat_amount']   = $vatAmt;
        $this->form['total_amount'] = $amount + $vatAmt;
    }

    public function openCreate(): void
    {
        $this->reset(['editingId']);
        $this->form = [
            'contract_waste_id'   => '',
            'customer_id'         => '',
            'invoice_number'      => '',
            'issue_date'          => now()->format('Y-m-d'),
            'due_date'            => '',
            'amount'              => '',
            'vat_percent'         => 10,
            'vat_amount'          => 0,
            'total_amount'        => 0,
            'status'              => 'unpaid',
            'paid_amount'         => 0,
            'paid_at'             => '',
            'service_description' => '',
            'notes'               => '',
        ];
        $this->showModal = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        $inv = InvoiceBaoChau::findOrFail($id);
        $this->editingId = $id;
        $this->form = [
            'contract_waste_id'   => $inv->contract_waste_id ?? '',
            'customer_id'         => $inv->customer_id,
            'invoice_number'      => $inv->invoice_number ?? '',
            'issue_date'          => $inv->issue_date?->format('Y-m-d') ?? '',
            'due_date'            => $inv->due_date?->format('Y-m-d') ?? '',
            'amount'              => (string) $inv->amount,
            'vat_percent'         => $inv->vat_percent,
            'vat_amount'          => (string) $inv->vat_amount,
            'total_amount'        => (string) $inv->total_amount,
            'status'              => $inv->status,
            'paid_amount'         => (string) $inv->paid_amount,
            'paid_at'             => $inv->paid_at?->format('Y-m-d') ?? '',
            'service_description' => $inv->service_description ?? '',
            'notes'               => $inv->notes ?? '',
        ];
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()->can($this->editingId ? 'invoices.edit' : 'invoices.create'),
            403
        );

        $this->validate();

        $data = [
            'contract_waste_id'   => $this->form['contract_waste_id'] ?: null,
            'customer_id'         => $this->form['customer_id'],
            'invoice_number'      => $this->form['invoice_number'] ?: null,
            'issue_date'          => $this->form['issue_date'] ?: null,
            'due_date'            => $this->form['due_date'] ?: null,
            'amount'              => (float) $this->form['amount'],
            'vat_percent'         => (int) $this->form['vat_percent'],
            'vat_amount'          => (float) ($this->form['vat_amount'] ?? 0),
            'total_amount'        => (float) ($this->form['total_amount'] ?? 0),
            'status'              => $this->form['status'],
            'paid_amount'         => (float) ($this->form['paid_amount'] ?? 0),
            'paid_at'             => $this->form['paid_at'] ?: null,
            'service_description' => $this->form['service_description'] ?: null,
            'notes'               => $this->form['notes'] ?: null,
        ];

        if ($this->editingId) {
            InvoiceBaoChau::findOrFail($this->editingId)->update($data);
        } else {
            $data['created_by'] = auth()->id();
            InvoiceBaoChau::create($data);
        }

        $this->showModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Đã lưu hóa đơn!']);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        abort_unless(auth()->user()->can('invoices.delete'), 403);
        InvoiceBaoChau::findOrFail($this->deletingId)->delete();
        $this->showDeleteModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Đã xóa hóa đơn!']);
    }

    public function render()
    {
        $query = InvoiceBaoChau::whereYear('issue_date', $this->year)
            ->when($this->filter_month, fn($q) => $q->whereMonth('issue_date', $this->filter_month))
            ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
            ->when($this->filter_customer, fn($q) => $q->where('customer_id', $this->filter_customer));

        $items = (clone $query)->with(['customer', 'contractWaste'])
            ->orderByDesc('issue_date')
            ->paginate(20);

        $summary = (clone $query)
            ->selectRaw('COUNT(*) as cnt,
                SUM(total_amount) as total,
                SUM(paid_amount) as paid,
                SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_cnt,
                SUM(CASE WHEN status IN ("unpaid","partial","overdue") THEN total_amount - paid_amount ELSE 0 END) as outstanding')
            ->first();

        $customers      = Customer::orderBy('name')->get();
        $contractWastes = ContractWaste::with('customer')->orderByDesc('signed_at')->get();
        $statuses       = InvoiceBaoChau::STATUSES;

        return view('livewire.admin.invoices.invoice-bao-chau-manager',
            compact('items', 'summary', 'customers', 'contractWastes', 'statuses'))
            ->layout('admin.layouts.app');
    }
}
