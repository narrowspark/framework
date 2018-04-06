<?php
declare(strict_types=1);
namespace Viserio\Component\Mail;

use Aws\Ses\SesClient;
use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Mail\Transport\ArrayTransport;
use Viserio\Component\Mail\Transport\LogTransport;
use Viserio\Component\Mail\Transport\MailgunTransport;
use Viserio\Component\Mail\Transport\MandrillTransport;
use Viserio\Component\Mail\Transport\SesTransport;
use Viserio\Component\Mail\Transport\SparkPostTransport;
use Viserio\Component\Support\AbstractManager;

class TransportManager extends AbstractManager implements ProvidesDefaultOptionsContract
{
    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'default' => 'local',
        ];
    }

    /**
     * Create an instance of the Log Swift Transport driver.
     *
     * @return \Viserio\Component\Mail\Transport\LogTransport
     */
    protected function createLogDriver(): LogTransport
    {
        return new LogTransport($this->container->get(LoggerInterface::class));
    }

    /**
     * Create an instance of the Log Swift Transport driver.
     *
     * @return \Viserio\Component\Mail\Transport\ArrayTransport
     */
    protected function createLocalDriver(): ArrayTransport
    {
        return new ArrayTransport();
    }

    /**
     * Create an instance of the Sendmail Swift Transport driver.
     *
     * @param array $config
     *
     * @return \Swift_SendmailTransport
     */
    protected function createSendmailDriver(array $config): Swift_SendmailTransport
    {
        return new Swift_SendmailTransport($config['command'] ?? '/usr/sbin/sendmail -bs');
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
        // for delivering mail such as Amazon SES, Sendgrid or a custom server
        // a developer has available.
        $transport = new Swift_SmtpTransport(
            $config['host'],
            $config['port']
        );

        if (isset($config['encryption'])) {
            $transport->setEncryption($config['encryption']);
        }

        // Once we have the transport we will check for the presence of a username
        // and password.
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
     * @return \Viserio\Component\Mail\Transport\MailgunTransport
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
     * @return \Viserio\Component\Mail\Transport\MandrillTransport
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
     * @return \Viserio\Component\Mail\Transport\SparkPostTransport
     */
    protected function createSparkPostDriver(array $config): SparkPostTransport
    {
        return new SparkPostTransport(
            $this->getHttpClient($config),
            $config['secret'],
            $config['options'] ?? []
        );
    }

    /**
     * Create an instance of the Amazon SES Swift Transport driver.
     *
     * @param array $config
     *
     * @return \Viserio\Component\Mail\Transport\SesTransport
     */
    protected function createSesDriver(array $config): SesTransport
    {
        $config += [
            'version' => 'latest',
            'service' => 'email',
        ];

        if (isset($config['key'], $config['secret'])) {
            $config['credentials'] = \array_intersect_key($config, \array_flip(['key', 'secret', 'token']));
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
        $guzzleConfig = $config['guzzle'] ?? [];

        $guzzleConfig['connect_timeout'] = 90;

        return new HttpClient($guzzleConfig);
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected static function getConfigName(): string
    {
        return 'mail';
    }
}
