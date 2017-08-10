<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Pipeline\Exception;

use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException implements Exception
{
}
