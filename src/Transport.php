<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * A connection to a device that executes RouterOS commands (API, SSH, …).
 *
 * After a ConnectionException (or a subclass) the transport is closed and every further call throws
 * ConnectionException without any I/O. A TrapException does not close it. For type-valid input `execute()` throws
 * no `\Error`. Later needs get a new optional interface (like StreamingTransport), not a change of this one.
 */
interface Transport
{
    /**
     * @throws CommunicationException
     */
    public function execute(Command $command): Result;

    /**
     * Closes the connection. Idempotent.
     */
    public function close(): void;

    public function isOpen(): bool;
}
