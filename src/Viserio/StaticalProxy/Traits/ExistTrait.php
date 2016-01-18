<?php
namespace Viserio\StaticalProxy\Traits;

trait ExistTrait
{
    /**
     * Checks various object types for existence
     *
     * @param mixed $object
     * @param bool  $autoload
     *
     * @return bool
     */
    protected function exists($object, $autoload = true)
    {
        return class_exists($object, $autoload) ||
            interface_exists($object, $autoload) ||
            trait_exists($object, $autoload);
    }
}
