<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecuritySettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_security_settings_page(): void
    {
        $this->get(route('settings.security'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_security_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.security'))
            ->assertOk()
            ->assertSee('Change Password')
            ->assertSee('Current Password')
            ->assertSee('Strong Password Rules')
            ->assertSee('Signed-in Devices')
            ->assertSee('Future 2FA Architecture');
    }

    public function test_password_update_requires_current_password_and_uses_strong_rules(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPass123!'),
        ]);

        $this->actingAs($user)
            ->from(route('settings.security'))
            ->patch(route('settings.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'weak',
                'password_confirmation' => 'weak',
            ])
            ->assertRedirect(route('settings.security'))
            ->assertSessionHasErrors(['current_password', 'password']);

        $this->actingAs($user)
            ->from(route('settings.security'))
            ->patch(route('settings.password.update'), [
                'current_password' => 'CurrentPass123!',
                'password' => 'BetterPass123!',
                'password_confirmation' => 'BetterPass123!',
            ])
            ->assertRedirect(route('settings.security'))
            ->assertSessionHas('status', 'password-updated');

        $user->refresh();
        $this->assertTrue(Hash::check('BetterPass123!', $user->password));
    }

    public function test_user_can_logout_other_sessions_with_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPass123!'),
        ]);

        $this->actingAs($user);

        $currentSessionId = session()->getId();
        DB::table('sessions')->insert([
            [
                'id' => $currentSessionId,
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 Chrome/125',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'other-session-1',
                'user_id' => $user->id,
                'ip_address' => '10.0.0.5',
                'user_agent' => 'Mozilla/5.0 Firefox/124',
                'payload' => 'payload',
                'last_activity' => now()->subMinutes(5)->timestamp,
            ],
        ]);

        $this->from(route('settings.security'))
            ->patch(route('settings.security.sessions.logout-others'), [
                'current_password' => 'CurrentPass123!',
            ])
            ->assertRedirect(route('settings.security'))
            ->assertSessionHas('status', 'sessions-cleared');

        $this->assertDatabaseMissing('sessions', ['id' => 'other-session-1', 'user_id' => $user->id]);
        $this->assertTrue(
            DB::table('sessions')->where('user_id', $user->id)->count() <= 1
        );
    }

    public function test_user_can_update_two_factor_placeholder_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.security.two-factor-placeholder.update'), [
                'two_factor_enabled' => '1',
                'two_factor_channel' => 'authenticator',
            ])
            ->assertRedirect(route('settings.security'))
            ->assertSessionHas('status', 'two-factor-placeholder-updated');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_enabled' => 1,
            'two_factor_channel' => 'authenticator',
        ]);
    }
}
