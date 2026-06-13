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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommissionRequestBillTest extends TestCase
{
    use RefreshDatabase;

    private User $accountantUser;
    private User $directorUser;
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
        $directorRole = Role::findByName(RoleEnum::GIAM_DOC->value);

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

        $directorRole->syncPermissions([
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

        $this->directorUser = User::factory()->create([
            'is_active' => true,
            'department_id' => $dept->id,
        ]);
        $this->directorUser->assignRole($directorRole);

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

        Storage::fake('public');
    }

    public function test_total_payout_shows_correct_amount_for_paid_requests(): void
    {
        CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Paid Receiver',
            'amount' => 1000000,
            'status' => 'Đã chi',
        ]);

        CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Pending Receiver',
            'amount' => 2000000,
            'status' => 'Chờ chi',
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->assertViewHas('summary', function ($summary) {
                return $summary['total_payout'] == 1000000 
                    && $summary['total_pending_payout'] == 2000000
                    && $summary['amount'] == 3000000;
            });
    }

    public function test_accountant_can_upload_payment_bill(): void
    {
        $request = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Receiver A',
            'amount' => 1000000,
            'status' => 'Đã chi',
        ]);

        $this->actingAs($this->accountantUser);

        $file = UploadedFile::fake()->image('payment_receipt.jpg');

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->set('viewingRequestId', $request->id)
            ->set('billFile', $file)
            ->call('uploadBill')
            ->assertHasNoErrors()
            ->assertSet('billFile', null)
            ->assertDispatched('swal:toast', ['type' => 'success', 'message' => 'Tải lên hóa đơn thành công!']);

        $request->refresh();
        $this->assertNotNull($request->payment_bill_path);
        Storage::disk('public')->assertExists($request->payment_bill_path);
    }

    public function test_director_can_upload_payment_bill(): void
    {
        $request = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Receiver A',
            'amount' => 1000000,
            'status' => 'Đã chi',
        ]);

        $this->actingAs($this->directorUser);

        $file = UploadedFile::fake()->create('receipt.pdf', 500, 'application/pdf');

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->set('viewingRequestId', $request->id)
            ->set('billFile', $file)
            ->call('uploadBill')
            ->assertHasNoErrors()
            ->assertSet('billFile', null);

        $request->refresh();
        $this->assertNotNull($request->payment_bill_path);
        Storage::disk('public')->assertExists($request->payment_bill_path);
    }

    public function test_sales_user_cannot_upload_payment_bill(): void
    {
        $request = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Receiver A',
            'amount' => 1000000,
            'status' => 'Đã chi',
        ]);

        $this->actingAs($this->salesUser);

        $file = UploadedFile::fake()->image('receipt.png');

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->set('viewingRequestId', $request->id)
            ->set('billFile', $file)
            ->call('uploadBill')
            ->assertStatus(403);
    }

    public function test_accountant_can_delete_payment_bill(): void
    {
        $request = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Receiver A',
            'amount' => 1000000,
            'status' => 'Đã chi',
            'payment_bill_path' => 'commission_bills/receipt.jpg',
        ]);

        // Put fake file in storage
        Storage::disk('public')->put('commission_bills/receipt.jpg', 'fake content');

        $this->actingAs($this->accountantUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->set('viewingRequestId', $request->id)
            ->call('deleteBill')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast', ['type' => 'success', 'message' => 'Đã xóa hóa đơn thanh toán.']);

        $request->refresh();
        $this->assertNull($request->payment_bill_path);
        Storage::disk('public')->assertMissing('commission_bills/receipt.jpg');
    }

    public function test_accountant_can_open_and_close_upload_bill_modal(): void
    {
        $request = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Receiver A',
            'amount' => 1000000,
            'status' => 'Đã chi',
        ]);

        $this->actingAs($this->accountantUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->call('openUploadBillModal', $request->id)
            ->assertSet('uploadingBillRequestId', $request->id)
            ->assertDispatched('open-upload-bill-modal')
            ->call('closeUploadBillModal')
            ->assertSet('uploadingBillRequestId', null)
            ->assertDispatched('close-upload-bill-modal');
    }

    public function test_accountant_can_upload_payment_bill_via_quick_upload_modal(): void
    {
        $request = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Receiver A',
            'amount' => 1000000,
            'status' => 'Đã chi',
        ]);

        $this->actingAs($this->accountantUser);

        $file = UploadedFile::fake()->image('quick_receipt.jpg');

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->call('openUploadBillModal', $request->id)
            ->set('billFile', $file)
            ->call('uploadBill')
            ->assertHasNoErrors()
            ->assertSet('billFile', null)
            ->assertSet('uploadingBillRequestId', null)
            ->assertDispatched('close-upload-bill-modal')
            ->assertDispatched('swal:toast', ['type' => 'success', 'message' => 'Tải lên hóa đơn thành công!']);

        $request->refresh();
        $this->assertNotNull($request->payment_bill_path);
        Storage::disk('public')->assertExists($request->payment_bill_path);
    }
}
