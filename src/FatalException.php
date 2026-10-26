<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * The device ended the session (`!fatal`). The transport is closed.
 */
class FatalException extends ConnectionException
{
    /**
     * @param string $reason the reason sent by the device
     */
    public function __construct(string $message, public readonly string $reason, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
