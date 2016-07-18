<?php
namespace Viserio\Mail;

use Interop\Container\ContainerInterface;
use Narrowspark\Arr\StaticArr as Arr;
use GuzzleHttp\Client as HttpClient;
use Swift_SmtpTransport as SmtpTransport;
use Swift_MailTransport as MailTransport;
use Psr\Log\LoggerInterface;
use Viserio\Support\AbstractManager;
use Viserio\Mail\Transport\{
    Log as LogTransport
};

class TransportManager extends AbstractManager
{
    /**
     * Create a new manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager $config
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ConfigContract $config, ContainerInterface $container)
    {
        $this->config = $config;
        $this->container = $container;
    }

    /**
     * Create an instance of the Log Swift Transport driver.
     *
     * @return \Viserio\Mail\Transport\Log
     */
    protected function createLogDriver(): LogTransport
    {
        return new LogTransport($this->container->get(LoggerInterface::class));
    }

    /**
     * Get a fresh Guzzle HTTP client instance.
     *
     * @param array $config
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient(array $config): HttpClient
    {
        $guzzleConfig = Arr::get($config, 'guzzle', []);

        return new HttpClient(Arr::add($guzzleConfig, 'connect_timeout', 90));
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'mail';
    }
}
