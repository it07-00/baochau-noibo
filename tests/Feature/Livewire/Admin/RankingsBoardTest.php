<?php

namespace Tests\Feature\Livewire\Admin;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\RankingsBoard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RankingsBoardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() === 'sqlite') {
            /** @var \PDO $pdo */
            $pdo = DB::connection()->getPdo();
            if (method_exists($pdo, 'sqliteCreateFunction')) {
                $pdo->sqliteCreateFunction(
                    'MONTH',
                    static fn (?string $date): ?int => $date ? (int) date('n', strtotime($date)) : null
                );
            } elseif (method_exists($pdo, 'createFunction')) {
                $pdo->createFunction(
                    'MONTH',
                    static fn (?string $date): ?int => $date ? (int) date('n', strtotime($date)) : null
                );
            }
        }

        foreach (RoleEnum::cases() as $role) {
            Role::findOrCreate($role->value);
        }
    }

    public function test_rankings_board_renders_successfully_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::GIAM_DOC->value);

        $this->actingAs($user);

        Livewire::test(RankingsBoard::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.rankings-board');
    }
}
