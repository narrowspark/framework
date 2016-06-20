<?php
namespace Viserio\Support;

use Closure;
use RuntimeException;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Support\Traits\ContainerAwareTrait;

abstract class Manager
{
    use ContainerAwareTrait;

    /**
     * The config instance.
     *
     * @var ConfigContract
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
     * @param ConfigContract $config
     *
     * @return self
     */
    public function setConfig(ConfigContract $config): Manager
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return ConfigContract
     */
    public function getConfig(): ConfigContract
    {
        return $this->config;
    }

    /**
     * Set the default cache driver name.
     *
     * @param string $name
     */
    abstract public function setDefaultDriver(string $name);

    /**
     * Get the default driver name.
     *
     * @return string
     */
    abstract public function getDefaultDriver(): string;

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
        $driver = $driver ?: $this->getDefaultDriver();

        if (! $this->hasDriver($driver)) {
            throw new RuntimeException(
                sprintf('The driver [%s] is not supported.', $driver)
            );
        }

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver, $config);
        }

        return $this->drivers[$driver];
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string   $driver
     * @param \Closure $callback
     *
     * @return self
     */
    public function extend(string $driver, Closure $callback): Manager
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);

        return $this;
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
