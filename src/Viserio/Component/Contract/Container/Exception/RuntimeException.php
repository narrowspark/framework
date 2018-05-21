<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException implements NotFoundExceptionInterface
{
}
