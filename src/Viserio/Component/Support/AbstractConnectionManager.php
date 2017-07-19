<?php
declare(strict_types=1);
namespace Viserio\Component\Support;

use Closure;
use InvalidArgumentException;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\Support\ConnectionManager as ConnectionManagerContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

abstract class AbstractConnectionManager implements
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract,
    ConnectionManagerContract
{
    use ContainerAwareTrait;
    use OptionsResolverTrait;

    /**
     * Resolved options.
     *
     * @var array
     */
    protected $resolvedOptions;

    /**
     * The active connection instances.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * The custom connection resolvers.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * Create a new connection manager instance.
     *
     * @param iterable|\Psr\Container\ContainerInterface $data
     */
    public function __construct($data)
    {
        $this->resolvedOptions = self::resolveOptions($data);
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return \call_user_func_array([$this->getConnection(), $method], $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', static::getConfigName()];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return ['connections'];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return $this->resolvedOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(?string $name = null)
    {
        $name = $name ?? $this->getDefaultConnection();

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
    public function reconnect(string $name = null)
    {
        $name = $name ?? $this->getDefaultConnection();

        $this->disconnect($name);

        return $this->getConnection($name);
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect(string $name = null): void
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
    public function extend(string $driver, Closure $callback): void
    {
        $this->extensions[$driver] = $callback->bindTo($this, $this);
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
    public function hasConnection(string $connect): bool
    {
        $method = 'create' . Str::studly($connect) . 'Connection';

        return \method_exists($this, $method) || isset($this->extensions[$connect]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionConfig(string $name): array
    {
        $name = $name ?? $this->getDefaultConnection();

        $connections = $this->resolvedOptions['connections'];

        if (isset($connections[$name]) && \is_array($connections[$name])) {
            $config         = $connections[$name];
            $config['name'] = $name;

            return $config;
        }

        return ['name' => $name];
    }

    /**
     * {@inheritdoc}
     */
    public function createConnection(array $config)
    {
        $method = 'create' . Str::studly($config['name']) . 'Connection';

        if (isset($this->extensions[$config['name']])) {
            return $this->callCustomCreator($config['name'], $config);
        } elseif (\method_exists($this, $method)) {
            return $this->$method($config);
        }

        throw new InvalidArgumentException(\sprintf('Connection [%s] not supported.', $config['name']));
    }

    /**
     * Call a custom connection creator.
     *
     * @param string $connection
     * @param array  $config
     *
     * @return mixed
     */
    protected function callCustomCreator(string $connection, array $config = [])
    {
        return $this->extensions[$connection]($config);
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    abstract protected static function getConfigName(): string;
}
