<?php

namespace Tests\Feature\Livewire\Admin\Quotations;

use App\Enums\Role as RoleEnum;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationDirectorViewTest extends TestCase
{
    use RefreshDatabase;

    private User $directorUser;

    private User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionsSeeder::class);

        $dept = Department::firstOrCreate(
            ['slug' => 'ban-giam-doc'],
            ['name' => 'Ban Giam doc']
        );

        $this->directorUser = User::factory()->create([
            'is_active' => true,
            'department_id' => $dept->id,
        ]);
        $this->directorUser->assignRole(RoleEnum::GIAM_DOC->value);

        $this->unauthorizedUser = User::factory()->create([
            'is_active' => true,
        ]);
        $this->unauthorizedUser->assignRole(RoleEnum::KY_THUAT->value);
    }

    public function test_director_user_can_access_quotation_tracking_page_as_view_only(): void
    {
        $this->actingAs($this->directorUser);

        $response = $this->get(route('app.quotation-tracking.index'));

        $response->assertOk();
        $response->assertDontSee('Import Excel');
        $response->assertDontSee('wire:click="create"', false);
        $response->assertDontSee('wire:click="edit', false);
    }

    public function test_director_user_cannot_access_quotation_documents_page(): void
    {
        $this->actingAs($this->directorUser);

        $this->get(route('app.quotation-docs.index'))->assertStatus(403);
    }

    public function test_director_user_sees_only_tracking_sidebar_item(): void
    {
        $this->actingAs($this->directorUser);

        $response = $this->get(route('app.quotation-tracking.index'));

        $response->assertOk();
        $response->assertSee('theo-doi-bao-gia', false);
        $response->assertDontSee('tao-bao-gia', false);
    }

    public function test_unauthorized_user_cannot_access_quotation_pages(): void
    {
        $this->actingAs($this->unauthorizedUser);

        $this->get(route('app.quotation-tracking.index'))->assertStatus(403);
        $this->get(route('app.quotation-docs.index'))->assertStatus(403);
    }
}
