<?php

namespace Tests\Feature\Livewire\Admin\Commissions;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\User;
use App\Models\CommissionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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

        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

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

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
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
            'status' => 'Chờ chi',
        ]);

        $this->actingAs($this->accountantUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
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
            'status' => 'Chờ chi',
        ]);

        $this->actingAs($this->salesUser);

        // Try to delete the paid request
        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->call('delete', $paidRequest->id)
            ->assertDispatched('swal:toast', [
                'type' => 'error',
                'message' => 'Không thể xóa yêu cầu đã được chi.',
            ]);

        $this->assertDatabaseHas('commission_requests', [
            'id' => $paidRequest->id,
            'deleted_at' => null,
        ]);

        // Try to delete the pending request
        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
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
            'status' => 'Chờ chi',
        ]);

        $ownRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'OWN USER',
            'amount' => 2000000,
            'status' => 'Chờ chi',
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->assertSee('OWN USER')
            ->assertDontSee('OTHER USER');

        // Verify trying to view other user's request aborts with 403
        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
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
            'status' => 'Chờ chi',
        ]);

        // 1. Accountant test
        $this->actingAs($this->accountantUser);
        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->assertSee('USER B REQUEST');

        // 2. Director test
        $directorUser = User::factory()->create(['is_active' => true]);
        $directorUser->assignRole(RoleEnum::GIAM_DOC->value);
        
        $this->actingAs($directorUser);
        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->assertSee('USER B REQUEST')
            ->call('viewRequest', $requestB->id)
            ->assertSet('viewingRequestId', $requestB->id);
    }
}
