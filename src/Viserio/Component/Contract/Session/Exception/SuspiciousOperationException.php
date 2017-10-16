<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Session\Exception;

use UnexpectedValueException;

/**
 * Raised when a user has performed an operation that should be considered
 * suspicious from a security perspective.
 */
class SuspiciousOperationException extends UnexpectedValueException implements Exception
{
}
