<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * The result of one command: the returned rows and the `ret` value (the ID of an added item), if any.
 */
final class Result
{
    /**
     * @param list<array<string, string>> $rows
     */
    public function __construct(public readonly array $rows, public readonly ?string $ret = null)
    {
    }
}
