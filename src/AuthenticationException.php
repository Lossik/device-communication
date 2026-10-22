<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * The device rejected the user name or password (or the response to the MD5 challenge of RouterOS before 6.43).
 */
class AuthenticationException extends \RuntimeException implements CommunicationException
{
}
