<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * The device sent something the protocol does not allow (an invalid length byte, a foreign `.tag`, a sentence
 * without a type). The transport is closed.
 */
class ProtocolException extends ConnectionException
{
}
