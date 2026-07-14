<?php

namespace Tests\Feature\Livewire\Admin\Quotations;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\QuotationDocument;
use App\Models\User;
use App\Livewire\Admin\QuotationDocuments\QuotationDocumentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QuotationDocumentNumberTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $roleEnum) {
            Role::findOrCreate($roleEnum->value);
        }
        $adminRole = Role::findByName(RoleEnum::IT->value);

        foreach (PermissionEnum::cases() as $permissionEnum) {
            Permission::findOrCreate($permissionEnum->value);
        }
        $adminRole->syncPermissions(Permission::all());

        $this->adminUser = User::factory()->create([
            'is_active' => true,
        ]);
        $this->adminUser->assignRole($adminRole);
    }

    public function test_document_number_defaults_to_first_number_in_current_year(): void
    {
        $this->actingAs($this->adminUser);

        $year = now()->format('Y');
        $expectedNumber = '001/' . $year . '/BG – **BC';

        Livewire::test(QuotationDocumentManager::class)
            ->call('create')
            ->assertSet('formData.document_number', $expectedNumber);
    }

    public function test_document_number_increments_from_legacy_format(): void
    {
        $this->actingAs($this->adminUser);

        $year = now()->format('Y');

        // Seed a legacy format document
        QuotationDocument::create([
            'document_number' => 'BG-' . $year . '-003',
            'staff_id' => $this->adminUser->id,
            'customer_name' => 'Test Customer',
            'date' => now()->toDateString(),
        ]);

        $expectedNumber = '004/' . $year . '/BG – **BC';

        Livewire::test(QuotationDocumentManager::class)
            ->call('create')
            ->assertSet('formData.document_number', $expectedNumber);
    }

    public function test_document_number_increments_from_new_format(): void
    {
        $this->actingAs($this->adminUser);

        $year = now()->format('Y');

        // Seed a new format document
        QuotationDocument::create([
            'document_number' => '008/' . $year . '/BG – **BC',
            'staff_id' => $this->adminUser->id,
            'customer_name' => 'Test Customer',
            'date' => now()->toDateString(),
        ]);

        $expectedNumber = '009/' . $year . '/BG – **BC';

        Livewire::test(QuotationDocumentManager::class)
            ->call('create')
            ->assertSet('formData.document_number', $expectedNumber);
    }

    public function test_detail_indicator_accepts_manual_text_alongside_catalog_suggestions(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(QuotationDocumentManager::class)
            ->call('create')
            ->call('addDetailItem')
            ->assertSee('Chọn hoặc nhập chỉ tiêu...')
            ->set('detailItems.0.description', 'Chỉ tiêu nhập tay theo yêu cầu khách hàng')
            ->assertSet('detailItems.0.description', 'Chỉ tiêu nhập tay theo yêu cầu khách hàng');
    }
}
