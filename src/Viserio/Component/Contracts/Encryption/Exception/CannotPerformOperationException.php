<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption\Exception;

use RuntimeException as BaseRuntimeException;

class CannotPerformOperationException extends BaseRuntimeException implements Exception
{
}
