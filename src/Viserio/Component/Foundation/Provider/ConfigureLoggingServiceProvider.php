<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Provider;

use Interop\Container\ServiceProviderInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SyslogHandler;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\Log\Log as LogContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Traits\ParseLevelTrait;
use Viserio\Component\Log\Writer;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ConfigureLoggingServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract
{
    use ParseLevelTrait;
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            Writer::class => [self::class, 'extendLogWriter'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'app'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return [
            'name',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'log'  => [
                'handler'   => 'single',
                'level'     => 'debug',
                'max_files' => 5,
            ],
        ];
    }

    /**
     * Extend viserio log writer.
     *
     * @param \Psr\Container\ContainerInterface        $container
     * @param null|\Viserio\Component\Contract\Log\Log $log
     *
     * @return null|\Viserio\Component\Contract\Log\Log
     */
    public static function extendLogWriter(ContainerInterface $container, ?LogContract $log = null): ?LogContract
    {
        if ($log !== null) {
            // Configure the Monolog handlers for the application.
            $options = self::resolveOptions($container);
            $method  = 'configure' . \ucfirst($options['log']['handler']) . 'Handler';

            self::{$method}($container, $log, $options);
        }

        return $log;
    }

    /**
     * Get a default Monolog formatter instance.
     *
     * @return \Monolog\Formatter\LineFormatter
     */
    protected static function getDefaultFormatter(): LineFormatter
    {
        $lineFormatter = new LineFormatter(null, null, true, true);
        $lineFormatter->includeStacktraces();

        return $lineFormatter;
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Psr\Container\ContainerInterface   $container
     * @param \Viserio\Component\Contract\Log\Log $log
     * @param array                               $options
     *
     * @return void
     */
    private static function configureSingleHandler(ContainerInterface $container, LogContract $log, array $options): void
    {
        $log->useFiles(
            $container->get(KernelContract::class)->getStoragePath('logs/narrowspark.log'),
            $options['log']['level'],
            null,
            self::getDefaultFormatter()
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Psr\Container\ContainerInterface   $container
     * @param \Viserio\Component\Contract\Log\Log $log
     * @param array                               $options
     *
     * @return void
     */
    private static function configureDailyHandler(ContainerInterface $container, LogContract $log, array $options): void
    {
        $log->useDailyFiles(
            $container->get(KernelContract::class)->getStoragePath('logs/narrowspark.log'),
            $options['log']['max_files'],
            $options['log']['level'],
            null,
            self::getDefaultFormatter()
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Psr\Container\ContainerInterface   $container
     * @param \Viserio\Component\Contract\Log\Log $log
     * @param array                               $options
     *
     * @return void
     */
    private static function configureErrorlogHandler(ContainerInterface $container, LogContract $log, array $options): void
    {
        $container->get(HandlerParser::class)->parseHandler(
            new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, self::parseLevel($options['log']['level'])),
            '',
            '',
            null,
            'line'
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Psr\Container\ContainerInterface   $container
     * @param \Viserio\Component\Contract\Log\Log $log
     * @param array                               $options
     *
     * @return void
     */
    private static function configureSyslogHandler(ContainerInterface $container, LogContract $log, array $options): void
    {
        $container->get(HandlerParser::class)->parseHandler(
            new SyslogHandler($options['name'], LOG_USER, self::parseLevel($options['log']['level'])),
            '',
            '',
            null,
            'line'
        );
    }
}
