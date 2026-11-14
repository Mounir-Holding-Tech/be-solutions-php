<?php

declare(strict_types=1);

namespace BeSolutions\Resources;

use BeSolutions\Client;

/** @internal */
abstract class Resource
{
    public function __construct(protected readonly Client $client)
    {
    }

    protected static function seg(string $value): string
    {
        return rawurlencode($value);
    }

    /** @param array<string, mixed> $params */
    protected static function query(array $params): string
    {
        $params = array_filter($params, static fn ($v) => $v !== null && $v !== '');
        return $params ? '?' . http_build_query($params) : '';
    }
}
