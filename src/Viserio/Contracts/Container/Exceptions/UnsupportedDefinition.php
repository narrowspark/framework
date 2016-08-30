<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Exceptions;

use Exception;
use Interop\Container\Exception\ContainerException;

class UnsupportedDefinition extends Exception implements ContainerException
{
    public static function fromDefinition(string $definition)
    {
        return new self(sprintf('%s is not a supported definition', get_class($definition)));
    }
}
