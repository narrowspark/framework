<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Container\Traits;

use ReflectionClass;
use ReflectionException;
use Viserio\Component\Container\ClassHelper;

trait TypeNotFoundMessageCreatorTrait
{
    /** @var array */
    protected $types;

    /** @var array */
    protected $ambiguousServiceTypes;

    /**
     * Populates the list of available types.
     *
     * @return void
     */
    abstract protected function populateAvailableTypes(): void;

    /**
     * Populates the list of available types for a given definition.
     *
     * @param string $id
     * @param string $value
     *
     * @return void
     */
    protected function populateAvailableType(string $id, string $value): void
    {
        $reflectionClass = $this->getClassReflector($value, false);

        if ($reflectionClass === null) {
            return;
        }

        foreach ($reflectionClass->getInterfaces() as $reflectionInterface) {
            $this->setType($reflectionInterface->getName(), $id);
        }

        do {
            $this->setType($reflectionClass->getName(), $id);
        } while ($reflectionClass = $reflectionClass->getParentClass());
    }

    /**
     * Get the reflection object for the object or class name.
     *
     * @param string $class
     * @param bool   $throw
     *
     * @throws ReflectionException
     *
     * @return null|ReflectionClass
     */
    abstract protected function getClassReflector(string $class, bool $throw = true): ?ReflectionClass;

    /**
     * @return array
     */
    abstract protected function getServicesAndAliases(): array;

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id identifier of the entry to look for
     *
     * @return bool
     */
    abstract protected function has(string $id): bool;

    /**
     * Generate a error message for not found classes or interfaces.
     *
     * @param string $type
     * @param string $label
     * @param string $currentId
     *
     * @throws ReflectionException
     *
     * @return string
     */
    private function createTypeNotFoundMessage(string $type, string $label, string $currentId): string
    {
        $reflection = $this->getClassReflector($type, false);

        if ($reflection === null) {
            // either $type does not exist or a parent class does not exist
            try {
                ClassHelper::isClassLoaded($type);

                $parentMsg = false;
            } catch (ReflectionException $exception) {
                $parentMsg = $exception->getMessage();
            }

            $message = \sprintf('has type [%s] but this class %s.', $type, $parentMsg !== false ? \sprintf('is missing a parent class (%s)', $parentMsg) : 'is properly not autoloaded or doesn\'t exist.');
        } else {
            $alternatives = $this->createTypeAlternatives($type);
            $message = \sprintf('references %s [%s] but no such service exists.%s', $reflection->isInterface() ? 'interface' : 'class', $type, $alternatives);

            if ($alternatives === null && $reflection->isInterface()) {
                $message .= ' Did you create a class that implements this interface?';
            }
        }

        return \sprintf('Cannot autowire service [%s]: %s %s', $currentId, $label, $message);
    }

    /**
     * @param string $type
     *
     * @return null|string
     */
    private function createTypeAlternatives(string $type): ?string
    {
        // try suggesting available aliases first
        $message = $this->getAliasesSuggestionForType($type);

        if ($message !== null) {
            return ' ' . $message;
        }

        $servicesAndAliases = $this->getServicesAndAliases();

        $key = \array_search(\strtolower($type), \array_map('\strtolower', $servicesAndAliases), true);

        if ($key !== false && ! $this->has($type)) {
            return \sprintf(' Did you mean [%s]?', $servicesAndAliases[$key]);
        }

        if (\array_key_exists($type, $this->ambiguousServiceTypes)) {
            $message = \sprintf('one of these existing services: ["%s"]', \implode('", "', $this->ambiguousServiceTypes[$type]));
        } elseif (\array_key_exists($type, $this->types)) {
            $message = \sprintf('the existing [%s] service', $this->types[$type]);
        } else {
            return null;
        }

        return \sprintf(' You should maybe alias this %s to %s.', \class_exists($type, false) ? 'class' : 'interface', $message);
    }

    /**
     * @param string      $type
     * @param null|string $extraContext
     *
     * @return null|string
     */
    private function getAliasesSuggestionForType(string $type, ?string $extraContext = null): ?string
    {
        $aliases = [];

        foreach (\array_merge(\class_parents($type), \class_implements($type)) as $parent) {
            if ($this->has($parent)) {
                $aliases[] = $parent;
            }
        }

        $extraContext = $extraContext !== null ? ' ' . $extraContext : '';

        if (1 < $len = \count($aliases)) {
            $message = \sprintf('Try changing the type-hint%s to one of its parents: ', $extraContext);

            $i = 0;

            for ($i, --$len; $i < $len; $i++) {
                $message .= \sprintf('%s [%s], ', \class_exists($aliases[$i], false) ? 'class' : 'interface', $aliases[$i]);
            }

            return $message . \sprintf('or %s [%s].', \class_exists($aliases[$i], false) ? 'class' : 'interface', $aliases[$i]);
        }

        if (\count($aliases) !== 0) {
            return \sprintf('Try changing the type-hint%s to [%s] instead.', $extraContext, $aliases[0]);
        }

        return null;
    }

    /**
     * Associates a type and a service id if applicable.
     *
     * @param string $type
     * @param string $id
     *
     * @return void
     */
    private function setType(string $type, string $id): void
    {
        // is this already a type/class that is known to match multiple services?
        if (\array_key_exists($type, $this->ambiguousServiceTypes)) {
            $this->ambiguousServiceTypes[$type][] = $id;

            return;
        }

        // check to make sure the type doesn't match multiple services
        if (! \array_key_exists($type, $this->types) || $this->types[$type] === $id) {
            $this->types[$type] = $id;

            return;
        }

        // keep an array of all services matching this type
        if (! \array_key_exists($type, $this->ambiguousServiceTypes)) {
            $this->ambiguousServiceTypes[$type] = [$this->types[$type]];

            unset($this->types[$type]);
        }

        $this->ambiguousServiceTypes[$type][] = $id;
    }
}
