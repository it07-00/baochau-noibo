<?php

namespace App\Livewire\Admin\Hr;

use App\Models\EmployeeContract;
use App\Models\EmployeeDocument;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class HrProfileDetail extends Component
{
    use WithFileUploads;

    public User $user;
    public string $activeTab = 'info';

    // ── Personal info form ──
    public $employee_code, $id_card_number, $id_card_issued_date, $id_card_issued_place;
    public $hometown, $permanent_address, $temporary_address;
    public $tax_code, $social_insurance_number, $bank_account, $bank_name;
    public $emergency_contact_name, $emergency_contact_phone;
    public $education_level, $major;
    public $start_date, $end_date, $employment_status, $work_type, $hr_notes;
    public $phone, $gender, $date_of_birth, $address;

    // ── Contract form ──
    public bool $showContractModal = false;
    public ?int $editingContractId = null;
    public $contract_number, $contract_type, $contract_signed_date;
    public $contract_start_date, $contract_end_date, $contract_salary;
    public $contract_status = 'active', $contract_notes, $contract_file;

    // ── Document upload ──
    public bool $showDocumentModal = false;
    public $document_type = 'khac', $document_title, $document_files = [];
    public $document_issued_date, $document_expiry_date, $document_notes;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->fillPersonalInfo();
    }

    private function fillPersonalInfo(): void
    {
        $fields = [
            'employee_code', 'id_card_number', 'id_card_issued_date', 'id_card_issued_place',
            'hometown', 'permanent_address', 'temporary_address',
            'tax_code', 'social_insurance_number', 'bank_account', 'bank_name',
            'emergency_contact_name', 'emergency_contact_phone',
            'education_level', 'major', 'start_date', 'end_date',
            'employment_status', 'work_type', 'hr_notes',
            'phone', 'gender', 'date_of_birth', 'address',
        ];

        foreach ($fields as $field) {
            $value = $this->user->{$field};
            $this->{$field} = $value instanceof \Carbon\Carbon ? $value->format('Y-m-d') : $value;
        }
    }

    // ══════════════════════════════════════════════
    // PERSONAL INFO
    // ══════════════════════════════════════════════

    public function savePersonalInfo(): void
    {
        $this->authorize('edit', 'hr-profiles');

        $this->validate([
            'employee_code' => 'nullable|string|max:20|unique:users,employee_code,' . $this->user->id,
            'id_card_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:30',
            'employment_status' => 'required|in:thu_viec,chinh_thuc,thuc_tap,nghi_viec',
            'work_type' => 'required|in:full_time,part_time',
        ]);

        $this->user->update([
            'employee_code'          => $this->employee_code,
            'id_card_number'         => $this->id_card_number,
            'id_card_issued_date'    => $this->id_card_issued_date ?: null,
            'id_card_issued_place'   => $this->id_card_issued_place,
            'hometown'               => $this->hometown,
            'permanent_address'      => $this->permanent_address,
            'temporary_address'      => $this->temporary_address,
            'tax_code'               => $this->tax_code,
            'social_insurance_number'=> $this->social_insurance_number,
            'bank_account'           => $this->bank_account,
            'bank_name'              => $this->bank_name,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone'=> $this->emergency_contact_phone,
            'education_level'        => $this->education_level,
            'major'                  => $this->major,
            'start_date'             => $this->start_date ?: null,
            'end_date'               => $this->end_date ?: null,
            'employment_status'      => $this->employment_status,
            'work_type'              => $this->work_type,
            'hr_notes'               => $this->hr_notes,
            'phone'                  => $this->phone,
            'gender'                 => $this->gender,
            'date_of_birth'          => $this->date_of_birth ?: null,
            'address'                => $this->address,
        ]);

        $this->dispatch('swal:success', message: 'Cập nhật thông tin thành công!');
    }

    private function checkPermission(string $action, string $resource): void
    {
        if (!auth()->user()->can("{$resource}.{$action}")) {
            abort(403);
        }
    }

    // ══════════════════════════════════════════════
    // CONTRACTS
    // ══════════════════════════════════════════════

    public function openContractModal(?int $id = null): void
    {
        $this->resetContractForm();
        if ($id) {
            $contract = EmployeeContract::findOrFail($id);
            $this->editingContractId    = $contract->id;
            $this->contract_number      = $contract->contract_number;
            $this->contract_type        = $contract->contract_type;
            $this->contract_signed_date = $contract->signed_date?->format('Y-m-d');
            $this->contract_start_date  = $contract->start_date?->format('Y-m-d');
            $this->contract_end_date    = $contract->end_date?->format('Y-m-d');
            $this->contract_salary      = $contract->salary;
            $this->contract_status      = $contract->status;
            $this->contract_notes       = $contract->notes;
        }
        $this->showContractModal = true;
    }

    public function saveContract(): void
    {
        $this->checkPermission('edit', 'hr-profiles');

        $this->validate([
            'contract_type'        => 'required|in:' . implode(',', array_keys(EmployeeContract::CONTRACT_TYPES)),
            'contract_signed_date' => 'required|date',
            'contract_start_date'  => 'required|date',
            'contract_end_date'    => 'nullable|date|after_or_equal:contract_start_date',
            'contract_salary'      => 'nullable|numeric|min:0',
            'contract_file'        => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        $data = [
            'user_id'         => $this->user->id,
            'contract_number' => $this->contract_number,
            'contract_type'   => $this->contract_type,
            'signed_date'     => $this->contract_signed_date,
            'start_date'      => $this->contract_start_date,
            'end_date'        => $this->contract_end_date ?: null,
            'salary'          => $this->contract_salary ?: null,
            'status'          => $this->contract_status,
            'notes'           => $this->contract_notes,
        ];

        if ($this->contract_file) {
            $data['file_path'] = $this->contract_file->store(
                "hr-documents/{$this->user->id}/contracts", 'private'
            );
        }

        if ($this->editingContractId) {
            $contract = EmployeeContract::findOrFail($this->editingContractId);
            abort_unless($contract->user_id === $this->user->id, 403);
            $contract->update($data);
        } else {
            EmployeeContract::create($data);
        }

        $this->showContractModal = false;
        $this->resetContractForm();
        $this->dispatch('swal:success', message: $this->editingContractId ? 'Cập nhật hợp đồng thành công!' : 'Thêm hợp đồng thành công!');
    }

    public function deleteContract(int $id): void
    {
        $this->checkPermission('delete', 'hr-profiles');
        $contract = EmployeeContract::findOrFail($id);
        abort_unless($contract->user_id === $this->user->id, 403);

        if ($contract->file_path) {
            \Illuminate\Support\Facades\Storage::disk('private')->delete($contract->file_path);
        }

        $contract->delete();
        $this->dispatch('swal:success', message: 'Xóa hợp đồng thành công!');
    }

    private function resetContractForm(): void
    {
        $this->editingContractId = null;
        $this->contract_number = null;
        $this->contract_type = null;
        $this->contract_signed_date = null;
        $this->contract_start_date = null;
        $this->contract_end_date = null;
        $this->contract_salary = null;
        $this->contract_status = 'active';
        $this->contract_notes = null;
        $this->contract_file = null;
        $this->resetErrorBag();
    }

    // ══════════════════════════════════════════════
    // DOCUMENTS
    // ══════════════════════════════════════════════

    public function openDocumentModal(): void
    {
        $this->resetDocumentForm();
        $this->showDocumentModal = true;
    }

    public function saveDocuments(): void
    {
        $this->checkPermission('edit', 'hr-profiles');

        $this->validate([
            'document_type'  => 'required|in:' . implode(',', array_keys(EmployeeDocument::DOCUMENT_TYPES)),
            'document_files' => 'required|array|min:1',
            'document_files.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        foreach ($this->document_files as $file) {
            $path = $file->store("hr-documents/{$this->user->id}/documents", 'private');

            EmployeeDocument::create([
                'user_id'       => $this->user->id,
                'document_type' => $this->document_type,
                'title'         => $this->document_title ?: EmployeeDocument::DOCUMENT_TYPES[$this->document_type],
                'file_path'     => $path,
                'file_name'     => $file->getClientOriginalName(),
                'file_size'     => $file->getSize(),
                'issued_date'   => $this->document_issued_date ?: null,
                'expiry_date'   => $this->document_expiry_date ?: null,
                'notes'         => $this->document_notes,
            ]);
        }

        $this->showDocumentModal = false;
        $this->resetDocumentForm();
        $this->dispatch('swal:success', message: 'Tải lên giấy tờ thành công!');
    }

    public function deleteDocument(int $id): void
    {
        $this->checkPermission('delete', 'hr-profiles');
        $doc = EmployeeDocument::findOrFail($id);
        abort_unless($doc->user_id === $this->user->id, 403);

        \Illuminate\Support\Facades\Storage::disk('private')->delete($doc->file_path);
        $doc->delete();
        $this->dispatch('swal:success', message: 'Xóa giấy tờ thành công!');
    }

    public function downloadDocument(int $id)
    {
        $this->checkPermission('view', 'hr-profiles');
        $doc = EmployeeDocument::findOrFail($id);
        abort_unless($doc->user_id === $this->user->id, 403);
        return \Illuminate\Support\Facades\Storage::disk('private')->download($doc->file_path, $doc->file_name);
    }

    public function downloadContract(int $id)
    {
        $this->checkPermission('view', 'hr-profiles');
        $contract = EmployeeContract::findOrFail($id);
        abort_unless($contract->user_id === $this->user->id, 403);
        if (!$contract->file_path) {
            return;
        }
        return \Illuminate\Support\Facades\Storage::disk('private')->download($contract->file_path);
    }

    private function resetDocumentForm(): void
    {
        $this->document_type = 'khac';
        $this->document_title = null;
        $this->document_files = [];
        $this->document_issued_date = null;
        $this->document_expiry_date = null;
        $this->document_notes = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.hr.hr-profile-detail', [
            'contracts' => $this->user->employeeContracts()->latest('signed_date')->get(),
            'documents' => $this->user->employeeDocuments()->latest()->get(),
        ])->layout('admin.layouts.app');
    }
}
