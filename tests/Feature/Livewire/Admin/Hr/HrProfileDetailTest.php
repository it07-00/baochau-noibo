<?php

namespace Tests\Feature\Livewire\Admin\Hr;

use App\Livewire\Admin\Hr\HrProfileDetail;
use App\Models\EmployeeContract;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HrProfileDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('hr-profiles.view');
        Permission::findOrCreate('hr-profiles.edit');
        Permission::findOrCreate('hr-profiles.delete');
    }

    public function test_user_with_permission_can_view_hr_profile_detail(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('hr-profiles.view');

        $employee = User::factory()->create([
            'name' => 'Nguyễn Văn A',
            'employee_code' => 'BC-101',
            'employment_status' => 'chinh_thuc',
            'work_type' => 'full_time',
        ]);

        Livewire::actingAs($admin)
            ->test(HrProfileDetail::class, ['user' => $employee])
            ->assertStatus(200)
            ->assertSee('Nguyễn Văn A')
            ->assertSee('BC-101')
            ->assertSee('Thông tin cá nhân');
    }

    public function test_user_can_save_personal_info(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['hr-profiles.view', 'hr-profiles.edit']);

        $employee = User::factory()->create([
            'name' => 'Trần Thị B',
            'employee_code' => 'BC-102',
            'employment_status' => 'thu_viec',
            'work_type' => 'full_time',
        ]);

        Livewire::actingAs($admin)
            ->test(HrProfileDetail::class, ['user' => $employee])
            ->set('employee_code', 'BC-888')
            ->set('phone', '0912345678')
            ->set('id_card_number', '123456789012')
            ->set('employment_status', 'chinh_thuc')
            ->set('work_type', 'full_time')
            ->call('savePersonalInfo')
            ->assertDispatched('swal:success');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'employee_code' => 'BC-888',
            'phone' => '0912345678',
            'id_card_number' => '123456789012',
            'employment_status' => 'chinh_thuc',
        ]);
    }

    public function test_user_can_add_and_delete_contract(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create();
        $admin->givePermissionTo(['hr-profiles.view', 'hr-profiles.edit', 'hr-profiles.delete']);

        $employee = User::factory()->create();

        $file = UploadedFile::fake()->create('hop_dong_ld.pdf', 100, 'application/pdf');

        Livewire::actingAs($admin)
            ->test(HrProfileDetail::class, ['user' => $employee])
            ->call('openContractModal')
            ->set('contract_type', 'co_thoi_han')
            ->set('contract_number', 'HĐLĐ-2026/01')
            ->set('contract_signed_date', '2026-01-15')
            ->set('contract_start_date', '2026-02-01')
            ->set('contract_end_date', '2027-01-31')
            ->set('contract_salary', 15000000)
            ->set('contract_status', 'active')
            ->set('contract_file', $file)
            ->call('saveContract')
            ->assertDispatched('swal:success');

        $contract = EmployeeContract::where('user_id', $employee->id)->first();
        $this->assertNotNull($contract);
        $this->assertEquals('HĐLĐ-2026/01', $contract->contract_number);
        $this->assertEquals(15000000, $contract->salary);

        Livewire::actingAs($admin)
            ->test(HrProfileDetail::class, ['user' => $employee])
            ->call('deleteContract', $contract->id)
            ->assertDispatched('swal:success');

        $this->assertDatabaseMissing('employee_contracts', ['id' => $contract->id]);
    }

    public function test_user_can_upload_and_delete_document(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create();
        $admin->givePermissionTo(['hr-profiles.view', 'hr-profiles.edit', 'hr-profiles.delete']);

        $employee = User::factory()->create();

        $file = UploadedFile::fake()->create('cccd_scan.jpg', 200, 'image/jpeg');

        Livewire::actingAs($admin)
            ->test(HrProfileDetail::class, ['user' => $employee])
            ->call('openDocumentModal')
            ->set('document_type', 'cccd_truoc')
            ->set('document_title', 'CCCD Mặt Trước Của Nhân Viên')
            ->set('document_files', [$file])
            ->call('saveDocuments')
            ->assertDispatched('swal:success');

        $doc = EmployeeDocument::where('user_id', $employee->id)->first();
        $this->assertNotNull($doc);
        $this->assertEquals('CCCD Mặt Trước Của Nhân Viên', $doc->title);

        Livewire::actingAs($admin)
            ->test(HrProfileDetail::class, ['user' => $employee])
            ->call('deleteDocument', $doc->id)
            ->assertDispatched('swal:success');

        $this->assertDatabaseMissing('employee_documents', ['id' => $doc->id]);
    }
}
