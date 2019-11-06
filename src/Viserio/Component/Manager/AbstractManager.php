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

namespace Viserio\Component\Manager;

use ArrayAccess;
use Viserio\Component\Manager\Traits\ManagerTrait;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Contract\Manager\Manager as ManagerContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;

abstract class AbstractManager implements ManagerContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionContract
{
    use ContainerAwareTrait;
    use OptionsResolverTrait;
    use ManagerTrait;

    /**
     * Default name for the drivers config.
     *
     * @var string
     */
    protected const CONFIG_LIST_NAME = 'drivers';

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * Create a new manager instance.
     *
     * @param array|ArrayAccess $config
     */
    public function __construct($config)
    {
        $this->resolvedOptions = static::resolveOptions($config);
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->getDriver()->{$method}(...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDriver(): string
    {
        return $this->resolvedOptions['default'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultDriver(string $name): void
    {
        $this->resolvedOptions['default'] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDriver(?string $driver = null)
    {
        $driver = $driver ?? $this->getDefaultDriver();

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver(
                $this->getDriverConfig($driver)
            );
        }

        return $this->drivers[$driver];
    }

    /**
     * {@inheritdoc}
     */
    public function hasDriver(string $driver): bool
    {
        $method = 'create' . static::studly($driver) . 'Driver';

        return \method_exists($this, $method) || isset($this->extensions[$driver]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDriverConfig(string $name): array
    {
        $name = $name ?? $this->getDefaultDriver();

        return $this->getConfigFromName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function createDriver(array $config)
    {
        $method = 'create' . static::studly($config['name']) . 'Driver';

        return $this->create($config, $method, 'Driver [%s] is not supported.');
    }
}
