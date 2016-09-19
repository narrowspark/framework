<?php
declare(strict_types=1);
namespace Viserio\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Monolog\Handler\ErrorLogHandler;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Log\Log as LogContract;
use Viserio\Log\Writer;

class ConfigureLoggingProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.log.configured';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Writer::class => [self::class, 'createConfiguredLogging'],
        ];
    }

    public static function createConfiguredLogging(ContainerInterface $container)
    {
        $log = self::registerLogger($container);

        self::configureHandlers($container, $log);

        return $log;
    }

    /**
     * Register the logger instance in the container.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return \Viserio\Contracts\Log\Log
     */
    protected static function registerLogger(ContainerInterface $container): LogContract
    {
        $log = $container->get(Writer::class);
        $log->setEventsDispatcher($container->get(DispatcherContract::class));

        return $log;
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Viserio\Contracts\Log\Log            $log
     */
    protected static function configureHandlers(ContainerInterface $container, LogContract $log)
    {
        $config = $container->get(ConfigManager::class);
        $level = $config->get('app.log_level', 'debug');

        $method = 'configure' . ucfirst($config->get('app.log')) . 'Handler';

        self::{$method}($container, $log, $level);
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Viserio\Contracts\Log\Log            $log
     * @param string                                $level
     */
    protected static function configureSingleHandler(ContainerInterface $container, LogContract $log, string $level)
    {
        $log->useFiles(
            $container->get(ConfigManager::class)->get('path.storage') . '/logs/narrowspark.log',
            $level
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Viserio\Contracts\Log\Log            $log
     * @param string                                $level
     */
    protected static function configureDailyHandler(ContainerInterface $container, LogContract $log, string $level)
    {
        $config = $container->get(ConfigManager::class);
        $maxFiles = $config->get('app.log_max_files');

        $log->useDailyFiles(
            $container->get(ConfigManager::class)->get('path.storage') . '/logs/narrowspark.log',
            is_null($maxFiles) ? 5 : $maxFiles,
            $level
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Viserio\Contracts\Log\Log            $log
     * @param string                                $level
     */
    protected static function configureErrorlogHandler(ContainerInterface $container, LogContract $log, string $level)
    {
        $log->getHandlerParser()->parseHandler(
            new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $level),
            '',
            '',
            null,
            'line'
        );
    }
}
