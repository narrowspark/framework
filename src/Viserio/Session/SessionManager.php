<?php
declare(strict_types=1);
namespace Viserio\Session;

use SessionHandlerInterface;
use Viserio\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Contracts\Encryption\Traits\EncrypterAwareTrait;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Session\Handler\CacheBasedSessionHandler;
use Viserio\Session\Handler\CookieSessionHandler;
use Viserio\Session\Handler\FileSessionHandler;
use Viserio\Support\AbstractManager;

class SessionManager extends AbstractManager
{
    use ContainerAwareTrait;
    use EncrypterAwareTrait;

    /**
     * Constructor.
     *
     * @param \Viserio\Contracts\Config\Manager       $config
     * @param \Viserio\Contracts\Encryption\Encrypter $encrypter
     */
    public function __construct(
        ConfigContract $config,
        EncrypterContract $encrypter
    ) {
        $this->config = $config;
        $this->encrypter = $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get($this->getConfigName() . '.driver', 'local');
    }

    /**
     * Create an instance of the file session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     */
    protected function createLocalDriver(): StoreContract
    {
        return $this->buildSession(
            new FileSessionHandler(
                $this->getContainer()->get(FilesystemContract::class),
                $this->config->get($this->getConfigName() . '.path'),
                $this->config->get($this->getConfigName() . '.lifetime')
            )
        );
    }

    /**
     * Create an instance of the "cookie" session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     */
    protected function createCookieDriver(): StoreContract
    {
        return $this->buildSession(
            new CookieSessionHandler(
                $this->getContainer()->get(JarContract::class),
                $this->config->get($this->getConfigName() . '.lifetime')
            )
        );
    }

    /**
     * Create an instance of the Memcached session driver.
     *
     * @return \Viserio\Contracts\Session\Store
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
     * @return \Viserio\Contracts\Session\Store
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
     * @return \Viserio\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createMongodbDriver(): StoreContract
    {
        return $this->createCacheBased(
            'mongodb',
            $this->config->get($this->getConfigName() . '.mongodb')
        );
    }

    /**
     * Create an instance of the Predis session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createPredisDriver(): StoreContract
    {
        return $this->createCacheBased(
            'predis',
            $this->config->get($this->getConfigName() . '.predis')
        );
    }

    /**
     * Create an instance of the Redis session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createRedisDriver(): StoreContract
    {
        return $this->createCacheBased(
            'redis',
            $this->config->get($this->getConfigName() . '.redis')
        );
    }

    /**
     * Create an instance of the Filesystem session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createFilesystemDriver(): StoreContract
    {
        return $this->createCacheBased(
            'filesystem',
            $this->config->get($this->getConfigName() . '.flysystem')
        );
    }

    /**
     * Create an instance of the Array session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     */
    protected function createArrayDriver(): StoreContract
    {
        return $this->createCacheBased('array');
    }

    /**
     * Create an instance of the APCu session driver.
     *
     * @return \Viserio\Contracts\Session\Store
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
     * @return \Viserio\Contracts\Session\Store
     *
     * @codeCoverageIgnore
     */
    protected function createApcDriver(): StoreContract
    {
        return $this->createCacheBased('apc');
    }

    /**
     * {@inheritdoc}
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
     * @return \Viserio\Contracts\Session\Store
     */
    protected function createCacheBased($driver, array $options = []): StoreContract
    {
        return $this->buildSession(
            new CacheBasedSessionHandler(
                clone $this->getContainer()->get(CacheManagerContract::class)->driver($driver, $options),
                $this->config->get($this->getConfigName() . '.lifetime')
            )
        );
    }

    /**
     * Build the session instance.
     *
     * @param \SessionHandlerInterface $handler
     *
     * @return \Viserio\Contracts\Session\Store
     */
    protected function buildSession(SessionHandlerInterface $handler): StoreContract
    {
        return new Store(
            $this->config->get($this->getConfigName() . '.cookie', ''),
            $handler,
            $this->encrypter
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
