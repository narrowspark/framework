<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Exceptions;

use Exception;
use Interop\Container\Exception\ContainerException as InteropContainerException;

class EntryNotFoundException extends Exception implements InteropContainerException
{
    public static function fromId(string $id)
    {
        return new self(sprintf('The container entry "%s" was not found', $id));
    }
}
