<?php
declare(strict_types=1);
namespace Viserio\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Monolog\Handler\ErrorLogHandler;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Log\Log as LogContract;
use Viserio\Log\Writer;

class ConfigureLoggingServiceProvider implements ServiceProvider
{
    public const PACKAGE = 'viserio.log.configured';

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
        $log = $container->get(Writer::class);

        self::configureHandlers($container, $log);

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
        $config = $container->get(RepositoryContract::class);
        $level  = $config->get('app.log_level', 'debug');

        $method = 'configure' . ucfirst($config->get('app.log', 'single')) . 'Handler';

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
            $container->get(RepositoryContract::class)->get('path.storage') . '/logs/narrowspark.log',
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
        $config   = $container->get(RepositoryContract::class);
        $maxFiles = $config->get('app.log_max_files', 5);

        $log->useDailyFiles(
            $container->get(RepositoryContract::class)->get('path.storage') . '/logs/narrowspark.log',
            $maxFiles,
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
