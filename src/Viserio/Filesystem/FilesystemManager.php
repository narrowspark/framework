<?php
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
     * {@inheritdoc}
     */
    public function getDefaultConnection(): string
    {
        return $this->config->get($this->getConfigName() . '.default', 'local');
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

    protected function createAwss3Connection(array $options)
    {
        return (new Adapters\AwsS3Connector())->connect($options);
    }

    protected function createDropboxConnection(array $options)
    {
        return (new Adapters\DropboxConnector())->connect($options);
    }

    protected function createFtpConnection(array $options)
    {
        return (new Adapters\FtpConnector())->connect($options);
    }

    protected function createGridfsConnection(array $options)
    {
        return (new Adapters\GridFSConnector())->connect($options);
    }

    protected function createLocalConnection(array $options)
    {
        return (new Adapters\LocalConnector())->connect($options);
    }

    protected function createNullConnection()
    {
        return (new Adapters\NullConnector())->connect([]);
    }

    protected function createRackspaceConnection(array $options)
    {
        return (new Adapters\RackspaceConnector())->connect($options);
    }

    protected function createSftpConnection(array $options)
    {
        return (new Adapters\SftpConnector())->connect($options);
    }

    protected function createVfsConnection(array $options)
    {
        return (new Adapters\VfsConnector())->connect($options);
    }

    protected function createWebdavConnection(array $options)
    {
        return (new Adapters\WebDavConnector())->connect($options);
    }

    protected function createZipConnection(array $options)
    {
        return (new Adapters\ZipConnector())->connect($options);
    }
}
