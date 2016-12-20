<?php
declare(strict_types=1);
namespace Viserio\Mail\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Swift_Mailer;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Mail\Mailer as MailerContract;
use Viserio\Contracts\Queue\Queue as QueueContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Contracts\View\Factory as ViewFactoryContract;
use Viserio\Mail\Mailer;
use Viserio\Mail\QueueMailer;
use Viserio\Mail\TransportManager;

class MailServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.mail';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            TransportManager::class => [self::class, 'createTransportManager'],
            'swift.transport'       => function (ContainerInterface $container) {
                return $container->get(TransportManager::class);
            },
            Swift_Mailer::class => [self::class, 'createSwiftMailer'],
            'swift.mailer'      => function (ContainerInterface $container) {
                return $container->get(Swift_Mailer::class);
            },
            MailerContract::class => [self::class, 'createMailer'],
            Mailer::class         => function (ContainerInterface $container) {
                return $container->get(MailerContract::class);
            },
            'mailer' => function (ContainerInterface $container) {
                return $container->get(MailerContract::class);
            },
        ];
    }

    public static function createTransportManager(ContainerInterface $container): TransportManager
    {
        return new TransportManager($container->get(RepositoryContract::class));
    }

    public static function createSwiftMailer(ContainerInterface $container): Swift_Mailer
    {
        $transporter = $container->get(TransportManager::class);

        return new Swift_Mailer($transporter->driver());
    }

    public static function createMailer(ContainerInterface $container): MailerContract
    {
        if ($container->has(QueueContract::class)) {
            $mailer = new QueueMailer(
                $container->get(Swift_Mailer::class),
                $container->get(QueueContract::class)
            );

            $mailer->setContainer($container);
        } else {
            $mailer = new Mailer(
                $container->get(Swift_Mailer::class)
            );
        }

        if ($container->has(ViewFactoryContract::class)) {
            $mailer->setViewFactory($container->get(ViewFactoryContract::class));
        }

        if ($container->has(DispatcherContract::class)) {
            $mailer->setEventsDispatcher($container->get(DispatcherContract::class));
        }

        // If a "from" address is set, we will set it on the mailer so that all mail
        // messages sent by the applications will utilize the same "from" address
        // on each one, which makes the developer's life a lot more convenient.
        $from = self::getConfig($container, 'from');

        if (is_array($from) && isset($from['address'])) {
            $mailer->alwaysFrom($from['address'], $from['name']);
        }

        $to = self::getConfig($container, 'to');

        if (is_array($to) && isset($to['address'])) {
            $mailer->alwaysTo($to['address'], $to['name']);
        }

        return $mailer;
    }
}
