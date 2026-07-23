<?php

namespace Tests\Feature\Livewire\Admin\Roles;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Roles\RoleManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::findOrCreate(RoleEnum::IT->value);
        Permission::findOrCreate(PermissionEnum::ROLES_VIEW->value);
        Permission::findOrCreate(PermissionEnum::ROLES_CREATE->value);
        Permission::findOrCreate(PermissionEnum::ROLES_EDIT->value);
        Permission::findOrCreate(PermissionEnum::ROLES_DELETE->value);

        $role->givePermissionTo([
            PermissionEnum::ROLES_VIEW->value,
            PermissionEnum::ROLES_CREATE->value,
            PermissionEnum::ROLES_EDIT->value,
            PermissionEnum::ROLES_DELETE->value,
        ]);

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole($role);
    }

    public function test_it_renders_role_manager_page(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(RoleManager::class)
            ->assertStatus(200)
            ->assertViewHas('totalRoles')
            ->assertViewHas('totalPermissions')
            ->assertViewHas('totalUsers');
    }

    public function test_it_filters_roles_by_search(): void
    {
        $this->actingAs($this->admin);

        Role::findOrCreate('ke-toan');
        Role::findOrCreate('marketing');

        Livewire::test(RoleManager::class)
            ->set('search', 'ke-toan')
            ->assertSee('Kế toán')
            ->assertDontSee('Marketing');
    }

    public function test_it_prevents_deleting_role_with_assigned_users(): void
    {
        $this->actingAs($this->admin);

        $role = Role::findOrCreate('ke-toan');
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        Livewire::test(RoleManager::class)
            ->call('deleteRole', $role->id)
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_it_deletes_unassigned_role(): void
    {
        $this->actingAs($this->admin);

        $role = Role::findOrCreate('test-unassigned-role');

        Livewire::test(RoleManager::class)
            ->call('deleteRole', $role->id)
            ->assertDispatched('swal:toast');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_it_renders_role_create_view(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('app.roles.create'));

        $response->assertStatus(200);
        $response->assertSee('Tạo mới vai trò');
        $response->assertSee('Ma trận phân quyền chi tiết');
    }

    public function test_it_renders_role_edit_view(): void
    {
        $this->actingAs($this->admin);

        $role = Role::findOrCreate(RoleEnum::KE_TOAN->value);
        $viewPermission = Permission::findOrCreate(PermissionEnum::USERS_VIEW->value);
        Permission::findOrCreate(PermissionEnum::USERS_EDIT->value);
        $role->givePermissionTo($viewPermission);

        $response = $this->get(route('app.roles.edit', $role));

        $response->assertStatus(200);
        $response->assertSee(PermissionEnum::USERS_VIEW->value);
        $response->assertSee(PermissionEnum::USERS_EDIT->value);
        $response->assertSee('module-group', false);
        $response->assertSee('value="'.PermissionEnum::USERS_VIEW->value.'"', false);
        $response->assertSee('checked', false);
        $response->assertSee('Chỉnh sửa');
        $response->assertSee('Ma trận phân quyền chi tiết');
    }
}
