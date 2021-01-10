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

namespace Viserio\Component\Container\Definition\Traits;

/**
 * @property array<string, bool> $changes
 *
 * @internal
 */
trait PropertiesAwareTrait
{
    /**
     * The class properties.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * {@inheritdoc}
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperties(array $properties)
    {
        $this->properties = [];

        foreach ($properties as $name => $call) {
            if (\is_array($call) && isset($call[0])) {
                $this->setProperty($name, $call[0], $call[1] ?? false);
            } else {
                $this->setProperty($name, $call);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty(string $key, $value, bool $static = false)
    {
        $this->changes['properties'] = true;

        $this->properties[$key] = [$value, $static];

        return $this;
    }
}
