<?php

namespace Tests\Feature\Livewire\Admin\Contracts;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\ContractAssignment;
use App\Models\ContractLegal;
use App\Models\ContractWorkflowStep;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractConsultingManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $techUser;
    private Department $dept;
    private Customer $customer;
    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        // Create department
        $this->dept = Department::firstOrCreate(
            ['slug' => 'kinh-doanh'],
            ['name' => 'Phòng Kinh Doanh']
        );

        // Clear Spatie permission cache
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed roles & permissions
        foreach (RoleEnum::cases() as $roleEnum) {
            Role::findOrCreate($roleEnum->value);
        }
        $adminRole = Role::findByName(RoleEnum::IT->value);
        $techRole = Role::findByName(RoleEnum::KY_THUAT->value);

        foreach (PermissionEnum::cases() as $perm) {
            Permission::findOrCreate($perm->value);
        }
        $adminRole->syncPermissions(Permission::all());

        // Assign consulting view to tech user
        $techRole->syncPermissions([
            PermissionEnum::CONTRACTS_CONSULTING_VIEW->value,
        ]);

        // Create users
        $this->adminUser = User::factory()->create([
            'is_active' => true,
            'department_id' => $this->dept->id,
        ]);
        $this->adminUser->assignRole($adminRole);

        $this->techUser = User::factory()->create([
            'is_active' => true,
        ]);
        $this->techUser->assignRole($techRole);

        $this->customer = Customer::create(['name' => 'Khách hàng Consulting']);
        $this->handler = Handler::create(['name' => 'Nhà thầu phụ Consulting']);
    }

    public function test_can_render_consulting_manager(): void
    {
        $contract = ContractLegal::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 30000000,
            'revenue' => 30000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractConsultingManager::class)
            ->assertStatus(200)
            ->assertSet('filter.hide_completed_workflow', false)
            ->assertDontSee('Chưa hoàn thành')
            ->assertSee($this->customer->name)
            ->assertSee('30,000,000');
    }

    public function test_consultant_hides_six_step_completed_contracts_by_default(): void
    {
        $consultingRole = Role::findByName(RoleEnum::TU_VAN->value);
        $consultingRole->syncPermissions([PermissionEnum::CONTRACTS_CONSULTING_VIEW->value]);

        $consultant = User::factory()->create(['is_active' => true]);
        $consultant->assignRole($consultingRole);

        $incompleteContract = ContractLegal::create([
            'customer_id' => Customer::create(['name' => 'Incomplete consulting contract'])->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 1000000,
        ]);
        $completedContract = ContractLegal::create([
            'customer_id' => Customer::create(['name' => 'Completed consulting contract'])->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 2000000,
        ]);

        foreach ([$incompleteContract, $completedContract] as $contract) {
            ContractAssignment::create([
                'assignable_type' => ContractLegal::class,
                'assignable_id' => $contract->id,
                'user_id' => $consultant->id,
                'assigned_by' => $this->adminUser->id,
            ]);
        }

        foreach (ContractWorkflowStep::STEP_KEYS as $stepName) {
            ContractWorkflowStep::create([
                'contract_type' => ContractLegal::class,
                'contract_id' => $completedContract->id,
                'user_id' => $consultant->id,
                'step_name' => $stepName,
                'action' => 'complete',
            ]);
        }

        $this->actingAs($consultant);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractConsultingManager::class)
            ->assertSet('filter.hide_completed_workflow', true)
            ->assertViewHas('docs', fn ($docs) => $docs->contains('id', $incompleteContract->id)
                && ! $docs->contains('id', $completedContract->id))
            ->set('filter.hide_completed_workflow', false)
            ->assertViewHas('docs', fn ($docs) => $docs->contains('id', $completedContract->id))
            ->call('resetFilters')
            ->assertSet('filter.hide_completed_workflow', true);
    }

    public function test_technical_staff_can_use_incomplete_workflow_filter(): void
    {
        $completedContract = ContractLegal::create([
            'customer_id' => Customer::create(['name' => 'Completed technical contract'])->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 2000000,
        ]);

        ContractAssignment::create([
            'assignable_type' => ContractLegal::class,
            'assignable_id' => $completedContract->id,
            'user_id' => $this->techUser->id,
            'assigned_by' => $this->adminUser->id,
        ]);

        foreach (ContractWorkflowStep::STEP_KEYS as $stepName) {
            ContractWorkflowStep::create([
                'contract_type' => ContractLegal::class,
                'contract_id' => $completedContract->id,
                'user_id' => $this->techUser->id,
                'step_name' => $stepName,
                'action' => 'complete',
            ]);
        }

        $this->actingAs($this->techUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractConsultingManager::class)
            ->assertSet('filter.hide_completed_workflow', true)
            ->assertSee('Chưa hoàn thành')
            ->assertViewHas('docs', fn ($docs) => ! $docs->contains('id', $completedContract->id))
            ->set('filter.hide_completed_workflow', false)
            ->assertViewHas('docs', fn ($docs) => $docs->contains('id', $completedContract->id));
    }

    public function test_can_crud_consulting_contract(): void
    {
        $this->actingAs($this->adminUser);

        // Create
        Livewire::test(\App\Livewire\Admin\Contracts\ContractConsultingManager::class)
            ->call('create')
            ->set('formData.customer_id', $this->customer->id)
            ->set('formData.handler_id', $this->handler->id)
            ->set('formData.department_id', $this->dept->id)
            ->set('formData.value', '40.000.000')
            ->call('save')
            ->assertHasNoErrors();

        $contract = ContractLegal::first();
        $this->assertNotNull($contract);
        $this->assertEquals(40000000, $contract->value);

        // Update
        Livewire::test(\App\Livewire\Admin\Contracts\ContractConsultingManager::class)
            ->call('edit', $contract->id)
            ->set('formData.value', '45.000.000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(45000000, $contract->refresh()->value);

        // Delete
        Livewire::test(\App\Livewire\Admin\Contracts\ContractConsultingManager::class)
            ->call('delete', $contract->id);

        $this->assertSoftDeleted('contract_consultings', ['id' => $contract->id]);
    }

    public function test_technical_staff_can_update_report_number(): void
    {
        $contract = ContractLegal::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 30000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        // Non-technical user cannot update report number
        $this->actingAs($this->adminUser);
        Livewire::test(\App\Livewire\Admin\Contracts\ContractConsultingManager::class)
            ->call('viewDetail', $contract->id)
            ->set('reportNumber', 'BC-12345')
            ->call('saveReportNumber')
            ->assertDispatched('swal:toast', [
                'type' => 'error',
                'message' => 'Chỉ nhân viên Kỹ thuật mới được cập nhật Báo cáo số.',
            ]);

        $this->assertNull($contract->refresh()->report_number);

        // Technical user CAN update report number
        $this->actingAs($this->techUser);
        Livewire::test(\App\Livewire\Admin\Contracts\ContractConsultingManager::class)
            ->call('viewDetail', $contract->id)
            ->set('reportNumber', 'BC-12345')
            ->call('saveReportNumber')
            ->assertDispatched('swal:toast', [
                'type' => 'success',
                'message' => 'Đã cập nhật Báo cáo số!',
            ]);

        $this->assertEquals('BC-12345', $contract->refresh()->report_number);
    }

    public function test_can_upload_and_delete_pdf_contract_files(): void
    {
        Storage::fake('public');

        $contract = ContractLegal::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 30000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        $file = UploadedFile::fake()->create('hopdong.pdf', 500, 'application/pdf');

        $component = Livewire::test(\App\Livewire\Admin\Contracts\ContractConsultingManager::class)
            ->call('viewDetail', $contract->id)
            ->set('newContractFiles', [$file])
            ->call('uploadContractFile')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast', [
                'type' => 'success',
                'message' => 'Đã lưu file PDF.',
            ]);

        $this->assertDatabaseHas('contract_milestone_files', [
            'contract_type' => ContractLegal::class,
            'contract_id' => $contract->id,
            'milestone' => 'contract_document',
            'original_name' => 'hopdong.pdf',
        ]);

        $dbFile = \App\Models\ContractMilestoneFile::first();
        Storage::disk('public')->assertExists($dbFile->file_path);

        // Test delete
        $component->call('deleteContractFile', $dbFile->id)
            ->assertDispatched('swal:toast', [
                'type' => 'success',
                'message' => 'Đã xóa file.',
            ]);

        $this->assertDatabaseMissing('contract_milestone_files', ['id' => $dbFile->id]);
        Storage::disk('public')->assertMissing($dbFile->file_path);
    }
}
