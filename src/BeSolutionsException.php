<?php

declare(strict_types=1);

namespace BeSolutions;

/** Thrown for every API error. getCode() / $status is the HTTP status (0 when the server was unreachable). */
class BeSolutionsException extends \RuntimeException
{
    /**
     * @param array<string, mixed>|null $body
     */
    public function __construct(string $message, public readonly int $status, public readonly ?array $body = null)
    {
        parent::__construct($message, $status);
    }
}
