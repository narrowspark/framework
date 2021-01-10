<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Container\Definition;

interface ObjectDefinition extends ArgumentAwareDefinition, AutowiredAwareDefinition, DecoratorAwareDefinition, Definition, MethodCallsAwareDefinition, PropertiesAwareDefinition, TagAwareDefinition
{
    /**
     * Get the class of this definition.
     */
    public function getClass(): string;

    /**
     * Set the class of the definition.
     *
     * @param object|string $class
     */
    public function setClass($class): void;
}
