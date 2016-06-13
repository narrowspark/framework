<?php
namespace Viserio\Session;

use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Contracts\Session\SessionHandler as SessionHandlerContract;
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
        $this->config->set($this->getConfigName().'::driver', $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get($this->getConfigName().'::driver', '');
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
     * @return \Viserio\Session\Store
     */
    protected function buildSession(SessionHandlerContract $handler): Store
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
