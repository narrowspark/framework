<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Session\Exception;

use UnexpectedValueException;

/**
 * Raised when a user has performed an operation that should be considered
 * suspicious from a security perspective.
 */
class SuspiciousOperationException extends UnexpectedValueException implements Exception
{
}
