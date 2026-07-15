<?php

namespace Tests\Feature\Livewire\Admin\Commissions;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Commissions\CommissionRequestManager;
use App\Models\CommissionRequest;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CommissionRequestManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $accountantUser;

    private User $salesUser;

    private Customer $customer;

    private ContractWaste $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $roleEnum) {
            Role::findOrCreate($roleEnum->value);
        }

        $salesRole = Role::findByName(RoleEnum::KINH_DOANH->value);
        $accountantRole = Role::findByName(RoleEnum::KE_TOAN->value);

        foreach (PermissionEnum::cases() as $perm) {
            Permission::findOrCreate($perm->value);
        }

        $salesRole->syncPermissions([
            PermissionEnum::COMMISSIONS_VIEW->value,
            PermissionEnum::COMMISSIONS_CREATE->value,
            PermissionEnum::COMMISSIONS_EDIT->value,
            PermissionEnum::COMMISSIONS_DELETE->value,
        ]);

        $accountantRole->syncPermissions([
            PermissionEnum::COMMISSIONS_VIEW->value,
        ]);

        $dept = Department::firstOrCreate(
            ['slug' => 'kinh-doanh'],
            ['name' => 'Phòng Kinh Doanh']
        );

        $this->salesUser = User::factory()->create([
            'is_active' => true,
            'department_id' => $dept->id,
        ]);
        $this->salesUser->assignRole($salesRole);

        $this->accountantUser = User::factory()->create([
            'is_active' => true,
            'department_id' => $dept->id,
        ]);
        $this->accountantUser->assignRole($accountantRole);

        $this->customer = Customer::create(['name' => 'Khách hàng C']);

        $this->contract = ContractWaste::create([
            'customer_id' => $this->customer->id,
            'staff_id' => $this->salesUser->id,
            'department_id' => $dept->id,
            'shd_bc' => '2026-0002',
            'value' => 5000000,
            'revenue' => 5000000,
            'status' => 'Mới tạo',
        ]);
    }

    public function test_can_render_manager_page(): void
    {
        $this->actingAs($this->salesUser);

        Livewire::test(CommissionRequestManager::class)
            ->assertStatus(200)
            ->assertSee('Quản lý Yêu cầu chi hoa hồng');
    }

    public function test_can_view_qr_details_modal(): void
    {
        $request = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Tran Van C',
            'bank_code' => 'VCB',
            'bank_number' => '987654321',
            'amount' => 1200000,
            'status' => 'Dự chi',
        ]);

        $this->actingAs($this->accountantUser);

        Livewire::test(CommissionRequestManager::class)
            ->call('viewRequest', $request->id)
            ->assertSet('viewingRequestId', $request->id)
            ->assertDispatched('open-view-modal')
            ->assertSee('TRAN VAN C')
            ->assertSee('987654321')
            ->assertSee('VCB')
            ->call('closeView')
            ->assertSet('viewingRequestId', null)
            ->assertDispatched('close-view-modal');
    }

    public function test_cannot_delete_already_paid_request(): void
    {
        $paidRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Tran Van Paid',
            'amount' => 1000000,
            'status' => 'Đã chi',
        ]);

        $pendingRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Tran Van Pending',
            'amount' => 1000000,
            'status' => 'Dự chi',
        ]);

        $this->actingAs($this->salesUser);

        // Try to delete the paid request
        Livewire::test(CommissionRequestManager::class)
            ->call('delete', $paidRequest->id)
            ->assertDispatched('swal:toast', [
                'type' => 'error',
                'message' => 'Không thể xóa yêu cầu đã được duyệt hoặc đã chi.',
            ]);

        $this->assertDatabaseHas('commission_requests', [
            'id' => $paidRequest->id,
            'deleted_at' => null,
        ]);

        // Try to delete the pending request
        Livewire::test(CommissionRequestManager::class)
            ->call('delete', $pendingRequest->id)
            ->assertDispatched('swal:success', [
                'message' => 'Xóa yêu cầu thành công!',
            ]);

        $this->assertSoftDeleted($pendingRequest);
    }

    public function test_sales_user_can_only_see_own_requests(): void
    {
        $anotherSalesUser = User::factory()->create([
            'is_active' => true,
        ]);
        $anotherSalesUser->assignRole(RoleEnum::KINH_DOANH->value);

        $otherRequest = CommissionRequest::create([
            'user_id' => $anotherSalesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'OTHER USER',
            'amount' => 1000000,
            'status' => 'Dự chi',
        ]);

        $ownRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'OWN USER',
            'amount' => 2000000,
            'status' => 'Dự chi',
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(CommissionRequestManager::class)
            ->assertSee('OWN USER')
            ->assertDontSee('OTHER USER');

        // Verify trying to view other user's request aborts with 403
        Livewire::test(CommissionRequestManager::class)
            ->call('viewRequest', $otherRequest->id)
            ->assertStatus(403);
    }

    public function test_accountant_and_director_can_see_all_requests(): void
    {
        $anotherSalesUser = User::factory()->create(['is_active' => true]);
        $anotherSalesUser->assignRole(RoleEnum::KINH_DOANH->value);

        $requestB = CommissionRequest::create([
            'user_id' => $anotherSalesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'USER B REQUEST',
            'amount' => 1000000,
            'status' => 'Dự chi',
        ]);

        // 1. Accountant test
        $this->actingAs($this->accountantUser);
        Livewire::test(CommissionRequestManager::class)
            ->assertSee('USER B REQUEST');

        // 2. Director test
        $directorUser = User::factory()->create(['is_active' => true]);
        $directorUser->assignRole(RoleEnum::GIAM_DOC->value);

        $this->actingAs($directorUser);
        Livewire::test(CommissionRequestManager::class)
            ->assertSee('USER B REQUEST')
            ->call('viewRequest', $requestB->id)
            ->assertSet('viewingRequestId', $requestB->id);
    }

    public function test_sales_manager_can_see_and_open_all_requests(): void
    {
        $salesManager = User::factory()->create(['is_active' => true]);
        $salesManager->assignRole(RoleEnum::TP_KINH_DOANH->value);

        $anotherSalesUser = User::factory()->create(['is_active' => true]);
        $anotherSalesUser->assignRole(RoleEnum::KINH_DOANH->value);

        $otherRequest = CommissionRequest::create([
            'user_id' => $anotherSalesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'REQUEST VISIBLE TO SALES MANAGER',
            'amount' => 1000000,
            'status' => 'Dự chi',
        ]);

        $this->actingAs($salesManager);

        Livewire::test(CommissionRequestManager::class)
            ->assertSee('REQUEST VISIBLE TO SALES MANAGER')
            ->call('viewRequest', $otherRequest->id)
            ->assertSet('viewingRequestId', $otherRequest->id)
            ->assertDispatched('open-view-modal');
    }

    public function test_accountant_approval_moves_estimate_to_approved_not_paid(): void
    {
        $request = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Receiver Waiting Payment',
            'amount' => 1000000,
            'status' => 'Dự chi',
        ]);

        $this->actingAs($this->accountantUser);

        Livewire::test(CommissionRequestManager::class)
            ->call('approve', $request->id)
            ->assertDispatched('swal:toast', [
                'type' => 'success',
                'message' => 'Kế toán đã duyệt chi yêu cầu thành công.',
            ]);

        $request->refresh();

        $this->assertSame('Đã duyệt', $request->status);
        $this->assertNull($request->payment_bill_path);
        $this->assertNotNull($request->processed_at);
    }

    public function test_sales_user_cannot_delete_approved_request(): void
    {
        $approvedRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Tran Van Approved',
            'amount' => 1000000,
            'status' => 'Đã duyệt',
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(CommissionRequestManager::class)
            ->call('delete', $approvedRequest->id)
            ->assertDispatched('swal:toast', [
                'type' => 'error',
                'message' => 'Không thể xóa yêu cầu đã được duyệt hoặc đã chi.',
            ]);

        $this->assertDatabaseHas('commission_requests', [
            'id' => $approvedRequest->id,
            'deleted_at' => null,
        ]);
    }

    public function test_accountant_can_delete_approved_and_paid_requests(): void
    {
        // Ensure accountant role has delete permission in this test
        $accountantRole = Role::findByName(RoleEnum::KE_TOAN->value);
        $accountantRole->givePermissionTo(PermissionEnum::COMMISSIONS_DELETE->value);

        $approvedRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Tran Van Approved',
            'amount' => 1000000,
            'status' => 'Đã duyệt',
        ]);

        $paidRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Tran Van Paid',
            'amount' => 1000000,
            'status' => 'Đã chi',
        ]);

        $this->actingAs($this->accountantUser);

        // Delete approved request
        Livewire::test(CommissionRequestManager::class)
            ->call('delete', $approvedRequest->id)
            ->assertDispatched('swal:success', [
                'message' => 'Xóa yêu cầu thành công!',
            ]);

        $this->assertSoftDeleted($approvedRequest);

        // Delete paid request
        Livewire::test(CommissionRequestManager::class)
            ->call('delete', $paidRequest->id)
            ->assertDispatched('swal:success', [
                'message' => 'Xóa yêu cầu thành công!',
            ]);

        $this->assertSoftDeleted($paidRequest);
    }
}
