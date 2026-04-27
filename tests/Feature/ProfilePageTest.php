<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_profile_page(): void
    {
        $this->get(route('profile'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile_page_sections(): void
    {
        $user = User::factory()->create([
            'name' => 'Doris Example',
            'email' => 'doris@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertOk()
            ->assertSee('Profile Photo')
            ->assertSee('Full Name')
            ->assertSee('Email')
            ->assertSee('Joined Date')
            ->assertSee('Account Badge')
            ->assertSee('Edit Profile')
            ->assertSee('Doris Example')
            ->assertSee('doris@example.com');
    }

    public function test_profile_page_shows_account_insights_counts(): void
    {
        $user = User::factory()->create();

        $user->projects()->create([
            'title' => 'Project One',
            'status' => 'completed',
            'progress' => 100,
        ]);

        $user->projects()->create([
            'title' => 'Project Two',
            'status' => 'active',
            'progress' => 55,
        ]);

        $project = Project::query()->where('user_id', $user->id)->firstOrFail();

        $user->tasks()->create([
            'project_id' => $project->id,
            'title' => 'Finished Task',
            'status' => Task::STATUS_DONE,
            'priority' => Task::PRIORITY_MEDIUM,
        ]);

        $user->tasks()->create([
            'project_id' => $project->id,
            'title' => 'Pending Task',
            'status' => Task::STATUS_PENDING,
            'priority' => Task::PRIORITY_LOW,
        ]);

        $user->chatSessions()->create(['title' => 'AI Session 1']);
        $user->chatSessions()->create(['title' => 'AI Session 2']);

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertOk()
            ->assertSee('Account Insights')
            ->assertSee('Total projects')
            ->assertSee('Completed projects')
            ->assertSee('AI chats used')
            ->assertSee('Tasks finished')
            ->assertSee('Member since')
            ->assertSee('2')
            ->assertSee('1');
    }
}
