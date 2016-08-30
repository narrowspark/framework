<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Exceptions;

use InvalidArgumentException;
use Interop\Container\Exception\ContainerException as InteropContainerException;

class ContainerException extends InvalidArgumentException implements InteropContainerException
{
}
