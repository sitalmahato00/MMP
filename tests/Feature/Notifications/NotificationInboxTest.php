<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_mark_open_and_delete_notifications(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test-notification',
            'data' => [
                'title' => 'Exam published',
                'message' => 'Semester exam results are available.',
                'action_url' => route('student.dashboard'),
                'action_label' => 'View dashboard',
            ],
        ]);

        $this->assertSame(1, $user->notifications()->count());
        $this->assertSame(1, $user->unreadNotifications()->count());

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);

        $openNotification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test-notification',
            'data' => [
                'title' => 'Open me',
                'message' => 'Redirect to dashboard.',
                'action_url' => route('student.dashboard'),
                'action_label' => 'Open',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('notifications.open', $openNotification))
            ->assertRedirect(route('student.dashboard'));

        $this->assertNotNull($openNotification->fresh()->read_at);

        $this->actingAs($user)
            ->delete(route('notifications.destroy', $openNotification))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', [
            'id' => $openNotification->id,
        ]);
    }
}
