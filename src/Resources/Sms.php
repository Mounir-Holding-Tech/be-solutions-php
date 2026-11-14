<?php

declare(strict_types=1);

namespace BeSolutions\Resources;

use BeSolutions\Client;

final class Sms extends Resource
{
    public readonly SmsSenders $senders;

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->senders = new SmsSenders($client);
    }

    /**
     * @param array{to: string, body: string, from?: string, scheduled_at?: string, tags?: list<array{name: string, value: string}>} $sms
     * @return array<string, mixed>
     */
    public function send(array $sms, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/sms', $sms, $idempotencyKey) ?? [];
    }

    /**
     * @param list<array<string, mixed>> $messages Up to 100.
     * @return list<array<string, mixed>>
     */
    public function batch(array $messages, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/sms/batch', $messages, $idempotencyKey)['data'] ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        return $this->client->request('GET', '/sms/' . self::seg($id)) ?? [];
    }

    /** @return array<string, mixed> */
    public function list(?int $limit = null, ?string $before = null, ?string $status = null): array
    {
        return $this->client->request('GET', '/sms' . self::query(['limit' => $limit, 'before' => $before, 'status' => $status])) ?? [];
    }

    public function cancel(string $id): void
    {
        $this->client->request('POST', '/sms/' . self::seg($id) . '/cancel');
    }

    /**
     * Price per segment for a phone number or an ISO country code.
     *
     * @return array{country: string, country_name: string, currency: string, price_per_segment: float}
     */
    public function pricing(?string $to = null, ?string $country = null): array
    {
        /** @var array{country: string, country_name: string, currency: string, price_per_segment: float} */
        return $this->client->request('GET', '/sms/pricing' . self::query(['to' => $to, 'country' => $country]));
    }
}
