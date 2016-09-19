<?php
declare(strict_types=1);
namespace Viserio\Log\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Log\Log;
use Viserio\Events\Dispatcher;
use Viserio\Log\Writer as MonologWriter;

class LoggerServiceProvider implements ServiceProvider
{
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
            'logger.options' => [self::class, 'createOptions'],
        ];
    }

    public static function createLogger(ContainerInterface $container): MonologWriter
    {
        if ($container->has(ConfigManager::class)) {
            $config = $container->get(ConfigManager::class)->get('log');
        } else {
            $config = self::get($container, 'options');
        }

        $logger = new MonologWriter(new Logger($config['env']));

        if ($container->has(Dispatcher::class)) {
            $logger->setEventsDispatcher($container->get(Dispatcher::class));
        }

        return $logger;
    }

    public static function createOptions(ContainerInterface $container) : array
    {
        return [
            'env' => self::get($container, 'env'),
        ];
    }

    /**
     * Returns the entry named PACKAGE.$name, of simply $name if PACKAGE.$name is not found.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return mixed
     */
    private static function get(ContainerInterface $container, string $name, $default = null)
    {
        $namespacedName = self::PACKAGE . '.' . $name;

        return $container->has($namespacedName) ? $container->get($namespacedName) :
            ($container->has($name) ? $container->get($name) : $default);
    }
}
