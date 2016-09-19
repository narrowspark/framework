<?php
declare(strict_types=1);
namespace Viserio\Mail;

use Aws\Ses\SesClient;
use GuzzleHttp\Client as HttpClient;
use Narrowspark\Arr\Arr;
use Psr\Log\LoggerInterface;
use Swift_MailTransport;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Viserio\Mail\Transport\Log as LogTransport;
use Viserio\Mail\Transport\Mailgun as MailgunTransport;
use Viserio\Mail\Transport\Mandrill as MandrillTransport;
use Viserio\Mail\Transport\Ses as SesTransport;
use Viserio\Mail\Transport\SparkPost as SparkPostTransport;
use Viserio\Support\AbstractManager;

class TransportManager extends AbstractManager
{
    /**
     * Create an instance of the Log Swift Transport driver.
     *
     * @return \Viserio\Mail\Transport\Log
     */
    protected function createLogDriver(): LogTransport
    {
        return new LogTransport($this->getContainer()->get(LoggerInterface::class));
    }

    /**
     * Create an instance of the Mail Swift Transport driver.
     *
     * @return \Swift_MailTransport
     */
    protected function createMailDriver(): Swift_MailTransport
    {
        return Swift_MailTransport::newInstance();
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
        return Swift_SendmailTransport::newInstance($config);
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
        $transport = Swift_SmtpTransport::newInstance(
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
