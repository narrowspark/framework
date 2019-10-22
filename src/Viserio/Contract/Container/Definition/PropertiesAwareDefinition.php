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

interface PropertiesAwareDefinition
{
    /**
     * Gets the properties to define when creating the service.
     *
     * @return array<string => null, string => \ReflectionProperty, string => mixed>
     */
    public function getProperties(): array;

    /**
     * Sets the properties to define when creating the service.
     *
     * @param array $properties
     *
     * @return static
     */
    public function setProperties(array $properties);

    /**
     * Sets a specific property.
     *
     * @param string $key
     * @param mixed  $value
     * @param bool   $static
     *
     * @return static
     */
    public function setProperty(string $key, $value, bool $static = false);
}
