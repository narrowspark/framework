<?php
namespace Brainwave\Filesystem;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Contracts\Config\Manager as ConfigContract;
use Brainwave\Contracts\Filesystem\FilesystemManager as Manager;
use Brainwave\Filesystem\Adapters\ConnectionFactory;
use League\Flysystem\AdapterInterface;

/**
 * FilesystemManager.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.3-dev
 */
class FilesystemManager implements Manager
{
    /**
     * Container instance.
     *
     * @var \Brainwave\Contracts\Config\Manager
     */
    protected $config;

    /**
     * The factory instance.
     *
     * @var \Brainwave\Filesystem\Adapters\ConnectionFactory
     */
    protected $factory;

    /**
     * The array of resolved filesystem drivers.
     *
     * @var array
     */
    protected $disks = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new filesystem manager instance.
     *
     * @param \Brainwave\Contracts\Config\Manager              $config
     * @param \Brainwave\Filesystem\Adapters\ConnectionFactory $factory
     */
    public function __construct(ConfigContract $config, ConnectionFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * Get an OAuth provider implementation.
     *
     * @param string|null $name
     *
     * @return \Brainwave\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->disks[$name] = $this->get($name);
    }

    /**
     * Attempt to get the disk from the local cache.
     *
     * @param string $name
     *
     * @return \Brainwave\Contracts\Filesystem\Filesystem
     */
    protected function get($name)
    {
        return isset($this->disks[$name]) ? $this->disks[$name] : $this->resolve($name);
    }

    /**
     * Resolve the given disk.
     *
     * @param string $name
     *
     * @return FilesystemAdapter
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        } else {
            return $this->adapt($this->factory->make($config));
        }
    }

    /**
     * Adapt the filesystem implementation.
     *
     * @param \League\Flysystem\AdapterInterface $filesystem
     *
     * @return \Brainwave\Contracts\Filesystem\Filesystem
     */
    protected function adapt(AdapterInterface $filesystem)
    {
        return new FilesystemAdapter($filesystem);
    }

    /**
     * Get the filesystem connection configuration.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->config->get(sprintf('filesystems::disks.%s', $name));
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('filesystems::default');
    }

    /**
     * Call a custom driver creator.
     *
     * @param array $config
     *
     * @return \Brainwave\Contracts\Filesystem\Filesystem
     */
    protected function callCustomCreator(array $config)
    {
        $driver = $this->customCreators[$config['driver']]($config);

        if ($driver instanceof AdapterInterface) {
            return $this->adapt($driver);
        } else {
            return $driver;
        }
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string   $driver
     * @param \Closure $callback
     *
     * @return $this
     */
    public function extend($driver, \Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }
}
