<?php
declare(strict_types=1);
namespace Viserio\Mail;

use Aws\Ses\SesClient;
use Interop\Container\ContainerInterface;
use Swift_SmtpTransport as SmtpTransport;
use Swift_MailTransport as MailTransport;
use Narrowspark\Arr\StaticArr as Arr;
use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;
use Viserio\Support\AbstractManager;
use Viserio\Mail\Transport\{
    Log as LogTransport,
    Ses as SesTransport,
    Mailgun as MailgunTransport,
    Mandrill as MandrillTransport,
    SparkPost as SparkPostTransport
};

class TransportManager extends AbstractManager
{
    /**
     * Create a new manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager     $config
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
     * Create an instance of the Mail Swift Transport driver.
     *
     * @return \Swift_MailTransport
     */
    protected function createMailDriver(): Swift_MailTransport
    {
        return MailTransport::newInstance();
    }

    /**
     * Create an instance of the Sendmail Swift Transport driver.
     *
     * @param array $config
     *
     * @return \Swift_SendmailTransport
     */
    protected function createSendmailDriver(array $config): SendmailTransport
    {
        return SendmailTransport::newInstance($config);
    }

    /**
     * Create an instance of the SMTP Swift Transport driver.
     *
     * @param array $config
     *
     * @return \Swift_SmtpTransport
     */
    protected function createSmtpDriver(array $config): Swift_SmtpTransport
    {
        // The Swift SMTP transport instance will allow us to use any SMTP backend
        // for delivering mail such as Sendgrid, Amazon SES, or a custom server
        // a developer has available. We will just pass this configured host.
        $transport = SmtpTransport::newInstance(
            $config['host'],
            $config['port']
        );

        if (isset($config['encryption'])) {
            $transport->setEncryption($config['encryption']);
        }

        // Once we have the transport we will check for the presence of a username
        // and password. If we have it we will set the credentials on the Swift
        // transporter instance so that we'll properly authenticate delivery.
        if (isset($config['username'], $config['password'])) {
            $transport->setUsername($config['username']);
            $transport->setPassword($config['password']);
        }

        if (isset($config['stream'])) {
            $transport->setStreamOptions($config['stream']);
        }

        return $transport;
    }

    /**
     * Create an instance of the Mailgun Swift Transport driver.
     *
     * @param array $config
     *
     * @return \Viserio\Mail\Transport\Mailgun
     */
    protected function createMailgunDriver(array $config): MailgunTransport
    {
        return new MailgunTransport(
            $this->getHttpClient($config),
            $config['secret'],
            $config['domain']
        );
    }

    /**
     * Create an instance of the Mandrill Swift Transport driver.
     *
     * @param array $config
     *
     * @return \Viserio\Mail\Transport\Mandrill
     */
    protected function createMandrillDriver(array $config): MandrillTransport
    {
        return new MandrillTransport(
            $this->getHttpClient($config),
            $config['secret']
        );
    }

    /**
     * Create an instance of the SparkPost Swift Transport driver.
     *
     * @param array $config
     *
     * @return \Viserio\Mail\Transport\SparkPost
     */
    protected function createSparkPostDriver(array $config): SparkPostTransport
    {
        return new SparkPostTransport(
            $this->getHttpClient($config),
            $config['secret'],
            Arr::get($config, 'options', [])
        );
    }


    /**
     * Create an instance of the Amazon SES Swift Transport driver.
     *
     * @param array $config
     *
     * @return \Viserio\Mail\Transport\Ses
     */
    protected function createSesDriver(array $config): SesTransport
    {
        $config += [
            'version' => 'latest',
            'service' => 'email',
        ];

        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        return new SesTransport(new SesClient($config));
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
