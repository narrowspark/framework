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

namespace Viserio\Component\OptionsResolver\Container\Definition;

use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

abstract class AbstractOptionDefinition
{
    use OptionsResolverTrait;

    /**
     * Name of the options aware class.
     *
     * @internal
     *
     * @var string
     */
    public static $configClass;

    /**
     * Name of the options aware class.
     *
     * @var string
     */
    protected $class;

    /**
     * Array of options.
     *
     * @var array|\ArrayAccess
     */
    protected $config;

    /**
     * Used config id.
     *
     * @var null|string
     */
    protected $configId;

    /**
     * Helper abstract class to create Option Definitions.
     *
     * @param string      $configClass
     * @param null|string $configId
     */
    public function __construct(string $configClass, string $configId = null)
    {
        $this->configId = $configId;
        $this->class = $configClass;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): string
    {
        return self::$configClass;
    }

    /**
     * Return the options aware class.
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Set the config array.
     *
     * @internal
     *
     * @param array|\ArrayAccess $config
     *
     * @return void
     */
    public function setConfig($config): void
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return self::resolveOptions($this->config, $this->configId);
    }
}
