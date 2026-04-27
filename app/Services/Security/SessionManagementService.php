<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionManagementService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listUserSessions(User $user, string $currentSessionId): array
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function (object $row) use ($currentSessionId): array {
                $userAgent = (string) ($row->user_agent ?? '');
                $lastActive = Carbon::createFromTimestamp((int) $row->last_activity);

                return [
                    'id' => (string) $row->id,
                    'ip_address' => (string) ($row->ip_address ?? ''),
                    'browser' => $this->resolveBrowser($userAgent),
                    'device' => $this->resolveDevice($userAgent),
                    'is_current' => (string) $row->id === $currentSessionId,
                    'last_active' => $lastActive->toDateTimeString(),
                    'last_active_human' => $lastActive->diffForHumans(),
                ];
            })
            ->values()
            ->all();
    }

    public function logoutOtherSessions(User $user, string $currentPassword, string $currentSessionId): int
    {
        Auth::logoutOtherDevices($currentPassword);

        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }

    private function resolveBrowser(string $userAgent): string
    {
        $agent = strtolower($userAgent);

        return match (true) {
            str_contains($agent, 'edg/') => 'Microsoft Edge',
            str_contains($agent, 'chrome/') => 'Google Chrome',
            str_contains($agent, 'firefox/') => 'Mozilla Firefox',
            str_contains($agent, 'safari/') && ! str_contains($agent, 'chrome/') => 'Safari',
            str_contains($agent, 'opr/') || str_contains($agent, 'opera') => 'Opera',
            default => 'Unknown Browser',
        };
    }

    private function resolveDevice(string $userAgent): string
    {
        $agent = strtolower($userAgent);

        return match (true) {
            str_contains($agent, 'iphone') => 'iPhone',
            str_contains($agent, 'ipad') => 'iPad',
            str_contains($agent, 'android') => 'Android Device',
            str_contains($agent, 'windows') => 'Windows PC',
            str_contains($agent, 'macintosh') || str_contains($agent, 'mac os') => 'Mac',
            str_contains($agent, 'linux') => 'Linux',
            default => 'Unknown Device',
        };
    }
}

