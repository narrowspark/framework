<?php
declare(strict_types=1);
namespace Viserio\Component\Session;

use Interop\Container\ContainerInterface as ContainerInteropInterface;
use SessionHandlerInterface;
use Viserio\Component\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Component\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contracts\Encryption\Traits\EncrypterAwareTrait;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Session\Handler\CacheBasedSessionHandler;
use Viserio\Component\Session\Handler\CookieSessionHandler;
use Viserio\Component\Session\Handler\FileSessionHandler;
use Viserio\Component\Support\AbstractManager;

class SessionManager extends AbstractManager implements ProvidesDefaultOptionsContract
{
    use EncrypterAwareTrait;

    /**
     * Create a new session manager instance.
     *
     * @param \Interop\Container\ContainerInterface             $container
     * @param \Viserio\Component\Contracts\Encryption\Encrypter $encrypter
     */
    public function __construct(
        ContainerInteropInterface $container,
        EncrypterContract $encrypter
    ) {
        parent::__construct($container);

        $this->container = $container;
        $this->encrypter = $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'default' => 'array',
        ];
    }

    /**
     * Create an instance of the file session driver.
     *
     * @param array $config
     *
     * @return \Viserio\Component\Contracts\Session\Store
     */
    protected function createLocalDriver(array $config): StoreContract
    {
        return $this->buildSession(
            new FileSessionHandler(
                $this->getContainer()->get(FilesystemContract::class),
                $config['path'],
                $this->options['lifetime']
            )
        );
    }

    /**
     * Create an instance of the "cookie" session driver.
     *
     * @return \Viserio\Component\Contracts\Session\Store
     */
    protected function createCookieDriver(): StoreContract
    {
        return $this->buildSession(
            new CookieSessionHandler(
                $this->getContainer()->get(JarContract::class),
                $this->options['lifetime']
            )
        );
    }

    /**
     * Create an instance of the Memcached session driver.
     *
     * @return \Viserio\Component\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createMemcachedDriver(): StoreContract
    {
        return $this->createCacheBased('memcached');
    }

    /**
     * Create an instance of the Memcache session driver.
     *
     * @return \Viserio\Component\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createMemcacheDriver(): StoreContract
    {
        return $this->createCacheBased('memcache');
    }

    /**
     * Create an instance of the Mongodb session driver.
     *
     * @return \Viserio\Component\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createMongodbDriver(): StoreContract
    {
        return $this->createCacheBased(
            'mongodb',
            $this->options['mongodb']
        );
    }

    /**
     * Create an instance of the Predis session driver.
     *
     * @return \Viserio\Component\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createPredisDriver(): StoreContract
    {
        return $this->createCacheBased(
            'predis',
            $this->options['predis']
        );
    }

    /**
     * Create an instance of the Redis session driver.
     *
     * @return \Viserio\Component\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createRedisDriver(): StoreContract
    {
        return $this->createCacheBased(
            'redis',
            $this->options['redis']
        );
    }

    /**
     * Create an instance of the Filesystem session driver.
     *
     * @return \Viserio\Component\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createFilesystemDriver(): StoreContract
    {
        return $this->createCacheBased(
            'filesystem',
            $this->options['flysystem']
        );
    }

    /**
     * Create an instance of the Array session driver.
     *
     * @return \Viserio\Component\Contracts\Session\Store
     */
    protected function createArrayDriver(): StoreContract
    {
        return $this->createCacheBased('array');
    }

    /**
     * Create an instance of the APCu session driver.
     *
     * @return \Viserio\Component\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createApcuDriver(): StoreContract
    {
        return $this->createCacheBased('apcu');
    }

    /**
     * Create an instance of the APC session driver.
     *
     * @return \Viserio\Component\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createApcDriver(): StoreContract
    {
        return $this->createCacheBased('apc');
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    protected function callCustomCreator(string $driver, array $options = [])
    {
        return $this->buildSession(parent::callCustomCreator($driver, $options));
    }

    /**
     * Create the cache based session handler instance.
     *
     * @param string $driver
     * @param array  $options
     *
     * @return \Viserio\Component\Contracts\Session\Store
     */
    protected function createCacheBased($driver, array $options = []): StoreContract
    {
        return $this->buildSession(
            new CacheBasedSessionHandler(
                clone $this->container->get(CacheManagerContract::class)->getDriver($driver, $options),
                $this->options['lifetime']
            )
        );
    }

    /**
     * Build the session instance.
     *
     * @param \SessionHandlerInterface $handler
     *
     * @return \Viserio\Component\Contracts\Session\Store
     */
    protected function buildSession(SessionHandlerInterface $handler): StoreContract
    {
        return new Store(
            $this->options['cookie'] ?? '',
            $handler,
            $this->getEncrypter()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'session';
    }
}
