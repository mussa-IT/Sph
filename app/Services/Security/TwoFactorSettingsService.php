<?php

namespace App\Services\Security;

use App\Models\User;

class TwoFactorSettingsService
{
    /**
     * @return array<string, mixed>
     */
    public function placeholderState(User $user): array
    {
        return [
            'enabled' => (bool) $user->two_factor_enabled,
            'channel' => (string) ($user->two_factor_channel ?: 'authenticator'),
            'status' => 'placeholder',
            'note' => 'Two-factor setup flows will be added in a future release.',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updatePlaceholder(User $user, array $data): User
    {
        $user->forceFill([
            'two_factor_enabled' => (bool) ($data['two_factor_enabled'] ?? false),
            'two_factor_channel' => (string) ($data['two_factor_channel'] ?? 'authenticator'),
        ])->save();

        return $user;
    }
}

