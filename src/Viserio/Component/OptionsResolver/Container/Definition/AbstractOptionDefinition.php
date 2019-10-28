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

use ReflectionClass;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\OptionsResolver\Exception\InvalidArgumentException;
use Viserio\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;

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

    /** @var string */
    protected static $interfaceCheckName = RequiresConfigContract::class;

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

    /** @var \ReflectionClass */
    protected $reflection;

    /*private
     * Helper abstract class to create Option Definitions.
     *
     * @param string      $configClass
     * @param null|string $configId
     */
    public function __construct(string $configClass, string $configId = null)
    {
        $this->reflection = new ReflectionClass($configClass);

        if (! $this->reflection->implementsInterface(static::$interfaceCheckName)) {
            throw new InvalidArgumentException(\sprintf('Provided class [%s] didn\'t implement the [%s] interface or one of the parent interfaces.', $configClass, static::$interfaceCheckName));
        }

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
     * Returns reflection of given config class.
     *
     * @return \ReflectionClass
     */
    public function getReflection(): ReflectionClass
    {
        return $this->reflection;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return self::resolveOptions($this->config, $this->configId);
    }
}
