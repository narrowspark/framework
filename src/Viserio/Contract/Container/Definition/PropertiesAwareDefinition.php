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
     * @return static
     */
    public function setProperties(array $properties);

    /**
     * Sets a specific property.
     *
     * @return static
     */
    public function setProperty(string $key, $value, bool $static = false);
}
