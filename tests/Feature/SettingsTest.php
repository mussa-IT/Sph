<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_settings_page(): void
    {
        $this->get(route('settings'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ])
            ->assertSessionHas('status', 'profile-updated')
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_authenticated_user_can_upload_avatar_and_extended_profile_fields(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->image('avatar.png', 300, 300);

        $this->actingAs($user)
            ->patch(route('settings.profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'bio' => 'Senior product engineer building AI systems.',
                'location' => 'San Francisco, USA',
                'website' => 'https://example.com',
                'avatar' => $avatar,
            ])
            ->assertSessionHas('status', 'profile-updated')
            ->assertRedirect();

        $user->refresh();
        $this->assertNotEmpty($user->avatar_path);
        $this->assertSame('Senior product engineer building AI systems.', $user->bio);
        $this->assertSame('San Francisco, USA', $user->location);
        $this->assertSame('https://example.com', $user->website);
        Storage::disk('public')->assertExists((string) $user->avatar_path);
    }

    public function test_authenticated_user_can_update_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPass123!'),
        ]);

        $this->actingAs($user)
            ->patch(route('settings.password.update'), [
                'current_password' => 'OldPass123!',
                'password' => 'NewPass123!',
                'password_confirmation' => 'NewPass123!',
            ])
            ->assertSessionHas('status', 'password-updated')
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue(Hash::check('NewPass123!', $user->password));
    }

    public function test_authenticated_user_can_update_preferences_and_session_locale(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patch(route('settings.preferences.update'), [
                'preferred_locale' => 'sw',
                'timezone' => 'Africa/Nairobi',
                'theme_preference' => 'system',
                'receive_product_updates' => 1,
                'receive_marketing_emails' => 1,
            ]);

        $response->assertSessionHas('status', 'preferences-updated')
            ->assertSessionHas('locale', 'sw')
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'preferred_locale' => 'sw',
            'timezone' => 'Africa/Nairobi',
            'receive_product_updates' => 1,
            'receive_marketing_emails' => 1,
        ]);
    }

    public function test_authenticated_user_can_delete_account_with_password_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('DeleteMe123!'),
        ]);

        $this->actingAs($user)
            ->delete(route('settings.account.destroy'), [
                'delete_password' => 'DeleteMe123!',
                'confirm_delete' => '1',
            ])
            ->assertRedirect(route('home'));

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
        $this->assertGuest();
    }

    public function test_delete_account_requires_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('DeleteMe123!'),
        ]);

        $this->actingAs($user)
            ->from(route('settings'))
            ->delete(route('settings.account.destroy'), [
                'delete_password' => 'WrongPassword123!',
                'confirm_delete' => '1',
            ])
            ->assertRedirect(route('settings'))
            ->assertSessionHasErrors('delete_password');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function test_delete_account_requires_permanent_action_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('DeleteMe123!'),
        ]);

        $this->actingAs($user)
            ->from(route('settings'))
            ->delete(route('settings.account.destroy'), [
                'delete_password' => 'DeleteMe123!',
            ])
            ->assertRedirect(route('settings'))
            ->assertSessionHasErrors('confirm_delete');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }
}
