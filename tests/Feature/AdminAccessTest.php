<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_user_cannot_access_admin_dashboard(): void
    {
        config(['security.admin_emails' => ['admin@example.com']]);

        $user = User::factory()->create(['email' => 'member@example.com']);

        $this->actingAs($user)
            ->get(route('admin.index'))
            ->assertForbidden();
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        config(['security.admin_emails' => ['admin@example.com']]);

        $user = User::factory()->create(['email' => 'admin@example.com']);

        $this->actingAs($user)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Operations')
            ->assertSee('Recent Users');
    }
}
