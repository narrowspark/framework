<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Viserio\Component\Contract\Mail\Mailer as MailerContract;
use Viserio\Component\Contract\Mail\QueueMailer as QueueMailerContract;
use Viserio\Component\Mail\MailManager;
use Viserio\Component\Mail\TransportFactory;

class MailServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            TransportFactory::class    => [self::class, 'createTransportFactory'],
            MailerContract::class      => [self::class, 'createMailer'],
            QueueMailerContract::class => function (ContainerInterface $container) {
                return $container->get(MailerContract::class);
            },
            'mailer'                   => function (ContainerInterface $container) {
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

    /**
     * Create a new transport factory.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Mail\TransportFactory
     */
    public static function createTransportFactory(ContainerInterface $container): TransportFactory
    {
        $transport = new TransportFactory();

        if ($container->has(LoggerInterface::class)) {
            $transport->setLogger($container->get(LoggerInterface::class));
        }

        return $transport;
    }

    /**
     * Create a new swift mailer manager.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Mail\MailManager
     */
    public static function createMailManager(ContainerInterface $container): MailManager
    {
        return new MailManager($container, $container->get(TransportFactory::class));
    }

    /**
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Mail\Mailer
     */
    public static function createMailer(ContainerInterface $container): MailerContract
    {
        return $container->get(MailManager::class)->getDefaultConnection();
    }
}
