<?php

declare(strict_types=1);

namespace BeSolutions;

/**
 * Verifies Be Solutions webhooks: X-RTC-Signature = hex(HMAC-SHA256(project secret, timestamp . "." . raw body)).
 *
 *   $event = \BeSolutions\Webhook::verify(file_get_contents('php://input'), getallheaders(), getenv('BES_PROJECT_SECRET'));
 */
final class Webhook
{
    /**
     * @param array<string, string|list<string>> $headers Request headers (any letter case).
     * @return array<string, mixed> The decoded event.
     * @throws BeSolutionsException When the signature is missing, wrong, or older than $toleranceSec.
     */
    public static function verify(string $rawBody, array $headers, string $projectSecret, int $toleranceSec = 300): array
    {
        $lower = array_change_key_case($headers, CASE_LOWER);
        $get = static function (string $name) use ($lower): ?string {
            $v = $lower[$name] ?? null;
            return is_array($v) ? ($v[0] ?? null) : $v;
        };
        $timestamp = $get('x-rtc-timestamp');
        $signature = $get('x-rtc-signature');
        if ($timestamp === null || $signature === null) {
            throw new BeSolutionsException('missing webhook signature headers', 400);
        }
        if (abs(time() - (int) $timestamp) > $toleranceSec) {
            throw new BeSolutionsException('webhook timestamp too old', 400);
        }
        $expected = hash_hmac('sha256', $timestamp . '.' . $rawBody, $projectSecret);
        if (!hash_equals($expected, $signature)) {
            throw new BeSolutionsException('invalid webhook signature', 400);
        }
        $event = json_decode($rawBody, true);
        if (!is_array($event)) {
            throw new BeSolutionsException('webhook body is not JSON', 400);
        }
        return $event;
    }
}
