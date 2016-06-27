<?php
namespace Viserio\Support;

use Closure;
use RuntimeException;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Support\Traits\ContainerAwareTrait;

abstract class AbstractManager
{
    use ContainerAwareTrait;

    /**
     * The config instance.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * All supported drivers.
     *
     * @var array
     */
    protected $supportedDrivers = [];

    /**
     * Create a new manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager $config
     *
     * @return void
     */
    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
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
        return call_user_func_array([$this->driver(), $method], $parameters);
    }

    /**
     * Set a config manager
     *
     * @param \Viserio\Contracts\Config\Manager $config
     *
     * @return void
     */
    public function setConfig(ConfigContract $config)
    {
        $this->config = $config;
    }

    /**
     * Get config
     *
     * @return \Viserio\Contracts\Config\Manager
     */
    public function getConfig(): ConfigContract
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
        return $this->config->get($this->getConfigName() . '.default');
    }

    /**
     * Set the default driver name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setDefaultDriver(string $name)
    {
        $this->config->set($this->getConfigName() . '.default', $name);
    }

    /**
     * Builder.
     *
     * @param string|null $driver  The cache driver to use
     * @param array       $config
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function driver(string $driver = null, array $config = [])
    {
        $driver = $driver ?? $this->getDefaultDriver();

        if (! $this->hasDriver($driver)) {
            throw new RuntimeException(
                sprintf('The driver [%s] is not supported.', $driver)
            );
        }

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->drivers[$driver])) {
            $settings = array_merge(
                $this->getDriverConfig($driver),
                $config
            );

            $this->drivers[$driver] = $this->createDriver($driver, $settings);
        }

        return $this->drivers[$driver];
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string   $driver
     * @param \Closure $callback
     *
     * @return void
     */
    public function extend(string $driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);
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
        return isset($this->supportedDrivers[$driver]) ||
            in_array($driver, $this->supportedDrivers, true) ||
            isset($this->customCreators[$driver]);
    }

    /**
     * Get the configuration for a driver.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getDriverConfig(string $name): array
    {
        $name = $name ?? $this->getDefaultDriver();

        $drivers = $this->config->get($this->getConfigName() . '.drivers');

        if (!isset($drivers[$name]) && !is_array($drivers[$name])) {
            return [
                'name' => $name
            ];
        }

        $config = $drivers[$name];
        $config['name'] = $name;

        return $config;
    }

    /**
     * Create a new driver instance.
     *
     * @param string $driver
     * @param array  $config
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    protected function createDriver(string $driver, array $config)
    {
        $method = 'create' . Str::studly($driver) . 'Driver';

        // We'll check to see if a creator method exists for the given driver. If not we
        // will check for a custom driver creator, which allows developers to create
        // drivers using their own customized driver creator Closure to create it.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver, $config);
        } elseif (method_exists($this, $method)) {
            return $this->$method($config);
        } elseif (isset($this->supportedDrivers[$driver]) && class_exists($this->supportedDrivers[$driver])) {
            return new $this->supportedDrivers[$driver]();
        }

        throw new RuntimeException(sprintf('Driver [%s] not supported.', $driver));
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
        return $this->customCreators[$driver]($config);
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    abstract protected function getConfigName(): string;
}
