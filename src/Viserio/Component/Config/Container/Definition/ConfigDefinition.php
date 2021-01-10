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

namespace Viserio\Component\Config\Container\Definition;

final class ConfigDefinition
{
    /**
     * Name of the config aware class.
     */
    private string $class;

    /** @var null|string */
    private ?string $id;

    private ?string $key = null;

    /**
     * Create a new ConfigDefinition instance.
     */
    public function __construct(string $configClass, ?string $configId = null)
    {
        $this->class = $configClass;
        $this->id = $configId;
    }

    /**
     * Return the config aware class.
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get config id.
     *
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @return $this
     */
    public function setKey(?string $key): self
    {
        $this->key = $key;

        return $this;
    }
}
