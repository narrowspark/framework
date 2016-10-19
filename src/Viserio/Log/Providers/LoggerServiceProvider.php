<?php
declare(strict_types=1);
namespace Viserio\Log\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Log\Log;
use Viserio\Log\Writer as MonologWriter;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;

class LoggerServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.log';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            MonologWriter::class => [self::class, 'createLogger'],
            'logger' => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
            },
            'log' => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
            },
            Logger::class => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
            },
            LoggerInterface::class => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
            },
            Log::class => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
            },
        ];
    }

    public static function createLogger(ContainerInterface $container): MonologWriter
    {
        $logger = new MonologWriter(new Logger(self::getConfig($container, 'env', 'production')));

        if ($container->has(DispatcherContract::class)) {
            $logger->setEventsDispatcher($container->get(DispatcherContract::class));
        }

        return $logger;
    }
}
