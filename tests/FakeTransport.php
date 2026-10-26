<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication\Tests;

use Lossik\DeviceCommunication\Command;
use Lossik\DeviceCommunication\Result;
use Lossik\DeviceCommunication\Transport;

/**
 * Stands in for the device: records every received Command and answers with the scripted Results in order.
 */
final class FakeTransport implements Transport
{
    /** @var list<Command> */
    public array $received = [];

    /** @var list<Result> */
    private array $results;

    public function __construct(Result ...$results)
    {
        $this->results = array_values($results);
    }

    public function execute(Command $command): Result
    {
        $this->received[] = $command;
        $result = array_shift($this->results);
        if ($result === null) {
            throw new \LogicException('FakeTransport: no scripted result left for ' . $command->verb);
        }

        return $result;
    }

    public function close(): void
    {
    }

    public function isOpen(): bool
    {
        return true;
    }
}
