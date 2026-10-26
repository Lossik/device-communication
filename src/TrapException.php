<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * The device refused the command (`!trap`). The response is read to its end, so the transport stays open.
 */
class TrapException extends \RuntimeException implements CommunicationException
{
    /**
     * @param string $message the message of the (first) trap
     * @param int|null $category the `category` of the (first) trap; null when the device sends none
     * @param list<array<string, string>> $traps all traps of the response, with their attributes
     */
    public function __construct(
        string $message,
        public readonly ?int $category = null,
        private readonly array $traps = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return list<array<string, string>>
     */
    public function traps(): array
    {
        return $this->traps;
    }
}
