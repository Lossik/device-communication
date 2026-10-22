<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * A time limit ran out (connect, idle silence, total response time, cancel). The transport is closed.
 */
class TimeoutException extends ConnectionException
{
    /**
     * @param string $limit which limit ran out, named as the transport's option (e.g. 'connectTimeout',
     *                      'idleTimeout', 'responseTimeout', 'cancelTimeout')
     */
    public function __construct(string $message, public readonly string $limit, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
