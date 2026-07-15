<?php

namespace App\Livewire\Admin\Commissions;

use App\Enums\CommissionRequestStatus;
use App\Enums\ContractType;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\CommissionRequest;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CommissionRequestManager extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $contractTypeFilter = '';

    public $requestMonthFilter = '';

    public $requesterFilter = '';

    public $perPage = 10;

    public ?int $rejectingId = null;

    public string $rejectReason = '';

    public ?int $viewingRequestId = null;

    public $billFile;

    public ?int $uploadingBillRequestId = null;

    protected $listeners = ['deleteConfirmed' => 'delete'];

    public function openUploadBillModal(int $id): void
    {
        $this->ensureAccountantOrDirectorAccess();

        $request = CommissionRequest::findOrFail($id);
        if ($request->status !== CommissionRequestStatus::DA_DUYET->value) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Chỉ có thể tải hóa đơn cho yêu cầu đã được duyệt.']);

            return;
        }

        $this->uploadingBillRequestId = $id;
        $this->billFile = null;
        $this->dispatch('open-upload-bill-modal');
    }

    public function closeUploadBillModal(): void
    {
        $this->uploadingBillRequestId = null;
        $this->billFile = null;
        $this->dispatch('close-upload-bill-modal');
    }

    private function applyFilters($query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('receiver_name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('contract', function ($qc) {
                        $qc->where('shd_bc', 'like', '%'.$this->search.'%')
                            ->orWhereHas('customer', function ($qcust) {
                                $qcust->where('name', 'like', '%'.$this->search.'%');
                            });
                    });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->contractTypeFilter) {
            $query->where('contract_type', $this->contractTypeFilter);
        }

        if ($this->requestMonthFilter && preg_match('/^\d{4}-\d{2}$/', $this->requestMonthFilter)) {
            [$year, $month] = explode('-', $this->requestMonthFilter);
            $query->whereYear('created_at', (int) $year)
                ->whereMonth('created_at', (int) $month);
        }

        if ($this->requesterFilter) {
            $query->where('user_id', (int) $this->requesterFilter);
        }
    }

    private function ensureAccountantApprovalAccess(): void
    {
        abort_unless(auth()->check() && auth()->user()->hasRole(Role::KE_TOAN->value), 403);
    }

    private function ensureAccountantOrDirectorAccess(): void
    {
        abort_unless(auth()->check() && (
            auth()->user()->hasRole(Role::KE_TOAN->value) ||
            auth()->user()->hasRole(Role::GIAM_DOC->value)
        ), 403);
    }

    private function canViewAllRequests(?User $user): bool
    {
        return $user && $user->hasAnyRole([
            Role::GIAM_DOC->value,
            Role::TP_KINH_DOANH->value,
            Role::KE_TOAN->value,
            Role::IT->value,
        ]);
    }

    private function resetRejectState(): void
    {
        $this->rejectingId = null;
        $this->rejectReason = '';
        $this->resetErrorBag('rejectReason');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingContractTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingRequestMonthFilter()
    {
        $this->resetPage();
    }

    public function updatingRequesterFilter()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $request = CommissionRequest::findOrFail($id);

        $isOwner = auth()->check() && $request->user_id === auth()->id();
        $hasDeletePermission = auth()->check() && auth()->user()->can(Permission::COMMISSIONS_DELETE->value);
        abort_unless($isOwner || $hasDeletePermission, 403);
        $isAccountant = auth()->check() && auth()->user()->hasRole(Role::KE_TOAN->value);
        if (in_array($request->status, [
            CommissionRequestStatus::DA_DUYET->value,
            CommissionRequestStatus::DA_CHI->value,
        ], true) && ! $isAccountant) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Không thể xóa yêu cầu đã được duyệt hoặc đã chi.']);

            return;
        }

        // Delete bill from storage if exists
        if ($request->payment_bill_path) {
            Storage::disk(config('filesystems.upload_disk', 'public'))->delete($request->payment_bill_path);
        }

        $request->delete();
        $this->dispatch('swal:success', ['message' => 'Xóa yêu cầu thành công!']);
    }

    public function approve(int $id): void
    {
        $this->ensureAccountantApprovalAccess();

        $request = CommissionRequest::findOrFail($id);

        if (in_array($request->status, [
            CommissionRequestStatus::DA_DUYET->value,
            CommissionRequestStatus::DA_CHI->value,
        ], true)) {
            $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Yêu cầu này đã được duyệt trước đó.']);

            return;
        }

        if ($request->status !== CommissionRequestStatus::DU_CHI->value) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Chỉ có thể duyệt yêu cầu đang ở trạng thái Dự chi.']);

            return;
        }

        app(CommissionService::class)->approve($request, auth()->user());

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Kế toán đã duyệt chi yêu cầu thành công.']);
    }

    public function startReject(int $id): void
    {
        $this->ensureAccountantApprovalAccess();

        $request = CommissionRequest::findOrFail($id);

        if ($request->status !== CommissionRequestStatus::DU_CHI->value) {
            $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Chỉ có thể từ chối yêu cầu đang ở trạng thái Dự chi.']);

            return;
        }

        $this->rejectingId = $request->id;
        $this->rejectReason = '';
        $this->dispatch('open-reject-modal');
    }

    public function cancelReject(): void
    {
        $this->resetRejectState();
        $this->dispatch('close-reject-modal');
    }

    public function viewRequest(int $id): void
    {
        $request = CommissionRequest::findOrFail($id);
        $user = auth()->user();
        $canViewAll = $this->canViewAllRequests($user);
        abort_unless($canViewAll || ($user && $request->user_id === $user->id), 403);

        $this->viewingRequestId = $id;
        $this->billFile = null;
        $this->dispatch('open-view-modal');
    }

    public function closeView(): void
    {
        $this->viewingRequestId = null;
        $this->billFile = null;
        $this->dispatch('close-view-modal');
    }

    public function confirmReject(): void
    {
        $this->ensureAccountantApprovalAccess();

        abort_unless($this->rejectingId !== null, 404);

        $this->validate([
            'rejectReason' => 'required|string|min:5|max:1000',
        ], [
            'rejectReason.required' => 'Vui lòng nhập lý do từ chối.',
            'rejectReason.min' => 'Lý do từ chối cần tối thiểu 5 ký tự.',
            'rejectReason.max' => 'Lý do từ chối không được vượt quá 1000 ký tự.',
        ]);

        $request = CommissionRequest::findOrFail($this->rejectingId);

        if ($request->status !== CommissionRequestStatus::DU_CHI->value) {
            $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Chỉ có thể từ chối yêu cầu đang ở trạng thái Dự chi.']);
            $this->cancelReject();

            return;
        }

        app(CommissionService::class)->reject($request, trim($this->rejectReason), auth()->user());

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Kế toán đã từ chối yêu cầu chi hoa hồng.']);
        $this->cancelReject();
    }

    public function uploadBill(): void
    {
        $this->ensureAccountantOrDirectorAccess();

        $this->validate([
            'billFile' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240', // max 10MB
        ], [
            'billFile.required' => 'Vui lòng chọn file hóa đơn.',
            'billFile.mimes' => 'Chỉ chấp nhận file ảnh (jpg, jpeg, png) hoặc PDF.',
            'billFile.max' => 'File không được vượt quá 10MB.',
        ]);

        $requestId = $this->uploadingBillRequestId ?? $this->viewingRequestId;
        abort_unless($requestId !== null, 404);

        $request = CommissionRequest::findOrFail($requestId);
        if ($request->status !== CommissionRequestStatus::DA_DUYET->value) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Chỉ có thể tải hóa đơn cho yêu cầu đã được duyệt.']);

            return;
        }

        $path = $this->billFile->store('commission_bills', config('filesystems.upload_disk', 'public'));

        if ($request->payment_bill_path) {
            Storage::disk(config('filesystems.upload_disk', 'public'))->delete($request->payment_bill_path);
        }

        $request->update([
            'payment_bill_path' => $path,
        ]);
        app(CommissionService::class)->markPaid($request, auth()->user());

        $this->billFile = null;

        if ($this->uploadingBillRequestId) {
            $this->closeUploadBillModal();
        }

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Tải lên hóa đơn thành công!']);
    }

    public function deleteBill(): void
    {
        $this->ensureAccountantOrDirectorAccess();

        $request = CommissionRequest::findOrFail($this->viewingRequestId);

        if ($request->payment_bill_path) {
            Storage::disk(config('filesystems.upload_disk', 'public'))->delete($request->payment_bill_path);
            $request->update([
                'payment_bill_path' => null,
                'status' => CommissionRequestStatus::DA_DUYET->value,
                'processed_at' => now(),
            ]);
            $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa hóa đơn; yêu cầu chuyển về trạng thái Đã duyệt.']);
        }
    }

    public function rejectionReason(?string $notes): string
    {
        if (empty($notes) || ! str_contains($notes, 'Lý do từ chối (kế toán):')) {
            return '';
        }

        return trim(Str::afterLast($notes, 'Lý do từ chối (kế toán):'));
    }

    public function rejectionReasonPreview(?string $notes, int $limit = 70): string
    {
        return Str::limit($this->rejectionReason($notes), $limit);
    }

    public function render()
    {
        $user = auth()->user();
        $isSpecialRole = $this->canViewAllRequests($user);

        $query = CommissionRequest::with(['contract.customer', 'contract.staff', 'user']);
        if (! $isSpecialRole && $user) {
            $query->where('user_id', $user->id);
        }
        $this->applyFilters($query);

        $summaryQuery = CommissionRequest::query();
        if (! $isSpecialRole && $user) {
            $summaryQuery->where('user_id', $user->id);
        }
        $this->applyFilters($summaryQuery);

        $statusCounts = (clone $summaryQuery)
            ->selectRaw('status, COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $summary = [
            'total' => $statusCounts->sum('cnt'),
            'estimated' => (int) ($statusCounts->get(CommissionRequestStatus::DU_CHI->value)?->cnt ?? 0),
            'pending' => (int) ($statusCounts->get(CommissionRequestStatus::DA_DUYET->value)?->cnt ?? 0),
            'paid' => (int) ($statusCounts->get(CommissionRequestStatus::DA_CHI->value)?->cnt ?? 0),
            'rejected' => (int) ($statusCounts->get(CommissionRequestStatus::TU_CHOI->value)?->cnt ?? 0),
            'amount' => (float) $statusCounts->sum('total_amount'),
            'total_estimated' => (float) ($statusCounts->get(CommissionRequestStatus::DU_CHI->value)?->total_amount ?? 0),
            'total_payout' => (float) ($statusCounts->get(CommissionRequestStatus::DA_CHI->value)?->total_amount ?? 0),
            'total_pending_payout' => (float) ($statusCounts->get(CommissionRequestStatus::DA_DUYET->value)?->total_amount ?? 0),
        ];

        $requestersQuery = User::query()->where('is_active', true);
        if (! $isSpecialRole && $user) {
            $requestersQuery->where('id', $user->id);
        } else {
            $requestersQuery->whereIn('id', CommissionRequest::query()->select('user_id')->distinct());
        }
        $requesters = $requestersQuery->orderBy('name')->get(['id', 'name']);

        $requests = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        $viewingRequest = null;
        if ($this->viewingRequestId) {
            $viewingQuery = CommissionRequest::with(['contract.customer', 'user']);
            if (! $isSpecialRole && $user) {
                $viewingQuery->where('user_id', $user->id);
            }
            $viewingRequest = $viewingQuery->find($this->viewingRequestId);
        }

        return view('livewire.admin.commissions.commission-request-manager', [
            'requests' => $requests,
            'contractTypes' => ContractType::labelMap(),
            'summary' => $summary,
            'requesters' => $requesters,
            'viewingRequest' => $viewingRequest,
            'canApprove' => auth()->check() && auth()->user()->hasRole(Role::KE_TOAN->value),
            'canEdit' => auth()->check()
                && auth()->user()->can(Permission::COMMISSIONS_EDIT->value)
                && ! auth()->user()->hasRole(Role::KE_TOAN->value),
            'canDelete' => auth()->check() && auth()->user()->can(Permission::COMMISSIONS_DELETE->value),
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Yêu cầu chi hoa hồng']);
    }
}
