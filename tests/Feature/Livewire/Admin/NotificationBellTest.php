<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\NotificationBell;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_a_browser_event_when_polling_finds_a_new_notification(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertNotDispatched('browser-notification');

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'Tests\\Fixtures\\BrowserNotification',
            'data' => [
                'contract_type' => 'commission',
                'contract_label' => 'Yêu cầu chi hoa hồng',
                'message' => 'Có yêu cầu mới cần xử lý.',
                'url' => '/hoa-hong',
            ],
        ]);

        $component
            ->call('$refresh')
            ->assertDispatched('browser-notification', function (string $event, array $payload): bool {
                return $payload['title'] === 'Yêu cầu chi hoa hồng'
                    && $payload['body'] === 'Có yêu cầu mới cần xử lý.'
                    && $payload['url'] === '/hoa-hong';
            });
    }
}
