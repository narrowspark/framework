<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Session\Exceptions;

use UnexpectedValueException;

/**
 * Raised when a user has performed an operation that should be considered
 * suspicious from a security perspective.
 */
class SuspiciousOperationException extends UnexpectedValueException
{
}
