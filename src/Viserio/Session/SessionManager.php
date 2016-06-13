<?php
namespace Viserio\Session;

use Viserio\Contracts\Config\Manager as ConfigContract;
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
     * Constructor.
     *
     * @param ConfigContract $config
     */
    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
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
        if ($this->config->get($this->getConfigName().'::encrypt', false)) {
            # code...
        }

        return new Store($this->config->get($this->getConfigName().'::cookie', false), $handler);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'session';
    }
}
