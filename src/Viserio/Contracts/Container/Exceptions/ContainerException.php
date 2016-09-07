<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Exceptions;

use Interop\Container\Exception\ContainerException as InteropContainerException;
use InvalidArgumentException;

class ContainerException extends InvalidArgumentException implements InteropContainerException
{
}
