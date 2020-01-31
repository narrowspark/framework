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

namespace Viserio\Component\Config\Container\Definition;

final class ConfigDefinition
{
    /**
     * Name of the config aware class.
     *
     * @var string
     */
    private string $class;

    /** @var null|string */
    private ?string $id;

    /**
     * Create a new ConfigDefinition instance.
     *
     * @param string      $configClass
     * @param null|string $configId
     */
    public function __construct(string $configClass, ?string $configId = null)
    {
        $this->class = $configClass;
        $this->id = $configId;
    }

    /**
     * Return the config aware class.
     *
     * @return string
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
}
