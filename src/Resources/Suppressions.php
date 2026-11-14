<?php

declare(strict_types=1);

namespace BeSolutions\Resources;

final class Suppressions extends Resource
{
    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->client->request('GET', '/suppressions')['data'] ?? [];
    }

    public function remove(string $email): void
    {
        $this->client->request('DELETE', '/suppressions/' . self::seg($email));
    }
}
