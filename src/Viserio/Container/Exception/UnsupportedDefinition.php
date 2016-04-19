<?php
namespace Viserio\Container\Exception;

use Interop\Container\Exception\NotFoundException;

class UnsupportedDefinition extends \Exception implements NotFoundException
{
    public static function fromDefinition($definition)
    {
        return new self(sprintf('%s is not a supported definition', get_class($definition)));
    }
}
