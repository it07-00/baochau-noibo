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

class CommissionRequestFormTest extends TestCase
{
    use RefreshDatabase;

    private User $salesUser;
    private Customer $customer;
    private ContractWaste $contract;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear Spatie permission cache
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed roles & permissions
        foreach (RoleEnum::cases() as $roleEnum) {
            Role::findOrCreate($roleEnum->value);
        }

        $salesRole = Role::findByName(RoleEnum::KINH_DOANH->value);

        foreach (PermissionEnum::cases() as $perm) {
            Permission::findOrCreate($perm->value);
        }

        // Give KINH_DOANH necessary permissions
        $salesRole->syncPermissions([
            PermissionEnum::COMMISSIONS_VIEW->value,
            PermissionEnum::COMMISSIONS_CREATE->value,
            PermissionEnum::COMMISSIONS_EDIT->value,
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

        $this->customer = Customer::create(['name' => 'Khách hàng A']);
        
        $this->contract = ContractWaste::create([
            'customer_id' => $this->customer->id,
            'staff_id' => $this->salesUser->id,
            'department_id' => $dept->id,
            'shd_bc' => '2026-0001',
            'value' => 10000000,
            'revenue' => 10000000,
            'status' => 'Mới tạo',
        ]);
    }

    public function test_can_render_form(): void
    {
        $this->actingAs($this->salesUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestForm::class)
            ->assertStatus(200)
            ->assertSee('Thông tin Yêu cầu chi hoa hồng');
    }

    public function test_can_create_commission_request_with_vietqr(): void
    {
        $this->actingAs($this->salesUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestForm::class)
            ->set('contract_type', ContractWaste::class)
            ->set('contract_id', $this->contract->id)
            ->set('receiver_name', 'Nguyen Van A')
            ->set('receiver_phone', '0987654321')
            ->set('bank_code', 'VCB')
            ->set('bank_number', '1234567890')
            ->set('amount', '1.000.000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('commission_requests', [
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'NGUYEN VAN A',
            'bank_code' => 'VCB',
            'bank_number' => '1234567890',
            'amount' => 1000000,
        ]);
    }

    public function test_can_auto_fill_and_reuse_saved_account_details(): void
    {
        // First create a past request
        $pastRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Nguyen Van Saved',
            'receiver_phone' => '0999888777',
            'bank_code' => 'TCB',
            'bank_number' => '999999999',
            'amount' => 500000,
            'status' => 'Đã chi',
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestForm::class)
            ->set('selectedSavedAccountId', $pastRequest->id)
            ->assertSet('receiver_name', 'NGUYEN VAN SAVED')
            ->assertSet('receiver_phone', '0999888777')
            ->assertSet('bank_code', 'TCB')
            ->assertSet('bank_number', '999999999')
            ->set('contract_type', ContractWaste::class)
            ->set('contract_id', $this->contract->id)
            ->set('amount', '1.500.000')
            ->call('save')
            ->assertHasNoErrors();

