<?php
namespace Viserio\Filesystem;

use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use Narrowspark\Arr\StaticArr as Arr;
use RuntimeException;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Support\AbstractManager;

class FilesystemManager extends AbstractManager
{
    /**
     * All supported drivers.
     *
     * @var array
     */
    protected $supportedDrivers = [
        'awss3',
        'dropbox',
        'ftp',
        'gridfs',
        'local',
        'null',
        'rackspace',
        'sftp',
        'vfs',
        'webdav',
        'zip',
    ];

    /**
     * Create a new filesystem manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager $config
     */
    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get($this->getConfigName() . '.default', 'local');
    }

    /**
     * {@inheritdoc}
     */
    public function driver(string $driver = null, array $options = [])
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
            $this->drivers[$driver] = $this->adapt($this->createDriver($driver, $options));
        }

        return $this->drivers[$driver];
    }

    /**
     * Get the configuration for a connection.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getConnectionConfig(string $name): array
    {
        $name = $name ?: $this->getDefaultDriver();

        $connections = $this->config->get($this->getConfigName() . '.connections');

        if (! is_array($config = Arr::get($connections, $name)) && ! $config) {
            throw new InvalidArgumentException("Adapter [$name] not configured.");
        }

        if (is_string($cache = Arr::get($config, 'cache'))) {
            $config['cache'] = $this->getCacheConfig($cache);
        }

        $config['name'] = $name;

        return $config;
    }

    /**
     * Get the cache configuration.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getCacheConfig(string $name): array
    {
        $cache = $this->config->get($this->getConfigName() . '.cache');

        if (! is_array($config = Arr::get($cache, $name)) && ! $config) {
            throw new InvalidArgumentException("Cache [$name] not configured.");
        }

        $config['name'] = $name;

        return $config;
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'flysystem';
    }

    /**
     * Adapt the filesystem implementation.
     *
     * @param \League\Flysystem\AdapterInterface $filesystem
     *
     * @return \Viserio\Contracts\Filesystem\Filesystem
     */
    protected function adapt(AdapterInterface $filesystem): \Viserio\Contracts\Filesystem\Filesystem
    {
        return new FilesystemAdapter($filesystem);
    }

    protected function createAwss3Driver(array $options)
    {
        return (new Adapters\AwsS3Connector())->connect($options);
    }

    protected function createDropboxDriver(array $options)
    {
        return (new Adapters\DropboxConnector())->connect($options);
    }

    protected function createFtpDriver(array $options)
    {
        return (new Adapters\FtpConnector())->connect($options);
    }

    protected function createGridfsDriver(array $options)
    {
        return (new Adapters\GridFSConnector())->connect($options);
    }

    protected function createLocalDriver(array $options)
    {
        return (new Adapters\LocalConnector())->connect($options);
    }

    protected function createNullDriver()
    {
        return (new Adapters\NullConnector())->connect([]);
    }

    protected function createRackspaceDriver(array $options)
    {
        return (new Adapters\RackspaceConnector())->connect($options);
    }

    protected function createSftpDriver(array $options)
    {
        return (new Adapters\SftpConnector())->connect($options);
    }

    protected function createVfsDriver(array $options)
    {
        return (new Adapters\VfsConnector())->connect($options);
    }

    protected function createWebdavDriver(array $options)
    {
        return (new Adapters\WebDavConnector())->connect($options);
    }

    protected function createZipDriver(array $options)
    {
        return (new Adapters\ZipConnector())->connect($options);
    }
}
