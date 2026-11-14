<?php

declare(strict_types=1);

namespace BeSolutions\Resources;

final class Participants extends Resource
{
    /** @return list<array<string, mixed>> */
    public function list(string $room): array
    {
        return $this->client->request('GET', '/rooms/' . self::seg($room) . '/participants')['participants'] ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $room, string $identity): array
    {
        return $this->client->request('GET', '/rooms/' . self::seg($room) . '/participants/' . self::seg($identity)) ?? [];
    }

    /**
     * @param array{name?: string, metadata?: string} $input
     * @return array<string, mixed>
     */
    public function update(string $room, string $identity, array $input): array
    {
        return $this->client->request('PATCH', '/rooms/' . self::seg($room) . '/participants/' . self::seg($identity), $input) ?? [];
    }

    /** Removes the participant from the call. */
    public function remove(string $room, string $identity): void
    {
        $this->client->request('DELETE', '/rooms/' . self::seg($room) . '/participants/' . self::seg($identity));
    }

    /** @return array<string, mixed> */
    public function mute(string $room, string $identity, string $trackSid, bool $muted = true): array
    {
        return $this->client->request('POST', '/rooms/' . self::seg($room) . '/participants/' . self::seg($identity) . '/mute', [
            'trackSid' => $trackSid,
            'muted' => $muted,
        ]) ?? [];
    }
}
