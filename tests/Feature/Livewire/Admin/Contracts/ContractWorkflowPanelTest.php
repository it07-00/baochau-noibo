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
use Illuminate\Support\Facades\Notification;
use App\Notifications\ContractWorkflowUpdatedNotification;
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

    public function test_validation_rules_require_file_only_for_finished_step(): void
    {
        $this->actingAs($this->techUser);

        // Intermediate step (e.g. processing) does NOT require file upload
        Livewire::test(\App\Livewire\Admin\Contracts\ContractWorkflowPanel::class, [
            'contractType' => 'commercial',
            'contractId' => $this->contract->id,
        ])
        ->call('openStep', 'processing')
        ->call('completeStep')
        ->assertHasNoErrors();

        // Final step (finished) requires file upload
        Livewire::test(\App\Livewire\Admin\Contracts\ContractWorkflowPanel::class, [
            'contractType' => 'commercial',
            'contractId' => $this->contract->id,
        ])
        ->call('openStep', 'finished')
        ->call('completeStep')
        ->assertHasErrors(['uploadFiles']);
    }

    public function test_finishing_project_notifies_accounting_but_not_it(): void
    {
        Notification::fake();
        Storage::fake('public');

        $accountant = User::factory()->create(['is_active' => true]);
        $accountant->assignRole(Role::findByName(RoleEnum::KE_TOAN->value));

        $itUser = User::factory()->create(['is_active' => true]);
        $itUser->assignRole(Role::findByName(RoleEnum::IT->value));

        $this->actingAs($this->techUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractWorkflowPanel::class, [
            'contractType' => 'commercial',
            'contractId' => $this->contract->id,
        ])
            ->call('openStep', 'finished')
            ->set('uploadFiles', [UploadedFile::fake()->create('ho-so-hoan-thanh.pdf', 500, 'application/pdf')])
            ->call('completeStep')
            ->assertHasNoErrors();

        Notification::assertSentTo(
            $accountant,
            ContractWorkflowUpdatedNotification::class,
            fn (ContractWorkflowUpdatedNotification $notification) => $notification->stepName === 'finished'
        );
        Notification::assertNotSentTo($itUser, ContractWorkflowUpdatedNotification::class);
    }

    public function test_accounting_is_not_notified_for_intermediate_steps(): void
    {
        Notification::fake();
        Storage::fake('public');

        $accountant = User::factory()->create(['is_active' => true]);
        $accountant->assignRole(Role::findByName(RoleEnum::KE_TOAN->value));

        $this->actingAs($this->techUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractWorkflowPanel::class, [
            'contractType' => 'commercial',
            'contractId' => $this->contract->id,
        ])
            ->call('openStep', 'processing')
            ->set('uploadFiles', [UploadedFile::fake()->create('dang-xu-ly.pdf', 500, 'application/pdf')])
            ->call('completeStep')
            ->assertHasNoErrors();

        Notification::assertNotSentTo($accountant, ContractWorkflowUpdatedNotification::class);
    }

    public function test_authorized_user_can_delete_attached_milestone_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->techUser);

        $file = UploadedFile::fake()->create('tailieu_xoa.pdf', 500, 'application/pdf');

        Livewire::test(\App\Livewire\Admin\Contracts\ContractWorkflowPanel::class, [
            'contractType' => 'commercial',
            'contractId' => $this->contract->id,
        ])
            ->call('openStep', 'finished')
            ->set('uploadFiles', [$file])
            ->call('completeStep')
            ->assertHasNoErrors();

        $milestoneFile = \App\Models\ContractMilestoneFile::where('contract_id', $this->contract->id)->first();
        $this->assertNotNull($milestoneFile);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractWorkflowPanel::class, [
            'contractType' => 'commercial',
            'contractId' => $this->contract->id,
        ])
            ->call('deleteFile', $milestoneFile->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('contract_milestone_files', [
            'id' => $milestoneFile->id,
        ]);
    }
}
