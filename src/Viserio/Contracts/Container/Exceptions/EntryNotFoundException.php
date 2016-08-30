<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Exceptions;

use Exception;
use Interop\Container\Exception\ContainerException;

class EntryNotFoundException extends Exception implements ContainerException
{
    public static function fromId(string $id)
    {
        return new self(sprintf('The container entry "%s" was not found', $id));
    }
}
