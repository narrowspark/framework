<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing;

use ReflectionClass;

class SimpleHydrator
{
    /**
     * Creates an instance of a class filled with data.
     *
     * @param mixed $class
     * @param array $attributes
     *
     * @return object
     */
    public static function hydrate($class, array $attributes = [])
    {
        $reflection = new ReflectionClass($class);
        $instance   = $reflection->newInstanceWithoutConstructor();

        foreach ($attributes as $field => $value) {
            if ($reflection->hasProperty($field)) {
                $property = $reflection->getProperty($field);
                $property->setAccessible(true);
                $property->setValue($instance, $value);
            }
        }

        return $instance;
    }
}
