<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * Marker of every runtime error of a transport. All of them extend `\RuntimeException`, so `catch (\Exception)`
 * catches them too. The numeric error codes of 2.x are gone; tell the errors apart by class.
 *
 * UnsupportedOperationException is a programming error (`\LogicException`) and does not implement this marker.
 * Neither does the `\InvalidArgumentException` a transport may throw before any I/O for input it cannot encode (a
 * value its charset cannot represent); every other runtime failure of a transport implements it.
 */
interface CommunicationException extends \Throwable
{
}
