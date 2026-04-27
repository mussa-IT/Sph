<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_notifications_page(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_notification_filters(): void
    {
        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'project_reminders',
            'title' => 'Project alert',
            'message' => 'A project reminder.',
            'read' => false,
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('All')
            ->assertSee('Unread')
            ->assertSee('Projects')
            ->assertSee('Tasks')
            ->assertSee('System')
            ->assertSee('Project alert');
    }

    public function test_notifications_filter_shows_only_matching_types(): void
    {
        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'project_reminders',
            'title' => 'Project Deadline',
            'message' => 'Project notification',
            'read' => false,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'task_deadlines',
            'title' => 'Task Due',
            'message' => 'Task notification',
            'read' => false,
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index', ['filter' => 'projects']));

        $response->assertOk()
            ->assertSee('Project Deadline')
            ->assertDontSee('Task Due');
    }

    public function test_notifications_feed_returns_json_for_navbar_dropdown(): void
    {
        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'system_alerts',
            'title' => 'Maintenance Window',
            'message' => 'Scheduled maintenance notice.',
            'read' => false,
        ]);

        $response = $this->actingAs($user)->getJson(route('notifications.feed'));

        $response->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.title', 'Maintenance Window');
    }
}
