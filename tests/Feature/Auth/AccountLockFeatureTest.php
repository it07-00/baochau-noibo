<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountLockFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_locked_user_cannot_login_with_valid_password(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->post(route('login.attempt'), [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('đã bị khóa', session('errors')->first('username'));
        $this->assertGuest();
    }

    public function test_active_user_can_login(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->post(route('login.attempt'), [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('app.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_locked_user_is_logged_out_from_existing_session(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $user->update(['is_active' => false]);

        $response = $this->get(route('app.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }
}
