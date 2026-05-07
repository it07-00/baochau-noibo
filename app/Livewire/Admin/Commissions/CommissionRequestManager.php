<?php

namespace App\Livewire\Admin\Commissions;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\CommissionRequest;
use App\Models\User;
use App\Notifications\CommissionRequestStatusUpdatedNotification;
use Livewire\Component;
use Livewire\WithPagination;

class CommissionRequestManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $contractTypeFilter = '';
    public $requestMonthFilter = '';
    public $requesterFilter = '';
    public $perPage = 10;
    public ?int $rejectingId = null;
    public string $rejectReason = '';

    protected $listeners = ['deleteConfirmed' => 'delete'];

    private function applyFilters($query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('receiver_name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('contract', function ($qc) {
                        $qc->where('shd_bc', 'like', '%' . $this->search . '%')
                            ->orWhereHas('customer', function ($qcust) {
                                $qcust->where('name', 'like', '%' . $this->search . '%');
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

    private function resetRejectState(): void
    {
        $this->rejectingId = null;
        $this->rejectReason = '';
        $this->resetErrorBag('rejectReason');
    }

    private function notifyRequesterStatusUpdate(CommissionRequest $request, string $status, ?string $reason = null): void
    {
        $requester = $request->user;
        if (!$requester) {
            return;
        }

        $contractLabel = (string) ($request->contract?->shd_bc ?: ('#' . $request->id));
        $processorName = (string) (auth()->user()?->name ?? 'Kế toán');

        $requester->notify(new CommissionRequestStatusUpdatedNotification(
            status: $status,
            processedByName: $processorName,
            contractLabel: $contractLabel,
            amount: (string) $request->amount,
            requestId: (int) $request->id,
            reason: $reason,
        ));
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
        abort_unless(auth()->check() && auth()->user()->can(Permission::COMMISSIONS_DELETE->value), 403);

        $request = CommissionRequest::findOrFail($id);
        $request->delete();
        $this->dispatch('swal:success', ['message' => 'Xóa yêu cầu thành công!']);
    }

    public function approve(int $id): void
    {
        $this->ensureAccountantApprovalAccess();

        $request = CommissionRequest::findOrFail($id);

        if ($request->status === 'Đã chi') {
            $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Yêu cầu này đã được duyệt chi trước đó.']);
            return;
        }

        $request->update([
            'status'       => 'Đã chi',
            'processed_at' => now(),
        ]);

        $this->notifyRequesterStatusUpdate($request, 'Đã chi');

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Kế toán đã duyệt chi yêu cầu thành công.']);
    }

    public function startReject(int $id): void
    {
        $this->ensureAccountantApprovalAccess();

        $request = CommissionRequest::findOrFail($id);

        if ($request->status === 'Từ chối') {
            $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Yêu cầu này đã được từ chối trước đó.']);
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

    public function confirmReject(): void
    {
        $this->ensureAccountantApprovalAccess();

        abort_unless($this->rejectingId !== null, 404);

        $this->validate([
            'rejectReason' => 'required|string|min:5|max:1000',
        ], [
            'rejectReason.required' => 'Vui lòng nhập lý do từ chối.',
            'rejectReason.min'      => 'Lý do từ chối cần tối thiểu 5 ký tự.',
            'rejectReason.max'      => 'Lý do từ chối không được vượt quá 1000 ký tự.',
        ]);

        $request = CommissionRequest::findOrFail($this->rejectingId);

        if ($request->status === 'Từ chối') {
            $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Yêu cầu này đã được từ chối trước đó.']);
            $this->cancelReject();
            return;
        }

        $reason = trim($this->rejectReason);
        $mergedNotes = trim(($request->notes ? rtrim($request->notes) . "\n\n" : '') . 'Lý do từ chối (kế toán): ' . $reason);

        $request->update([
            'status'       => 'Từ chối',
            'processed_at' => now(),
            'notes'        => $mergedNotes,
        ]);

        $this->notifyRequesterStatusUpdate($request, 'Từ chối', $reason);

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Kế toán đã từ chối yêu cầu chi hoa hồng.']);
        $this->cancelReject();
    }

    public function render()
    {
        $query = CommissionRequest::with(['contract.customer', 'contract.staff', 'user']);
        $this->applyFilters($query);

        $summaryQuery = CommissionRequest::query();
        $this->applyFilters($summaryQuery);

        $summary = [
            'total'    => (clone $summaryQuery)->count(),
            'pending'  => (clone $summaryQuery)->where('status', 'Chờ chi')->count(),
            'approved' => (clone $summaryQuery)->where('status', 'Đã chi')->count(),
            'rejected' => (clone $summaryQuery)->where('status', 'Từ chối')->count(),
            'amount'   => (clone $summaryQuery)->sum('amount'),
        ];

        $requesters = User::query()
            ->whereIn('id', CommissionRequest::query()->select('user_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name']);

        $requests = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.admin.commissions.commission-request-manager', [
            'requests'      => $requests,
            'contractTypes' => CommissionRequest::CONTRACT_TYPE_LABELS,
            'summary'       => $summary,
            'requesters'    => $requesters,
            'canApprove'    => auth()->check() && auth()->user()->hasRole(Role::KE_TOAN->value),
            'canEdit'       => auth()->check()
                && auth()->user()->can(Permission::COMMISSIONS_EDIT->value)
                && !auth()->user()->hasRole(Role::KE_TOAN->value),
            'canDelete'     => auth()->check() && auth()->user()->can(Permission::COMMISSIONS_DELETE->value),
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Yêu cầu chi hoa hồng']);
    }
}
