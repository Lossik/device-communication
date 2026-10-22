<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * A programming error: the operation is not possible with this transport (`listen` over a transport without
 * streaming, a Query the transport cannot translate). Thrown before anything is sent; the transport stays open.
 */
class UnsupportedOperationException extends \LogicException
{
}
