<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * Marker of every runtime error of a transport. All of them extend `\RuntimeException`, so `catch (\Exception)`
 * catches them too. The numeric error codes of 2.x are gone; tell the errors apart by class.
 *
 * UnsupportedOperationException is a programming error (`\LogicException`) and does not implement this marker.
 */
interface CommunicationException extends \Throwable
{
}
