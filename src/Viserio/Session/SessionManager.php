<?php
declare(strict_types=1);
namespace Viserio\Session;

use Interop\Container\ContainerInterface;
use SessionHandlerInterface;
use Viserio\Cache\CacheManager;
use Viserio\Contracts\Config\Manager as ConfigContract;
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
        $path = $this->config->get($this->getConfigName() . '.path');
        $lifetime = $this->config->get($this->getConfigName() . '.lifetime');

        return $this->buildSession(
            new FileSessionHandler(
                $this->getContainer()->get(FilesystemContract::class),
                $path,
                $lifetime
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
        $lifetime = $this->config->get($this->getConfigName() . '.lifetime');

        return $this->buildSession(
            new CookieSessionHandler(
                $this->getContainer()->get(JarContract::class),
                $lifetime
            )
        );
    }

    /**
     * Create an instance of the Memcached session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     */
    protected function createMemcachedDriver(): StoreContract
    {
        return $this->createCacheBased('memcached');
    }

    /**
     * Create an instance of the Memcache session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     */
    protected function createMemcacheDriver(): StoreContract
    {
        return $this->createCacheBased('memcache');
    }

    /**
     * Create an instance of the Mongodb session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     */
    protected function createMongodbDriver(): StoreContract
    {
        $options = $this->config->get($this->getConfigName() . '.mongodb');

        return $this->createCacheBased('mongodb', $options);
    }

    /**
     * Create an instance of the Predis session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     */
    protected function createPredisDriver(): StoreContract
    {
        $options = $this->config->get($this->getConfigName() . '.predis');

        return $this->createCacheBased('predis', $options);
    }

    /**
     * Create an instance of the Redis session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     */
    protected function createRedisDriver(): StoreContract
    {
        $options = $this->config->get($this->getConfigName() . '.redis');

        return $this->createCacheBased('redis', $options);
    }

    /**
     * Create an instance of the Filesystem session driver.
     *
     * @return \Viserio\Contracts\Session\Store
     */
    protected function createFilesystemDriver(): StoreContract
    {
        $options = $this->config->get($this->getConfigName() . '.flysystem');

        return $this->createCacheBased('filesystem', $options);
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
     */
    protected function createApcuDriver(): StoreContract
    {
        return $this->createCacheBased('apcu');
    }

    /**
     * Create an instance of the APC session driver.
     *
     * @return \Viserio\Contracts\Session\Store
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
        $lifetime = $this->config->get($this->getConfigName() . '.lifetime');

        return $this->buildSession(
            new CacheBasedSessionHandler(
                clone $this->getContainer()->get(CacheManager::class)->driver($driver, $options),
                $lifetime
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
        return new Store($this->config->get($this->getConfigName() . '.cookie', false), $handler, $this->encrypter);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'session';
    }
}
