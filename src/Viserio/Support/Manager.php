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
     * Set a config manager
     *
     * @param ConfigContract $config
     *
     * @return self
     */
    public function setConfig(ConfigContract $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return ConfigContract
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the default cache driver name.
     *
     * @param string $name
     */
    abstract public function setDefaultDriver($name);

    /**
     * Get the default driver name.
     *
     * @return string
     */
    abstract public function getDefaultDriver();

    /**
     * Builder.
     *
     * @param string|null $driver  The cache driver to use
     * @param array       $options
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function driver($driver = null, array $options = [])
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (!$this->hasDriver($driver)) {
            throw new RuntimeException(
                sprintf('The driver [%s] is not supported.', $driver)
            );
        }

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver, $options);
        }

        return $this->drivers[$driver];
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string   $driver
     * @param \Closure $callback
     *
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Get all of the created "drivers".
     *
     * @return array
     */
    public function getDrivers()
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
    public function hasDriver($driver)
    {
        return isset($this->supportedDrivers[$driver]) ||
            in_array($driver, $this->supportedDrivers, true) ||
            isset($this->customCreators[$driver]);
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
        return call_user_func_array([$this->driver(), $method], $parameters);
    }

    /**
     * Create a new driver instance.
     *
     * @param string $driver
     * @param array  $options
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    protected function createDriver($driver, array $options)
    {
        $method = 'create' . Str::studly($driver) . 'Driver';
        $options = array_filter($options);

        // We'll check to see if a creator method exists for the given driver. If not we
        // will check for a custom driver creator, which allows developers to create
        // drivers using their own customized driver creator Closure to create it.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver, $options);
        } elseif (method_exists($this, $method)) {
            return empty($options) ? $this->$method() : $this->$method($options);
        } elseif (isset($this->supportedDrivers[$driver]) && class_exists($this->supportedDrivers[$driver])) {
            return new $this->supportedDrivers[$driver]();
        }

        throw new RuntimeException(sprintf('Driver [%s] not supported.', $driver));
    }

    /**
     * Call a custom driver creator.
     *
     * @param string $driver
     * @param array  $options
     *
     * @return mixed
     */
    protected function callCustomCreator($driver, array $options = [])
    {
        return $this->customCreators[$driver]($options);
    }
}
