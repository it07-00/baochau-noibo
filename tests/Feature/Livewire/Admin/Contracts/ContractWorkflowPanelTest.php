<?php

namespace Tests\Feature\Livewire\Admin\Contracts;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\ContractResearch;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use App\Models\ContractWorkflowStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractWorkflowPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $techUser;
    private User $unauthorizedUser;
    private ContractResearch $contract;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear Spatie permission cache
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed roles & permissions
        foreach (RoleEnum::cases() as $roleEnum) {
            Role::findOrCreate($roleEnum->value);
        }
        $techRole = Role::findByName(RoleEnum::KY_THUAT->value);

        // Create department
        $dept = Department::firstOrCreate(
            ['slug' => 'ky-thuat'],
            ['name' => 'Phòng Kỹ Thuật']
        );

        // Create tech user
        $this->techUser = User::factory()->create([
            'is_active' => true,
            'department_id' => $dept->id,
        ]);
        $this->techUser->assignRole($techRole);

        // Create unauthorized user (e.g. standard user without the specific role)
        $this->unauthorizedUser = User::factory()->create([
            'is_active' => true,
        ]);

        // Create defaults for testing
        $customer = Customer::create(['name' => 'Khách hàng A']);
        $handler = Handler::create(['name' => 'Nhà thầu phụ A']);

        $this->contract = ContractResearch::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $this->techUser->id,
            'department_id' => $dept->id,
            'value' => 50000000,
            'revenue' => 50000000,
            'status' => 'PTH đang kiểm tra',
        ]);
    }

    public function test_can_render_workflow_panel(): void
    {
        $this->actingAs($this->techUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractWorkflowPanel::class, [
            'contractType' => 'commercial',
            'contractId' => $this->contract->id,
        ])
        ->assertStatus(200)
        ->assertSee('Đã hoàn thành'); // Should show step navigation
    }

    public function test_unauthorized_user_cannot_open_steps(): void
    {
        $this->actingAs($this->unauthorizedUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractWorkflowPanel::class, [
            'contractType' => 'commercial',
            'contractId' => $this->contract->id,
        ])
        ->call('openStep', 'receiving')
        ->assertSet('activeStep', null); // Active step should remain null
    }

    public function test_authorized_user_can_open_and_complete_workflow_step_with_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->techUser);

        $file = UploadedFile::fake()->create('tailieu.pdf', 500, 'application/pdf');

        Livewire::test(\App\Livewire\Admin\Contracts\ContractWorkflowPanel::class, [
            'contractType' => 'commercial',
            'contractId' => $this->contract->id,
        ])
        ->call('openStep', 'survey')
        ->assertSet('activeStep', 'survey')
        ->set('uploadFiles', [$file])
        ->set('comment', 'Khảo sát thực tế hoàn thành')
        ->call('completeStep')
        ->assertHasNoErrors()
        ->assertSet('activeStep', null);

        // Verify status updated on contract
        $this->assertEquals('survey', $this->contract->refresh()->workflow_status);

        // Verify step record created in DB
        $this->assertDatabaseHas('contract_workflow_steps', [
            'contract_type' => ContractResearch::class,
            'contract_id' => $this->contract->id,
            'step_name' => 'survey',
            'comment' => 'Khảo sát thực tế hoàn thành',
        ]);
    }

    public function test_validation_rules_require_file_for_optional_roles_or_non_optional_steps(): void
    {
        $this->actingAs($this->techUser);

        // Complete step that requires file upload (e.g. signing / outline / etc.)
        Livewire::test(\App\Livewire\Admin\Contracts\ContractWorkflowPanel::class, [
            'contractType' => 'commercial',
            'contractId' => $this->contract->id,
        ])
        ->call('openStep', 'processing')
        // No uploadFiles set
        ->call('completeStep')
        ->assertHasErrors(['uploadFiles']);
    }
}
