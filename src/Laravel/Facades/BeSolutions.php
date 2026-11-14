<?php

declare(strict_types=1);

namespace BeSolutions\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * BeSolutions::emails()->send([...]) is not how facades proxy properties, so use the resources through the client:
 *   BeSolutions::client()->emails->send([...])   or inject \BeSolutions\Client.
 *
 * @method static \BeSolutions\Client client()
 * @method static array createAccessToken(array $input)
 * @method static array usage(?int $from = null, ?int $to = null)
 */
final class BeSolutions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'besolutions';
    }

    /** The underlying client, to reach its resources: BeSolutions::client()->sms->send([...]). */
    public static function client(): \BeSolutions\Client
    {
        return static::getFacadeRoot();
    }
}
