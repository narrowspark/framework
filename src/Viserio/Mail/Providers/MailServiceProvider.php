<?php
declare(strict_types=1);
namespace Viserio\Mail\Providers;

use Aws\Ses\SesClient;
use GuzzleHttp\Client as HttpClient;
use Viserio\Application\ServiceProvider;
use Viserio\Mail\Mailer;
use Viserio\Mail\Transport\Log as LogTransport;
use Viserio\Mail\Transport\Mailgun as MailgunTransport;
use Viserio\Mail\Transport\Mandrill as MandrillTransport;
use Viserio\Mail\Transport\Postmark as PostmarkTransport;
use Viserio\Mail\Transport\Ses as SesTransport;

class MailServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerSwiftMailer();

        $this->app->singleton('mailer', function ($app) {
            // Once we have create the mailer instance, we will set a container instance
            // on the mailer. This allows us to resolve mailer classes via containers
            // for maximum testability on said classes instead of passing Closures.
            $mailer = new Mailer(
                $app->get('swift.mailer'),
                $app->get('view'),
                $app->get('events')
            );

            $mailer->setLogger($app->get('log')->getMonolog());

            // If a "from" address is set, we will set it on the mailer so that all mail
            // messages sent by the applications will utilize the same "from" address
            // on each one, which makes the developer's life a lot more convenient.
            $from = $app->get('config')->get('mail::from');

            if (is_array($from) && isset($from['address'])) {
                $mailer->alwaysFrom($from['address'], $from['name']);
            }

            // If a "to" address is set, we will set it on the mailer so that all mail
            // messages sent by the applications will utilize the same "to" address
            // on each one, which makes it easier for a develop to test, view and share
            // emails sent from a development or staging environment without spamming
            // real customers.
            $to = $app->get('config')->get('mail.to');

            if (is_array($to) && isset($to['address'])) {
                $mailer->alwaysTo($to['address'], $to['name']);
            }

            return $mailer;
        });
    }

    /**
     * Register the Swift Mailer instance.
     */
    public function registerSwiftMailer()
    {
        $this->registerSwiftTransport($this->app->get('config'));

        // Once we have the transporter registered, we will register the actual Swift
        // mailer instance, passing in the transport instances, which allows us to
        // override this transporter instances during app start-up if necessary.
        $this->app->bind('swift.mailer', function ($container) {
            return new \Swift_Mailer($container['swift.transport']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            'mailer',
            'swift.transport',
            'ses.transport',
        ];
    }

    /**
     * Register the Swift Transport instance.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \Swift_SmtpTransport|null|\Swift_SendmailTransport|\Swift_MailTransport|MailgunTransport|MandrillTransport|SesTransport|PostmarkTransport|LogTransport
     */
    protected function registerSwiftTransport($config)
    {
        switch ($config['mail::driver']) {
            case 'smtp':
                return $this->registerSmtpTransport($config);

            case 'sendmail':
                return $this->registerSendmailTransport($config);

            case 'mail':
                return $this->registerMailTransport();

            case 'mailgun':
                return $this->registerMailgunTransport($config);

            case 'mandrill':
                return $this->registerMandrillTransport($config);

            case 'ses':
                return $this->registerSesTransport($config);

            case 'postmark':
                return $this->createPostmarkDriver($config);

            case 'log':
                return $this->registerLogTransport();

            default:
                throw new \InvalidArgumentException('Invalid mail driver.');
        }
    }

    /**
     * Register the SMTP Swift Transport instance.
     *
     * @param array $config
     *
     * @return \Swift_SmtpTransport|null
     *
     * @throw  \InvalidArgumentException
     */
    protected function registerSmtpTransport($config)
    {
        $this->app->bind('swift.transport', function ($app) use ($config) {

            // The Swift SMTP transport instance will allow us to use any SMTP backend
            // for delivering mail such as Sendgrid, Amazon SES, or a custom server
            // a developer has available. We will just pass this configured host.

            // Once we have the transport we will check for the presence of a username
            // and password. If we have it we will set the credentials on the Swift
            // transporter instance so that we'll properly authenticate delivery.

            // switch between ssl, tls and normal

            $smtp = \Swift_SmtpTransport::newInstance();
            $smtp->setHost($config['mail::host']);
            $smtp->setPort($app->get('config')->get('mail::port'));
            $smtp->setUsername($config['mail::smtp_username']);
            $smtp->setPassword($config['mail::smtp_password']);

            if ($app->get('config')->get('mail::entcryption') === 'ssl') {
                $smtp->setEncryption('ssl');
            } elseif ($app->get('config')->get('mail::entcryption') === 'tls') {
                $smtp->setEncryption('tls');
            } else {
                throw new \InvalidArgumentException('Invalid SMTP Encrypton.');
            }

            return $smtp;
        });
    }

    /**
     * Register the SES Swift Transport instance.
     *
     * @param array $config
     *
     * @return SesTransport|null
     */
    protected function registerSesTransport($config)
    {
        $this->app->bind('ses.transport', function () use ($config) {
            $config = $this->app->get('config')->get('services.ses', []);
            $config += [
                'version' => 'latest',
                'service' => 'email',
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
            ];

            unset($config['key'], $config['secret']);

            return new SesTransport(new SesClient($config));
        });
    }

    /**
     * Register the Sendmail Swift Transport instance.
     *
     * @param array $config
     *
     * @return \Swift_SendmailTransport|null
     */
    protected function registerSendmailTransport($config)
    {
        $this->app->bind('swift.transport', function () use ($config) {
            return \Swift_SendmailTransport::newInstance($config['mail::sendmail']);
        });
    }

    /**
     * Register the Mail Swift Transport instance.
     *
     * @return \Swift_MailTransport|null
     */
    protected function registerMailTransport()
    {
        $this->app->bind('swift.transport', function () {
            return \Swift_MailTransport::newInstance();
        });
    }

    /**
     * Register the Mailgun Swift Transport instance.
     *
     * @param array $config
     *
     * @return MailgunTransport|null
     */
    protected function registerMailgunTransport($config)
    {
        $client = new HttpClient();
        $mailgun = $config['mail::services.mailgun'];

        $$this->app->bind('swift.transport', function () use ($mailgun) {
            return new MailgunTransport($client, $mailgun['secret'], $mailgun['base.url'], $mailgun['domain']);
        });
    }

    /**
     * Register the Mandrill Swift Transport instance.
     *
     * @param array $config
     *
     * @return MandrillTransport|null
     */
    protected function registerMandrillTransport($config)
    {
        $client = new HttpClient();
        $mandrill = $config['mail::services.mandrill'];

        $this->app->bind('swift.transport', function () use ($mandrill) {
            return new MandrillTransport($client, $mandrill['secret']);
        });
    }

    /**
     * Create an instance of the Postmark Swift Transport driver.
     *
     * @param array $config
     *
     * @return PostmarkTransport|null
     */
    protected function createPostmarkDriver($config)
    {
        $potmark = $config['mail::services.postmark'];

        $this->app->bind('swift.transport', function () use ($potmark) {
            return new PostmarkTransport($potmark['serverToken']);
        });
    }

    /**
     * Register the "Log" Swift Transport instance.
     *
     * @return LogTransport|null
     */
    protected function registerLogTransport()
    {
        $this->app->bind('swift.transport', function ($container) {
            return new LogTransport($container['log']->getMonolog());
        });
    }
}
