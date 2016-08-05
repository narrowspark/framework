<?php
declare(strict_types=1);
namespace Viserio\Filesystem;

use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use Narrowspark\Arr\StaticArr as Arr;
use RuntimeException;
use Viserio\Contracts\{
    Config\Manager as ConfigContract,
    Filesystem\Filesystem as FilesystemContract
};
use Viserio\Support\AbstractConnectionManager;

class FilesystemManager extends AbstractConnectionManager
{
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
        return $this->adapt(parent::connection($name));
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionConfig(string $name): array
    {
        $config = parent::getConnectionConfig($name);

        if (is_string($cache = Arr::get($config, 'cache'))) {
            $config['cache'] = $this->getCacheConfig($cache);
        }

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
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'filesystem';
    }

    /**
     * Adapt the filesystem implementation.
     *
     * @param \League\Flysystem\AdapterInterface $filesystem
     *
     * @return \Viserio\Contracts\Filesystem\Filesystem
     */
    protected function adapt(AdapterInterface $filesystem): FilesystemContract
    {
        return new FilesystemAdapter($filesystem);
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
