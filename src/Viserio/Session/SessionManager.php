<?php
namespace Viserio\Session;

use Viserio\Contracts\{
    Config\Manager as ConfigContract,
    Encryption\Encrypter as EncrypterContract,
    Session\SessionHandler as SessionHandlerContract,
    Session\Store as StoreContract
};
use Viserio\Support\AbstractManager;

class SessionManager extends AbstractManager
{
    /**
     * All supported drivers.
     *
     * @var array
     */
    protected $supportedDrivers = [
        'apc',
        'apcu',
        'array',
        'filesystem',
        'local',
        'memcache',
        'memcached',
        'mongodb',
        'predis',
        'redis',
    ];

    /**
     * Encrypter instance.
     *
     * @var EncrypterContract
     */
    private $encrypter;

    /**
     * Constructor.
     *
     * @param \Viserio\Contracts\Config\Manager    $config
     * @param \Viserio\Contracts\Encryption\Encrypter $encrypter
     */
    public function __construct(ConfigContract $config, EncrypterContract $encrypter)
    {
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
     * @return StoreContract
     */
    protected function createLocalDriver(): StoreContract
    {
        $path = $this->config->get($this->getConfigName() . '.files');
        $lifetime = $this->config->get($this->getConfigName() . '.lifetime');

        return $this->buildSession(
            new FileSessionHandler($this->getContainer()->get('files'), $path, $lifetime)
        );
    }

     /**
     * Create an instance of the "cookie" session driver.
     *
     * @return StoreContract
     */
    protected function createCookieDriver(): StoreContract
    {
        $lifetime = $this->config->get($this->getConfigName() . '.lifetime');

        return $this->buildSession(new CookieSessionHandler($this->getContainer()->get('cookie'), $lifetime));
    }

    /**
     * Create an instance of the Memcached session driver.
     *
     * @return StoreContract
     */
    protected function createMemcachedDriver(): StoreContract
    {
        return $this->createCacheBased('memcached');
    }

    /**
     * Create an instance of the Memcache session driver.
     *
     * @return StoreContract
     */
    protected function createMemcacheDriver(): StoreContract
    {
        return $this->createCacheBased('memcache');
    }

    /**
     * Create an instance of the Mongodb session driver.
     *
     * @return StoreContract
     */
    protected function createMongodbDriver(): StoreContract
    {
        $options = $this->config->get($this->getConfigName() . '.mongodb');

        return $this->createCacheBased('mongodb', $options);
    }

    /**
     * Create an instance of the Predis session driver.
     *
     * @return StoreContract
     */
    protected function createPredisDriver(): StoreContract
    {
        $options = $this->config->get($this->getConfigName() . '.predis');

        return $this->createCacheBased('predis', $options);
    }

    /**
     * Create an instance of the Redis session driver.
     *
     * @return StoreContract
     */
    protected function createRedisDriver(): StoreContract
    {
        $options = $this->config->get($this->getConfigName() . '.redis');

        return $this->createCacheBased('redis', $options);
    }

    /**
     * Create an instance of the Filesystem session driver.
     *
     * @return StoreContract
     */
    protected function createFilesystemDriver(): StoreContract
    {
        $options = $this->config->get($this->getConfigName() . '.flysystem');

        return $this->createCacheBased('filesystem', $options);
    }

    /**
     * Create an instance of the Array session driver.
     *
     * @return StoreContract
     */
    protected function createArrayDriver(): StoreContract
    {
        return $this->createCacheBased('array');
    }

    /**
     * Create an instance of the APCu session driver.
     *
     * @return StoreContract
     */
    protected function createApcuDriver(): StoreContract
    {
        return $this->createCacheBased('apcu');
    }

    /**
     * Create an instance of the APC session driver.
     *
     * @return StoreContract
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
     * @param array $options
     *
     * @return StoreContract
     */
    protected function createCacheBased($driver, array $options = []): StoreContract
    {
        $lifetime = $this->config->get($this->getConfigName() . '.lifetime');

        return $this->buildSession(
            new CacheBasedSessionHandler(
                clone $this->getContainer()->get('cache')->driver($driver, $options),
                $lifetime
            )
        );
    }

    /**
     * Build the session instance.
     *
     * @param SessionHandlerContract $handler
     *
     * @return StoreContract
     */
    protected function buildSession(SessionHandlerContract $handler): StoreContract
    {
        return new Store($this->config->get($this->getConfigName().'::cookie', false), $handler, $this->encrypter);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'session';
    }
}