        // Verify the database has the new request with the exact same details
        $this->assertDatabaseHas('commission_requests', [
            'user_id' => $this->salesUser->id,
            'receiver_name' => 'NGUYEN VAN SAVED',
            'bank_code' => 'TCB',
            'bank_number' => '999999999',
            'amount' => 1500000,
        ]);
    }

    public function test_validation_errors_in_vietnamese(): void
    {
        $this->actingAs($this->salesUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestForm::class)
            ->set('contract_type', '')
            ->set('contract_id', '')
            ->set('receiver_name', '')
            ->set('amount', '')
            ->call('save')
            ->assertHasErrors([
                'contract_type' => 'required',
                'contract_id' => 'required',
                'receiver_name' => 'required',
                'amount' => 'required',
            ])
            ->assertSee('Vui lòng chọn loại hợp đồng.')
            ->assertSee('Vui lòng chọn số hợp đồng.')
            ->assertSee('Vui lòng nhập tên người nhận.')
            ->assertSee('Vui lòng nhập số tiền.');
    }

    public function test_cannot_edit_already_paid_request(): void
    {
        $paidRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Tran Van Paid',
            'amount' => 1000000,
            'status' => 'Đã chi',
        ]);

        $this->actingAs($this->salesUser);

        // Mount should fail with a 403 status code
        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestForm::class, ['id' => $paidRequest->id])
            ->assertStatus(403);
    }

    public function test_owner_without_edit_permission_can_edit_pending_request(): void
    {
        $salesRole = Role::findByName(RoleEnum::KINH_DOANH->value);
        $salesRole->revokePermissionTo(PermissionEnum::COMMISSIONS_EDIT->value);

        $pendingRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Original Receiver',
            'amount' => 1000000,
            'status' => 'Chờ chi',
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestForm::class, ['id' => $pendingRequest->id])
            ->assertStatus(200)
            ->set('receiver_name', 'Updated Receiver Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('commission_requests', [
            'id' => $pendingRequest->id,
            'receiver_name' => 'UPDATED RECEIVER NAME',
        ]);
    }

    public function test_editing_rejected_request_clears_notes_and_resets_status(): void
    {
        $rejectedRequest = CommissionRequest::create([
            'user_id' => $this->salesUser->id,
            'contract_type' => ContractWaste::class,
            'contract_id' => $this->contract->id,
            'receiver_name' => 'Original Receiver',
            'amount' => 1000000,
            'status' => 'Từ chối',
            'notes' => 'Old notes content',
            'processed_at' => now(),
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestForm::class, ['id' => $rejectedRequest->id])
            ->assertStatus(200)
            ->assertSet('notes', '')
            ->set('notes', 'New submitted notes')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('commission_requests', [
            'id' => $rejectedRequest->id,
            'status' => 'Chờ chi',
            'notes' => 'New submitted notes',
            'processed_at' => null,
        ]);
    }

    public function test_banks_are_loaded_correctly(): void
    {
        $this->actingAs($this->salesUser);

        \Illuminate\Support\Facades\Http::fake([
            'api.vietqr.io/*' => \Illuminate\Support\Facades\Http::response([
                'code' => '00',
                'desc' => 'Success',
                'data' => [
                    [
                        'code' => 'VCB',
                        'shortName' => 'Vietcombank',
                    ],
                    [
                        'code' => 'TCB',
                        'shortName' => 'Techcombank',
                    ]
                ]
            ], 200)
        ]);

        \Illuminate\Support\Facades\Cache::forget('vietqr_banks_list');

        $component = Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestForm::class);

        $banks = $component->get('banks');

        $this->assertArrayHasKey('VCB', $banks);
        $this->assertArrayHasKey('TCB', $banks);
        $this->assertEquals('Vietcombank (VCB)', $banks['VCB']);
        $this->assertEquals('Techcombank (TCB)', $banks['TCB']);
    }

    public function test_notifications_are_sent_on_creation_and_rejection_resubmit(): void
    {
        $accountantRole = Role::findByName(RoleEnum::KE_TOAN->value);
        $accountant = User::factory()->create(['is_active' => true]);
        $accountant->assignRole($accountantRole);

        $this->actingAs($this->salesUser);

        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestForm::class)
            ->set('contract_type', ContractWaste::class)
            ->set('contract_id', $this->contract->id)
            ->set('receiver_name', 'Nguyen Van A')
            ->set('amount', '1.000.000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(1, $accountant->unreadNotifications()->count());
        $notification = $accountant->unreadNotifications()->first();
        $this->assertEquals('commission', $notification->data['contract_type']);
        $this->assertStringContainsString('gửi yêu cầu chi hoa hồng', $notification->data['message']);

        $request = CommissionRequest::latest()->first();
        
        $this->actingAs($accountant);
        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestManager::class)
            ->call('startReject', $request->id)
            ->set('rejectReason', 'Wrong name')
            ->call('confirmReject')
            ->assertHasNoErrors();

        $this->salesUser->refresh();
        $this->assertEquals(1, $this->salesUser->unreadNotifications()->count());
        $notification = $this->salesUser->unreadNotifications()->first();
        $this->assertEquals('commission', $notification->data['contract_type']);
        $this->assertStringContainsString('từ chối. Lý do: Wrong name', $notification->data['message']);

        $accountant->unreadNotifications()->update(['read_at' => now()]);

        $this->actingAs($this->salesUser);
        Livewire::test(\App\Livewire\Admin\Commissions\CommissionRequestForm::class, ['id' => $request->id])
            ->set('receiver_name', 'Nguyen Van B')
            ->call('save')
            ->assertHasNoErrors();

        $accountant->refresh();
        $this->assertEquals(1, $accountant->unreadNotifications()->count());
        $notification = $accountant->unreadNotifications()->first();
        $this->assertEquals('commission', $notification->data['contract_type']);
        $this->assertStringContainsString('gửi yêu cầu chi hoa hồng', $notification->data['message']);
    }
}
