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
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Contract\Manager\ConnectionManager as ConnectionManagerContract;

abstract class AbstractConnectionManager implements ConnectionManagerContract,
    RequiresComponentConfigContract,
    RequiresMandatoryConfigContract
{
    use ContainerAwareTrait;
    use OptionsResolverTrait;
    use ManagerTrait;

    /**
     * Default name for the connections config.
     *
     * @var string
     */
    protected const CONFIG_LIST_NAME = 'connections';

    /**
     * The active connection instances.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * Create a new connection manager instance.
     *
     * @param array|ArrayAccess $config
     */
    public function __construct($config)
    {
        $this->resolvedOptions = self::resolveOptions($config);
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->getConnection()->{$method}(...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(?string $name = null)
    {
        $name = $name ?? $this->getDefaultConnection();

        // If the given connection has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a connection created by this name, we'll just return that instance.
        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->createConnection(
                $this->getConnectionConfig($name)
            );
        }

        return $this->connections[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function reconnect(?string $name = null): object
    {
        $name = $name ?? $this->getDefaultConnection();

        $this->disconnect($name);

        return $this->getConnection($name);
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect(?string $name = null): void
    {
        $name = $name ?? $this->getDefaultConnection();

        unset($this->connections[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConnection(): string
    {
        return $this->resolvedOptions['default'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultConnection(string $name): void
    {
        $this->resolvedOptions['default'] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function hasConnection(string $connect): bool
    {
        $method = 'create' . static::studly($connect) . 'Connection';

        return \method_exists($this, $method) || isset($this->extensions[$connect]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionConfig(string $name): array
    {
        $name = $name ?? $this->getDefaultConnection();

        return $this->getConfigFromName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function createConnection(array $config)
    {
        $method = 'create' . static::studly($config['name']) . 'Connection';

        return $this->create($config, $method, 'Connection [%s] is not supported.');
    }
}
