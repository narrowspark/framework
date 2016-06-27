<?php
namespace Viserio\Queue;

use Viserio\Contracts\{
    Config\Manager as ConfigContract,
    Encryption\Encrypter as EncrypterContract
};
use Viserio\Support\Manager;

class QueueManager extends Manager
{
    /**
     * All supported drivers.
     *
     * @var array
     */
    protected $supportedDrivers = [
        'null'
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
        return $this->config->get($this->getConfigName() . '::driver', 'local');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'queue';
    }
}
