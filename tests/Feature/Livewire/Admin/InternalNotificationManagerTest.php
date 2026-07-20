<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\InternalNotifications\InternalNotificationManager;
use App\Models\User;
use App\Notifications\InternalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class InternalNotificationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_groups_sent_notifications_without_any_value_database_function(): void
    {
        $sender = User::factory()->create();
        $batchId = (string) Str::uuid();

        foreach (User::factory()->count(2)->create() as $recipient) {
            $recipient->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => InternalNotification::class,
                'data' => [
                    'contract_label' => 'Thông báo thử nghiệm',
                    'message' => 'Nội dung thử nghiệm',
                    'sender_id' => $sender->id,
                    'batch_id' => $batchId,
                    'recipients_label' => 'Tất cả',
                ],
            ]);
        }

        Livewire::actingAs($sender)
            ->test(InternalNotificationManager::class)
            ->assertSee('Thông báo thử nghiệm')
            ->assertSee('2 người nhận');
    }
}
