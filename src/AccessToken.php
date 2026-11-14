<?php

declare(strict_types=1);

namespace BeSolutions;

/** Participant access tokens (HS256 JWT), identical to POST /v1/tokens. */
final class AccessToken
{
    /**
     * @param array{room: string, identity: string, name?: string, metadata?: string, ttl?: int, grants?: array<string, mixed>} $input
     * @return array{token: string, expiresAt: int}
     */
    public static function sign(string $projectKey, string $projectSecret, array $input): array
    {
        $ttl = min((int) ($input['ttl'] ?? 6 * 3600), 24 * 3600);
        $now = time();
        $claims = [
            'iss' => $projectKey,
            'sub' => $input['identity'],
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
        ];
        if (isset($input['name'])) {
            $claims['name'] = $input['name'];
        }
        if (isset($input['metadata'])) {
            $claims['metadata'] = $input['metadata'];
        }
        $claims['video'] = ['room' => $input['room']] + ($input['grants'] ?? []);

        $b64 = static fn (string $v): string => rtrim(strtr(base64_encode($v), '+/', '-_'), '=');
        $head = $b64((string) json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body = $b64((string) json_encode($claims, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $sig = $b64(hash_hmac('sha256', $head . '.' . $body, $projectSecret, true));

        return ['token' => $head . '.' . $body . '.' . $sig, 'expiresAt' => $claims['exp'] * 1000];
    }
}
