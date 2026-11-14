<?php

declare(strict_types=1);

namespace BeSolutions\Resources;

final class Emails extends Resource
{
    /**
     * Sends an email from a verified domain. Attachment "content" may be raw bytes (set "raw" => true) or base64.
     *
     * @param array<string, mixed> $email from, to, subject, html/text, cc, bcc, reply_to, headers, attachments, tags, scheduled_at
     * @return array{id: string}
     */
    public function send(array $email, ?string $idempotencyKey = null): array
    {
        /** @var array{id: string} */
        return $this->client->request('POST', '/emails', self::prepare($email), $idempotencyKey);
    }

    /**
     * @param list<array<string, mixed>> $emails Up to 100 (no attachments or scheduling).
     * @return list<array{id: string}>
     */
    public function batch(array $emails, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/emails/batch', array_map([self::class, 'prepare'], $emails), $idempotencyKey)['data'] ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        return $this->client->request('GET', '/emails/' . self::seg($id)) ?? [];
    }

    /** @return array{object: string, has_more: bool, data: list<array<string, mixed>>} */
    public function list(?int $limit = null, ?string $before = null): array
    {
        /** @var array{object: string, has_more: bool, data: list<array<string, mixed>>} */
        return $this->client->request('GET', '/emails' . self::query(['limit' => $limit, 'before' => $before]));
    }

    public function reschedule(string $id, string $scheduledAt): void
    {
        $this->client->request('PATCH', '/emails/' . self::seg($id), ['scheduled_at' => $scheduledAt]);
    }

    public function cancel(string $id): void
    {
        $this->client->request('POST', '/emails/' . self::seg($id) . '/cancel');
    }

    /**
     * @param array<string, mixed> $email
     * @return array<string, mixed>
     */
    private static function prepare(array $email): array
    {
        if (isset($email['attachments']) && is_array($email['attachments'])) {
            $email['attachments'] = array_map(static function (array $a): array {
                if (!empty($a['raw'])) {
                    $a['content'] = base64_encode((string) $a['content']);
                }
                unset($a['raw']);
                return $a;
            }, $email['attachments']);
        }
        return $email;
    }
}
