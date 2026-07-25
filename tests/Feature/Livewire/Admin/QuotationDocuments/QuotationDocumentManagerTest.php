<?php

namespace Tests\Feature\Livewire\Admin\QuotationDocuments;

use App\Enums\Permission;
use App\Enums\Role;
use App\Livewire\Admin\QuotationDocuments\QuotationDocumentManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

class QuotationDocumentManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        SpatiePermission::firstOrCreate(['name' => Permission::QUOTATION_TRACKING_CREATE->value]);
        SpatiePermission::firstOrCreate(['name' => Permission::QUOTATION_TRACKING_EDIT->value]);
        SpatiePermission::firstOrCreate(['name' => Permission::QUOTATION_TRACKING_VIEW->value]);

        SpatieRole::firstOrCreate(['name' => Role::KINH_DOANH->value]);
        SpatieRole::firstOrCreate(['name' => Role::TP_KINH_DOANH->value]);
        SpatieRole::firstOrCreate(['name' => Role::TU_VAN->value]);
        $adminRole = SpatieRole::firstOrCreate(['name' => Role::GIAM_DOC->value]);
        $adminRole->givePermissionTo([
            Permission::QUOTATION_TRACKING_CREATE->value,
            Permission::QUOTATION_TRACKING_EDIT->value,
            Permission::QUOTATION_TRACKING_VIEW->value,
        ]);

        $this->adminUser = User::factory()->create(['is_active' => true]);
        $this->adminUser->assignRole(Role::GIAM_DOC->value);
    }

    public function test_can_save_quotation_document_with_long_description(): void
    {
        $this->actingAs($this->adminUser);

        $longDescription = 'Thực hiện Quan trắc môi trường lao động 2026 tại CÔNG TY TNHH MỘT THÀNH VIÊN PHÁT TRIỂN CÔNG NGHIỆP BW THỜI HÒA, Đường NA2 và đường DA1, Lô A3 BW, khu công nghiệp Thới Hòa, phường Thới Hòa, thành phố Hồ Chí Minh, Việt Nam, Hồ sơ';

        Livewire::test(QuotationDocumentManager::class)
            ->call('create')
            ->set('formData.customer_name', 'CÔNG TY TNHH BW THỜI HÒA')
            ->set('formData.customer_address', 'Lô A3 BW')
            ->set('formData.date', '2026-07-25')
            ->set('detailItems', [
                [
                    'group_name' => 'I. YẾU TỐ VI KHÍ HẬU',
                    'description' => $longDescription,
                    'unit' => 'Mẫu',
                    'quantity' => 1,
                    'frequency' => 1,
                    'unit_price' => '111.990.000',
                    'amount' => 111990000,
                    'note' => 'Ghi chú dài vượt quá 255 ký tự để test tính năng lưu TEXT trong cơ sở dữ liệu không bị lỗi SQL truncation.',
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotation_document_items', [
            'description' => $longDescription,
        ]);
    }
}
