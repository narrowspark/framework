<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Swift_Mailer;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Mail\Mailer as MailerContract;
use Viserio\Component\Contract\Queue\QueueConnector as QueueConnectorContract;
use Viserio\Component\Contract\View\Factory as ViewFactoryContract;
use Viserio\Component\Mail\Mailer;
use Viserio\Component\Mail\QueueMailer;
use Viserio\Component\Mail\TransportManager;

class MailServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
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

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    public static function createTransportManager(ContainerInterface $container): TransportManager
    {
        return new TransportManager($container);
    }

    public static function createSwiftMailer(ContainerInterface $container): Swift_Mailer
    {
        $transporter = $container->get(TransportManager::class);

        return new Swift_Mailer($transporter->getDriver());
    }

    public static function createMailer(ContainerInterface $container): MailerContract
    {
        if ($container->has(QueueConnectorContract::class)) {
            $mailer = new QueueMailer(
                $container->get(Swift_Mailer::class),
                $container->get(QueueConnectorContract::class),
                $container
            );
        } else {
            $mailer = new Mailer($container->get(Swift_Mailer::class), $container);
        }

        $mailer->setContainer($container);

        if ($container->has(ViewFactoryContract::class)) {
            $mailer->setViewFactory($container->get(ViewFactoryContract::class));
        }

        if ($container->has(EventManagerContract::class)) {
            $mailer->setEventManager($container->get(EventManagerContract::class));
        }

        return $mailer;
    }
}
