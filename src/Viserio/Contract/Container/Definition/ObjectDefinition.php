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

namespace Viserio\Contract\Container\Definition;

interface ObjectDefinition extends ArgumentAwareDefinition, AutowiredAwareDefinition, DecoratorAwareDefinition, Definition, MethodCallsAwareDefinition, PropertiesAwareDefinition, TagAwareDefinition
{
    /**
     * Get the class of this definition.
     *
     * @return string
     */
    public function getClass(): string;

    /**
     * Set the class of the definition.
     *
     * @param object|string $class
     */
    public function setClass($class): void;
}
