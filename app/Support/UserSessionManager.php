<?php

namespace App\Support;

use App\User;

class UserSessionManager
{
    const MAX_ACTIVE_SESSIONS = 2;

    public static function configuredLimit(User $user): int
    {
        $limit = (int) ($user->max_active_sessions ?? 1);

        return max(1, min(self::MAX_ACTIVE_SESSIONS, $limit));
    }

    public static function hasVerifiedTotp(User $user): bool
    {
        return (int) $user->enable_2fa === 1
            && (int) $user->verify_2fa === 1
            && ! empty($user->secret);
    }

    public static function requiresTotpSetup(User $user): bool
    {
        return (int) $user->method === 2 || self::configuredLimit($user) > 1;
    }

    public static function requiresTotpChallenge(User $user): bool
    {
        return self::requiresTotpSetup($user) && self::hasVerifiedTotp($user);
    }

    public static function effectiveLimit(User $user): int
    {
        $limit = self::configuredLimit($user);

        if ($limit > 1 && ! self::hasVerifiedTotp($user)) {
            return 1;
        }

        return $limit;
    }

    public static function register(User $user, string $sessionId, ?string $ipAddress = null): void
    {
        if ($sessionId === '') {
            return;
        }

        $entries = array_values(array_filter(self::entries($user), function ($entry) use ($sessionId) {
            return $entry['id'] !== $sessionId;
        }));

        $entries[] = [
            'id' => $sessionId,
            'ip' => $ipAddress,
            'logged_at' => date('Y-m-d H:i:s'),
            'last_seen_at' => date('Y-m-d H:i:s'),
        ];

        $entries = self::latest($entries);
        $entries = array_slice($entries, 0, self::effectiveLimit($user));

        $user->forceFill([
            'last_session_id' => $sessionId,
            'active_session_ids' => json_encode(array_values($entries)),
        ])->save();
    }

    public static function unregister(User $user, string $sessionId): void
    {
        if ($sessionId === '') {
            return;
        }

        $entries = array_values(array_filter(self::entries($user), function ($entry) use ($sessionId) {
            return $entry['id'] !== $sessionId;
        }));

        $user->forceFill([
            'active_session_ids' => empty($entries) ? null : json_encode(array_values($entries)),
        ])->save();
    }

    public static function isAllowed(User $user, string $sessionId): bool
    {
        if ($sessionId === '') {
            return false;
        }

        $entries = self::entries($user);
        $entryIds = array_map(function ($entry) {
            return $entry['id'];
        }, $entries);

        if (! empty($user->last_session_id) && ! in_array($user->last_session_id, $entryIds, true)) {
            return $user->last_session_id === $sessionId;
        }

        if (empty($entries)) {
            return empty($user->last_session_id) || $user->last_session_id === $sessionId;
        }

        $allowedIds = array_map(function ($entry) {
            return $entry['id'];
        }, array_slice(self::latest($entries), 0, self::effectiveLimit($user)));

        return in_array($sessionId, $allowedIds, true);
    }

    public static function entries(User $user): array
    {
        $raw = $user->active_session_ids ?? null;
        if (empty($raw)) {
            return [];
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        $entries = [];
        foreach ($decoded as $entry) {
            if (is_string($entry)) {
                $entry = ['id' => $entry];
            }

            if (! is_array($entry) || empty($entry['id'])) {
                continue;
            }

            $entries[] = [
                'id' => (string) $entry['id'],
                'ip' => $entry['ip'] ?? null,
                'logged_at' => $entry['logged_at'] ?? null,
                'last_seen_at' => $entry['last_seen_at'] ?? ($entry['logged_at'] ?? null),
            ];
        }

        return $entries;
    }

    private static function latest(array $entries): array
    {
        usort($entries, function ($left, $right) {
            return strcmp((string) ($right['last_seen_at'] ?? ''), (string) ($left['last_seen_at'] ?? ''));
        });

        return $entries;
    }
}
