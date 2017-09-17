<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Encryption\Exception;

use RuntimeException as BaseRuntimeException;

class CannotPerformOperationException extends BaseRuntimeException implements Exception
{
}
