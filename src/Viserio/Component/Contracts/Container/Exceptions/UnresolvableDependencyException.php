<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Container\Exceptions;

use Exception;
use Interop\Container\Exception\ContainerException as InteropContainerException;

class UnresolvableDependencyException extends Exception implements InteropContainerException
{
}
