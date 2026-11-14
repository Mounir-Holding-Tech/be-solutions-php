<?php

declare(strict_types=1);

namespace BeSolutions\Resources;

final class Domains extends Resource
{
    /** @return array<string, mixed> The domain with the DNS records to create. */
    public function create(string $name): array
    {
        return $this->client->request('POST', '/domains', ['name' => $name]) ?? [];
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->client->request('GET', '/domains')['data'] ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        return $this->client->request('GET', '/domains/' . self::seg($id)) ?? [];
    }

    /** @return array<string, mixed> */
    public function verify(string $id): array
    {
        return $this->client->request('POST', '/domains/' . self::seg($id) . '/verify') ?? [];
    }

    public function remove(string $id): void
    {
        $this->client->request('DELETE', '/domains/' . self::seg($id));
    }
}
