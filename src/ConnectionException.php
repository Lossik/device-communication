<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * The connection failed: the device is unreachable, the TLS handshake failed, the peer closed the connection (EOF),
 * or a command was issued on a closed transport.
 *
 * After this exception (or a subclass) the transport is closed and every further call throws ConnectionException
 * without any I/O.
 */
class ConnectionException extends \RuntimeException implements CommunicationException
{
}
