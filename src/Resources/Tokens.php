<?php

declare(strict_types=1);

namespace BeSolutions\Resources;

final class Tokens extends Resource
{
    /**
     * Mints a participant access token on the server.
     *
     * @param array{room: string, identity: string, name?: string, metadata?: string, ttl?: int, grants?: array<string, mixed>} $input
     * @return array{token: string, expiresAt: int}
     */
    public function create(array $input): array
    {
        /** @var array{token: string, expiresAt: int} */
        return $this->client->request('POST', '/tokens', $input);
    }
}
