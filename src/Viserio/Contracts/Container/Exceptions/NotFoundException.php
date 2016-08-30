<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Exceptions;

use Exception;
use Interop\Container\Exception\ContainerException;

class NotFoundException extends Exception implements ContainerException
{
}
