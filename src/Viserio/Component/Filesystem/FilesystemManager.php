<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Filesystem;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Cached\CachedAdapter;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Viserio\Component\Filesystem\Cache\CachedFactory;
use Viserio\Component\Filesystem\Encryption\EncryptionWrapper;
use Viserio\Component\Manager\AbstractConnectionManager;
use Viserio\Contract\Cache\Traits\CacheManagerAwareTrait;
use Viserio\Contract\Filesystem\Exception\InvalidArgumentException;
use Viserio\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;

class FilesystemManager extends AbstractConnectionManager implements ProvidesDefaultOptionsContract
{
    use CacheManagerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'default' => 'local',
        ];
    }

    /**
     * Get a crypted aware connection instance.
     *
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key
     * @param null|string                               $name
     *
     * @return \Viserio\Component\Filesystem\Encryption\EncryptionWrapper
     */
    public function cryptedConnection(EncryptionKey $key, string $name = null): EncryptionWrapper
    {
        return new EncryptionWrapper($this->getConnection($name), $key);
    }

    /**
     * Get the clean flysystem adapter.
     *
     * @param null|string $name
     *
     * @return \League\Flysystem\AdapterInterface
     */
    public function getFlysystemAdapter(string $name = null): AdapterInterface
    {
        return parent::getConnection($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(string $name = null)
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
        $cacheName = ($config['cache'] ?? false);

        if (\is_string($cacheName)) {
            $config['cache'] = $this->getCacheConfig($cacheName);
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigName(): string
    {
        return 'filesystem';
    }

    /**
     * Get the cache configuration.
     *
     * @param string $name
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return array
     */
    protected function getCacheConfig(string $name): array
    {
        $cache = $this->resolvedOptions['cached'];

        if (! \is_array($config = ($cache[$name] ?? false)) && ! $config) {
            throw new InvalidArgumentException(\sprintf('Cache [%s] not configured.', $name));
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
     * @return \Viserio\Contract\Filesystem\Filesystem
     */
    protected function adapt(AdapterInterface $adapter, array $config): FilesystemContract
    {
        if (isset($config['cache']) && \is_array($config['cache'])) {
            $cacheFactory = new CachedFactory($this, $this->cacheManager);

            $adapter = new CachedAdapter($adapter, $cacheFactory->getConnection($config));

            unset($config['cache']);
        }

        return new FilesystemAdapter($adapter, $config);
    }

    /**
     * Create an instance of the Awss3 connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \League\Flysystem\AdapterInterface
     */
    protected function createAwss3Connection(array $config): AdapterInterface
    {
        return (new Adapter\AwsS3Connector($config))->connect();
    }

    /**
     * Create an instance of the Dropbox connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \League\Flysystem\AdapterInterface
     */
    protected function createDropboxConnection(array $config): AdapterInterface
    {
        return (new Adapter\DropboxConnector($config))->connect();
    }

    /**
     * Create an instance of the Ftp connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \League\Flysystem\AdapterInterface
     */
    protected function createFtpConnection(array $config): AdapterInterface
    {
        return (new Adapter\FtpConnector($config))->connect();
    }

    /**
     * Create an instance of the Local connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \League\Flysystem\AdapterInterface
     */
    protected function createLocalConnection(array $config): AdapterInterface
    {
        return (new Adapter\LocalConnector($config))->connect();
    }

    /**
     * Create an instance of the Null connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \League\Flysystem\AdapterInterface
     */
    protected function createNullConnection(array $config): AdapterInterface
    {
        return (new Adapter\NullConnector())->connect();
    }

    /**
     * Create an instance of the Sftp connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \League\Flysystem\AdapterInterface
     */
    protected function createSftpConnection(array $config): AdapterInterface
    {
        return (new Adapter\SftpConnector($config))->connect();
    }

    /**
     * Create an instance of the Vfs connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \League\Flysystem\AdapterInterface
     */
    protected function createVfsConnection(array $config): AdapterInterface
    {
        return (new Adapter\VfsConnector())->connect();
    }

    /**
     * Create an instance of the WebDav connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \League\Flysystem\AdapterInterface
     */
    protected function createWebdavConnection(array $config): AdapterInterface
    {
        return (new Adapter\WebDavConnector($config))->connect();
    }

    /**
     * Create an instance of the Zip connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \League\Flysystem\AdapterInterface
     */
    protected function createZipConnection(array $config): AdapterInterface
    {
        return (new Adapter\ZipConnector($config))->connect();
    }
}
