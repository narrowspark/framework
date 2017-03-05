<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Monolog\Handler\ErrorLogHandler;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Log\Log as LogContract;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Traits\ParseLevelTrait;
use Viserio\Component\Log\Writer;

class ConfigureLoggingServiceProvider implements ServiceProvider
{
    use ParseLevelTrait;

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
     * @param \Viserio\Component\Contracts\Log\Log  $log
     *
     * @return void
     */
    private static function configureHandlers(ContainerInterface $container, LogContract $log): void
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
     * @param \Viserio\Component\Contracts\Log\Log  $log
     * @param string                                $level
     *
     * @return void
     */
    private static function configureSingleHandler(ContainerInterface $container, LogContract $log, string $level): void
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
     * @param \Viserio\Component\Contracts\Log\Log  $log
     * @param string                                $level
     *
     * @return void
     */
    private static function configureDailyHandler(ContainerInterface $container, LogContract $log, string $level): void
    {
        $config   = $container->get(RepositoryContract::class);
        $maxFiles = $config->get('app.log_max_files', 5);

        $log->useDailyFiles(
            $config->get('path.storage') . '/logs/narrowspark.log',
            $maxFiles,
            $level
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Viserio\Component\Contracts\Log\Log  $log
     * @param string                                $level
     *
     * @return void
     */
    private static function configureErrorlogHandler(ContainerInterface $container, LogContract $log, string $level): void
    {
        $container->get(HandlerParser::class)->parseHandler(
            new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, self::parseLevel($level)),
            '',
            '',
            null,
            'line'
        );
    }
}
