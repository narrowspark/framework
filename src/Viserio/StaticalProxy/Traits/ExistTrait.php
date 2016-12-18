<?php
declare(strict_types=1);
namespace Viserio\StaticalProxy\Traits;

trait ExistTrait
{
    /**
     * Checks various object types for existence.
     *
     * @param mixed $object
     * @param bool  $autoload
     *
     * @return bool
     */
    protected function exists($object, bool $autoload = true): bool
    {
        return class_exists($object, $autoload) ||
            interface_exists($object, $autoload) ||
            trait_exists($object, $autoload);
    }
}
