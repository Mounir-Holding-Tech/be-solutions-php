<?php

declare(strict_types=1);

namespace BeSolutions\Resources;

final class Rooms extends Resource
{
    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->client->request('GET', '/rooms')['rooms'] ?? [];
    }

    /**
     * @param array{name: string, maxParticipants?: int, emptyTimeout?: int, metadata?: string} $input
     * @return array<string, mixed>
     */
    public function create(array $input): array
    {
        return $this->client->request('POST', '/rooms', $input) ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $room): array
    {
        return $this->client->request('GET', '/rooms/' . self::seg($room)) ?? [];
    }

    /**
     * @param array{maxParticipants?: int, emptyTimeout?: int, metadata?: string} $input
     * @return array<string, mixed>
     */
    public function update(string $room, array $input): array
    {
        return $this->client->request('PATCH', '/rooms/' . self::seg($room), $input) ?? [];
    }

    /** Ends the call for everyone. */
    public function delete(string $room): void
    {
        $this->client->request('DELETE', '/rooms/' . self::seg($room));
    }

    /** @param list<string>|null $to Identities to send to (everyone when null). */
    public function sendData(string $room, mixed $payload, ?string $topic = null, ?array $to = null): void
    {
        $body = ['payload' => $payload];
        if ($topic !== null) {
            $body['topic'] = $topic;
        }
        if ($to !== null) {
            $body['to'] = $to;
        }
        $this->client->request('POST', '/rooms/' . self::seg($room) . '/data', $body);
    }
}
