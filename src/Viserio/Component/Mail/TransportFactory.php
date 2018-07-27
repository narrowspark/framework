<?php
declare(strict_types=1);
namespace Viserio\Component\Mail;

use Aws\Ses\SesClient;
use Closure;
use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Swift_Transport;
use Viserio\Component\Contract\Mail\Exception\InvalidArgumentException;
use Viserio\Component\Mail\Transport\ArrayTransport;
use Viserio\Component\Mail\Transport\LogTransport;
use Viserio\Component\Mail\Transport\MailgunTransport;
use Viserio\Component\Mail\Transport\MandrillTransport;
use Viserio\Component\Mail\Transport\SesTransport;
use Viserio\Component\Mail\Transport\SparkPostTransport;
use Viserio\Component\Support\Str;

class TransportFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $transports = [];

    /**
     * The registered custom transformer creators.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * Get a transport instance.
     *
     * @param string $transport
     * @param array  $config
     *
     * @throws \Viserio\Component\Contract\Mail\Exception\InvalidArgumentException
     *
     * @return \Swift_Transport
     */
    public function getTransport(string $transport, array $config): Swift_Transport
    {
        // If the given transport has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a transport created by this name, we'll just return that instance.
        if (! isset($this->transports[$transport])) {
            $this->transports[$transport] = $this->createTransport($transport, $config);
        }

        return $this->transports[$transport];
    }

    /**
     * Make a new transport instance.
     *
     * @param string $transport
     * @param array  $config
     *
     * @throws \Viserio\Component\Contract\Mail\Exception\InvalidArgumentException
     *
     * @return \Swift_Transport
     */
    public function createTransport(string $transport, array $config): Swift_Transport
    {
        $method = 'create' . Str::studly($transport) . 'Transport';

        $config['name'] = $transport;

        return $this->create($config, $method);
    }

    /**
     * Get all of the created "transports".
     *
     * @return array
     */
    public function getTransports(): array
    {
        return $this->transports;
    }

    /**
     * Check if the given transport is supported.
     *
     * @param string $transport
     *
     * @return bool
     */
    public function hasTransport(string $transport): bool
    {
        $method = 'create' . Str::studly($transport) . 'Transport';

        return \method_exists($this, $method) || isset($this->extensions[$transport]);
    }

    /**
     * {@inheritdoc}
     */
    public function extend(string $driver, Closure $callback): void
    {
        $this->extensions[$driver] = $callback->bindTo($this, $this);
    }

    /**
     * Make a new driver instance.
     *
     * @param array  $config
     * @param string $method
     *
     * @throws \Viserio\Component\Contract\Mail\Exception\InvalidArgumentException
     *
     * @return \Swift_Transport
     */
    protected function create(array $config, string $method): Swift_Transport
    {
        if (isset($this->extensions[$config['name']])) {
            return $this->callCustomCreator($config['name'], $config);
        }

        if (\method_exists($this, $method)) {
            return $this->{$method}($config);
        }

        throw new InvalidArgumentException(\sprintf('Transport [%s] is not supported.', $config['name']));
    }

    /**
     * Call a custom connection / driver creator.
     *
     * @param string $extension
     * @param array  $config
     *
     * @return mixed
     */
    protected function callCustomCreator(string $extension, array $config = [])
    {
        return $this->extensions[$extension]($config);
    }

    /**
     * Create an instance of the Log Swift Transport driver.
     *
     * @return \Viserio\Component\Mail\Transport\LogTransport
     */
    protected function createLogTransport(): LogTransport
    {
        return new LogTransport($this->logger);
    }

    /**
     * Create an instance of the Log Swift Transport driver.
     *
     * @return \Viserio\Component\Mail\Transport\ArrayTransport
     */
    protected function createArrayTransport(): ArrayTransport
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
    protected function createSendmailTransport(array $config): Swift_SendmailTransport
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
    protected function createSmtpTransport(array $config): Swift_SmtpTransport
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
    protected function createMailgunTransport(array $config): MailgunTransport
    {
        return new MailgunTransport(
            $this->getHttpClient($config),
            $config['secret'],
            $config['domain'],
            $config['base_url'] ?? null
        );
    }

    /**
     * Create an instance of the Mandrill Swift Transport driver.
     *
     * @param array $config
     *
     * @return \Viserio\Component\Mail\Transport\MandrillTransport
     */
    protected function createMandrillTransport(array $config): MandrillTransport
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
    protected function createSparkPostTransport(array $config): SparkPostTransport
    {
        return new SparkPostTransport(
            $this->getHttpClient($config),
            $config['secret'],
            $config['options'] ?? [],
            $config['endpoint'] ?? null
        );
    }

    /**
     * Create an instance of the Amazon SES Swift Transport driver.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \Viserio\Component\Mail\Transport\SesTransport
     */
    protected function createSesTransport(array $config): SesTransport
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
}
