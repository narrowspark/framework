<?php
namespace Viserio\Queue;

use Viserio\Contracts\{
    Config\Manager as ConfigContract,
    Encryption\Encrypter as EncrypterContract
};
use Viserio\Support\AbstractConnectionManager;

class QueueManager extends AbstractConnectionManager
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
     * Constructor.b
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
    protected function getConfigName(): string
    {
        return 'queue';
    }
}
