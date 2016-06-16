<?php
namespace Viserio\Session;

use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Contracts\Session\SessionHandler as SessionHandlerContract;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Support\Manager;

class SessionManager extends Manager
{
    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * Encrypter instance.
     *
     * @var EncrypterContract
     */
    private $encrypter;

    /**
     * Constructor.
     *
     * @param ConfigContract    $config
     * @param EncrypterContract $encrypter
     */
    public function __construct(ConfigContract $config, EncrypterContract $encrypter)
    {
        $this->config = $config;
        $this->encrypter = $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultDriver(string $name)
    {
        $this->config->set($this->getConfigName() . '::driver', $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get($this->getConfigName() . '::driver', '');
    }

    /**
     * Create an instance of the file session driver.
     *
     * @return StoreContract
     */
    protected function createFileDriver(): StoreContract
    {
        $path = $this->config->get($this->getConfigName() . '::files');
        $lifetime = $this->config->get($this->getConfigName() . '::lifetime');

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
        $lifetime = $this->config->get($this->getConfigName() . '::lifetime');

        return $this->buildSession(new CookieSessionHandler($this->getContainer()->get('cookie'), $lifetime));
    }

    /**
     * {@inheritdoc}
     */
    protected function callCustomCreator(string $driver, array $options = [])
    {
        return $this->buildSession(parent::callCustomCreator($driver, $options));
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
