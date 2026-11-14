<?php

declare(strict_types=1);

namespace BeSolutions;

use BeSolutions\Resources\Domains;
use BeSolutions\Resources\Emails;
use BeSolutions\Resources\Participants;
use BeSolutions\Resources\Rooms;
use BeSolutions\Resources\Sms;
use BeSolutions\Resources\Suppressions;
use BeSolutions\Resources\Tokens;

/**
 * Be Solutions API client.
 *
 *   $bes = new \BeSolutions\Client(['api_key' => getenv('BES_API_KEY'), 'base_url' => 'https://be-solutions.live']);
 *   $bes->emails->send(['from' => 'Acme <hello@acme.com>', 'to' => 'ada@example.com', 'subject' => 'Hi', 'html' => '<p>Hi</p>']);
 */
final class Client
{
    public const VERSION = '0.1.0';

    public readonly string $baseUrl;
    public readonly Tokens $tokens;
    public readonly Rooms $rooms;
    public readonly Participants $participants;
    public readonly Emails $emails;
    public readonly Domains $domains;
    public readonly Suppressions $suppressions;
    public readonly Sms $sms;

    private string $authorization;
    private ?string $projectKey;
    private ?string $projectSecret;
    private int $timeout;

    /**
     * @param array{api_key?: string, project_key?: string, project_secret?: string, base_url?: string, timeout?: int} $options
     */
    public function __construct(array $options = [])
    {
        $env = static fn (string $k): ?string => ($v = getenv($k)) === false || $v === '' ? null : $v;
        $this->baseUrl = rtrim($options['base_url'] ?? $env('BES_BASE_URL') ?? 'https://be-solutions.live', '/');
        $this->projectKey = $options['project_key'] ?? $env('BES_PROJECT_KEY');
        $this->projectSecret = $options['project_secret'] ?? $env('BES_PROJECT_SECRET');
        $apiKey = $options['api_key'] ?? $env('BES_API_KEY');
        if ($apiKey) {
            $this->authorization = 'Bearer ' . $apiKey;
        } elseif ($this->projectKey && $this->projectSecret) {
            $this->authorization = 'Basic ' . base64_encode($this->projectKey . ':' . $this->projectSecret);
        } else {
            throw new \InvalidArgumentException('Be Solutions: pass api_key (bes_…) or project_key + project_secret');
        }
        $this->timeout = (int) ($options['timeout'] ?? 20);

        $this->tokens = new Tokens($this);
        $this->rooms = new Rooms($this);
        $this->participants = new Participants($this);
        $this->emails = new Emails($this);
        $this->domains = new Domains($this);
        $this->suppressions = new Suppressions($this);
        $this->sms = new Sms($this);
    }

    /**
     * Low-level request to /v1. Returns the decoded JSON body (associative array) or null for 204.
     *
     * @param array<string, mixed>|list<mixed>|null $body
     * @return array<string, mixed>|null
     * @throws BeSolutionsException
     */
    public function request(string $method, string $path, ?array $body = null, ?string $idempotencyKey = null): ?array
    {
        $headers = [
            'Authorization: ' . $this->authorization,
            'Accept: application/json',
            'User-Agent: be-solutions-php/' . self::VERSION,
        ];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        if ($idempotencyKey !== null) {
            $headers[] = 'Idempotency-Key: ' . $idempotencyKey;
        }

        $ch = curl_init($this->baseUrl . '/v1' . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new BeSolutionsException('Be Solutions unreachable: ' . $error, 0);
        }
        if ($status === 204 || $raw === '') {
            return null;
        }
        $data = json_decode((string) $raw, true);
        if ($status >= 400) {
            $message = is_array($data) ? ($data['message'] ?? $data['error'] ?? "HTTP $status") : "HTTP $status";
            throw new BeSolutionsException((string) $message, $status, is_array($data) ? $data : null);
        }
        return is_array($data) ? $data : null;
    }

    /**
     * Signs a participant access token locally (no network call). Needs project_key and project_secret.
     *
     * @param array{room: string, identity: string, name?: string, metadata?: string, ttl?: int, grants?: array<string, mixed>} $input
     * @return array{token: string, expiresAt: int}
     */
    public function createAccessToken(array $input): array
    {
        if (!$this->projectKey || !$this->projectSecret) {
            throw new \LogicException('createAccessToken needs project_key and project_secret');
        }
        return AccessToken::sign($this->projectKey, $this->projectSecret, $input);
    }

    /**
     * Participant-minutes and sessions for a period (epoch milliseconds).
     *
     * @return array<string, mixed>
     */
    public function usage(?int $from = null, ?int $to = null): array
    {
        $query = http_build_query(array_filter(['from' => $from, 'to' => $to], static fn ($v) => $v !== null));
        return $this->request('GET', '/usage' . ($query ? '?' . $query : '')) ?? [];
    }
}
