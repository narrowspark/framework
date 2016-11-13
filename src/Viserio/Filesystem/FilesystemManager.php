<?php
declare(strict_types=1);
namespace Viserio\Filesystem;

use Defuse\Crypto\Key;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Cached\CachedAdapter;
use Narrowspark\Arr\Arr;
use Viserio\Contracts\Cache\Traits\CacheAwareTrait;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Filesystem\Cache\CachedFactory;
use Viserio\Filesystem\Encryption\EncryptionWrapper;
use Viserio\Support\AbstractConnectionManager;

class FilesystemManager extends AbstractConnectionManager
{
    use CacheAwareTrait;

    /**
     * Get a crypted aware connection instance.
     *
     * @param string|null       $name
     * @param Defuse\Crypto\Key $key
     *
     * @return mixed
     */
    public function cryptedConnection(string $name = null, Key $key)
    {
        return new EncryptionWrapper($this->connection($name), $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConnection(): string
    {
        return $this->config->get($this->getConfigName() . '.default', 'local');
    }

    /**
     * Get the clean flysystem adapter.
     *
     * @param string|null $name
     *
     * @return \League\Flysystem\AdapterInterface
     */
    public function getFlysystemAdapter(string $name = null): AdapterInterface
    {
        return parent::connection($name);
    }

    /**
     * {@inheritdoc}
     */
    public function connection(string $name = null)
    {
        $name = $name ?? $this->getDefaultConnection();

        if (! isset($this->connections[$name])) {
            $config = $this->getConnectionConfig($name);

            $this->connections[$name] = [
                'connection' => $this->createConnection($config),
                'config' => $config,
            ];
        }

        return $this->adapt(
            $this->connections[$name]['connection'],
            $this->connections[$name]['config']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionConfig(string $name): array
    {
        $config = parent::getConnectionConfig($name);

        if (is_string($cacheName = Arr::get($config, 'cache'))) {
            $config['cache'] = $this->getCacheConfig($cacheName);
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'filesystem';
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
        $cache = $this->config->get($this->getConfigName() . '.cached');

        if (! is_array($config = Arr::get($cache, $name)) && ! $config) {
            throw new InvalidArgumentException(sprintf('Cache [%s] not configured.', $name));
        }

        $config['name'] = $name;

        return $config;
    }

    /**
     * Adapt the filesystem implementation.
     *
     * @param \League\Flysystem\AdapterInterface $adapter
     * @param array                              $config
     *
     * @return \Viserio\Contracts\Filesystem\Filesystem
     */
    protected function adapt(AdapterInterface $adapter, array $config): FilesystemContract
    {
        if (isset($config['cache']) && is_array($config['cache'])) {
            $cacheFactory = new CachedFactory($this, $this->cache);

            $adapter = new CachedAdapter($adapter, $cacheFactory->connection($config));
        }

        $filesystemAdapter = new FilesystemAdapter($adapter);

        return $filesystemAdapter;
    }

    /**
     * Create an instance of the Awss3 connection.
     *
     * @param array $config
     */
    protected function createAwss3Connection(array $config)
    {
        return (new Adapters\AwsS3Connector())->connect($config);
    }

    /**
     * Create an instance of the Dropbox connection.
     *
     * @param array $config
     */
    protected function createDropboxConnection(array $config)
    {
        return (new Adapters\DropboxConnector())->connect($config);
    }

    /**
     * Create an instance of the Ftp connection.
     *
     * @param array $config
     */
    protected function createFtpConnection(array $config)
    {
        return (new Adapters\FtpConnector())->connect($config);
    }

    /**
     * Create an instance of the Gridfs connection.
     *
     * @param array $config
     */
    protected function createGridfsConnection(array $config)
    {
        return (new Adapters\GridFSConnector())->connect($config);
    }

    /**
     * Create an instance of the Local connection.
     *
     * @param array $config
     */
    protected function createLocalConnection(array $config)
    {
        return (new Adapters\LocalConnector())->connect($config);
    }

    /**
     * Create an instance of the Null connection.
     *
     * @param array $config
     */
    protected function createNullConnection(array $config)
    {
        return (new Adapters\NullConnector())->connect([]);
    }

    /**
     * Create an instance of the Rackspace connection.
     *
     * @param array $config
     */
    protected function createRackspaceConnection(array $config)
    {
        return (new Adapters\RackspaceConnector())->connect($config);
    }

    /**
     * Create an instance of the Sftp connection.
     *
     * @param array $config
     */
    protected function createSftpConnection(array $config)
    {
        return (new Adapters\SftpConnector())->connect($config);
    }

    /**
     * Create an instance of the Vfs connection.
     *
     * @param array $config
     */
    protected function createVfsConnection(array $config)
    {
        return (new Adapters\VfsConnector())->connect($config);
    }

    /**
     * Create an instance of the WebDav connection.
     *
     * @param array $config
     */
    protected function createWebdavConnection(array $config)
    {
        return (new Adapters\WebDavConnector())->connect($config);
    }

    /**
     * Create an instance of the Zip connection.
     *
     * @param array $config
     */
    protected function createZipConnection(array $config)
    {
        return (new Adapters\ZipConnector())->connect($config);
    }
}
