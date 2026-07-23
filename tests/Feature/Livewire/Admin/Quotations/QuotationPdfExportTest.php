<?php

namespace Tests\Feature\Livewire\Admin\Quotations;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\QuotationDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QuotationPdfExportTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $salesUser;

    private User $otherSalesUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $roleEnum) {
            Role::findOrCreate($roleEnum->value);
        }
        foreach (PermissionEnum::cases() as $permissionEnum) {
            Permission::findOrCreate($permissionEnum->value);
        }

        $adminRole = Role::findByName(RoleEnum::IT->value);
        $adminRole->syncPermissions(Permission::all());

        $salesRole = Role::findByName(RoleEnum::KINH_DOANH->value);
        $salesRole->givePermissionTo(PermissionEnum::QUOTATION_TRACKING_VIEW->value);

        $this->adminUser = User::factory()->create(['is_active' => true]);
        $this->adminUser->assignRole($adminRole);

        $this->salesUser = User::factory()->create(['is_active' => true]);
        $this->salesUser->assignRole($salesRole);

        $this->otherSalesUser = User::factory()->create(['is_active' => true]);
        $this->otherSalesUser->assignRole($salesRole);
    }

    public function test_admin_can_export_quotation_pdf(): void
    {
        $doc = QuotationDocument::create([
            'document_number' => '001/'.now()->format('Y').'/BG – BC',
            'staff_id' => $this->salesUser->id,
            'customer_name' => 'Công ty ABC',
            'date' => now()->toDateString(),
            'subtotal' => 10000000,
            'vat_rate' => 8,
            'vat_amount' => 800000,
            'total' => 10800000,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('app.quotation-docs.export-pdf', $doc->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_sales_user_can_export_own_quotation_pdf(): void
    {
        $doc = QuotationDocument::create([
            'document_number' => '002/'.now()->format('Y').'/BG – BC',
            'staff_id' => $this->salesUser->id,
            'customer_name' => 'Công ty XYZ',
            'date' => now()->toDateString(),
            'subtotal' => 5000000,
            'vat_rate' => 8,
            'vat_amount' => 400000,
            'total' => 5400000,
        ]);

        $response = $this->actingAs($this->salesUser)
            ->get(route('app.quotation-docs.export-pdf', $doc->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_sales_user_cannot_export_others_quotation_pdf(): void
    {
        $doc = QuotationDocument::create([
            'document_number' => '003/'.now()->format('Y').'/BG – BC',
            'staff_id' => $this->otherSalesUser->id,
            'customer_name' => 'Công ty 123',
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->salesUser)
            ->get(route('app.quotation-docs.export-pdf', $doc->id));

        $response->assertStatus(403);
    }
}
