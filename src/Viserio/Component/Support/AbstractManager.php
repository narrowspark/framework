<?php
declare(strict_types=1);
namespace Viserio\Component\Support;

use Closure;
use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;

abstract class AbstractManager implements RequiresConfig, RequiresMandatoryOptions
{
    use ContainerAwareTrait;
    use ConfigurationTrait;

    /**
     * Manager config.
     *
     * @var array|\ArrayAccess
     */
    protected $config = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * Create a new manager instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->createConfiguration($container);
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->getDriver(), $method], $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function dimensions(): iterable
    {
        return ['viserio', $this->getConfigName()];
    }

    /**
     * {@inheritdoc}
     */
    public function mandatoryOptions(): iterable
    {
        return ['drivers'];
    }

    /**
     * Get manager config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config['default'];
    }

    /**
     * Set the default driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver(string $name)
    {
        $this->config['default'] = $name;
    }

    /**
     * Get a driver instance.
     *
     * @param string|null $driver
     *
     * @return mixed
     */
    public function getDriver(string $driver = null)
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
     * Register a custom driver creator Closure.
     *
     * @param string   $driver
     * @param \Closure $callback
     */
    public function extend(string $driver, Closure $callback)
    {
        $this->extensions[$driver] = $callback->bindTo($this, $this);
    }

    /**
     * Get all of the created "drivers".
     *
     * @return array
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }

    /**
     * Check if the given driver is supported.
     *
     * @param string $driver
     *
     * @return bool
     */
    public function hasDriver(string $driver): bool
    {
        $method = 'create' . Str::studly($driver) . 'Driver';

        return method_exists($this, $method) || isset($this->extensions[$driver]);
    }

    /**
     * Get the configuration for a driver.
     *
     * @param string $name
     *
     * @return array
     */
    public function getDriverConfig(string $name): array
    {
        $name = $name ?? $this->getDefaultDriver();

        $drivers = $this->config['drivers'] ?? [];

        if (isset($drivers[$name]) && is_array($drivers[$name])) {
            $config         = $drivers[$name];
            $config['name'] = $name;

            return $config;
        }

        return ['name' => $name];
    }

    /**
     * Make a new driver instance.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function createDriver(array $config)
    {
        $method = 'create' . Str::studly($config['name']) . 'Driver';

        if (isset($this->extensions[$config['name']])) {
            return $this->callCustomCreator($config['name'], $config);
        } elseif (method_exists($this, $method)) {
            return $this->$method($config);
        }

        throw new InvalidArgumentException(sprintf('Driver [%s] not supported.', $config['name']));
    }

    /**
     * Call a custom driver creator.
     *
     * @param string $driver
     * @param array  $config
     *
     * @return mixed
     */
    protected function callCustomCreator(string $driver, array $config = [])
    {
        return $this->extensions[$driver]($config);
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    abstract protected function getConfigName(): string;

    /**
     * Create handler configuration.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @see \Viserio\Component\Exception\ErrorHandler::options()
     *
     * @return void
     */
    protected function createConfiguration(ContainerInterface $container): void
    {
        if ($container->has(RepositoryContract::class)) {
            $config = $container->get(RepositoryContract::class);
        } else {
            $config = $container->get('config');
        }

        $this->config = $this->options($config);
    }
}
