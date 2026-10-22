<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * A transport that can deliver the rows of a long-running command (`listen`, `monitor`, …) as they arrive.
 */
interface StreamingTransport extends Transport
{
    /**
     * @param callable(array<string, string>): bool $onRow returns false = cancel (/cancel)
     * @param float|null $idleTimeout silence after which the stream ends with TimeoutException; null = no limit
     * @throws CommunicationException
     */
    public function stream(Command $command, callable $onRow, ?float $idleTimeout = null): void;
}
