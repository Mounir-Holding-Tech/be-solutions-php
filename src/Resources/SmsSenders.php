<?php

declare(strict_types=1);

namespace BeSolutions\Resources;

final class SmsSenders extends Resource
{
    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->client->request('GET', '/sms/senders')['data'] ?? [];
    }

    /** Requests an alphanumeric sender ID (reviewed by an administrator before use). @return array<string, mixed> */
    public function request(string $name, string $useCase): array
    {
        return $this->client->request('POST', '/sms/senders', ['name' => $name, 'use_case' => $useCase]) ?? [];
    }

    public function remove(string $id): void
    {
        $this->client->request('DELETE', '/sms/senders/' . self::seg($id));
    }
}
