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
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class ConfigureLoggingServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract
{
    use ParseLevelTrait;

    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Writer::class => [self::class, 'createConfiguredWriter'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'app'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'path' => [
                'storage'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'log' => [
                'handler' => 'single',
                'level' => 'debug',
                'max_files' => 5,
            ]
        ];
    }


    /**
     * Extend viserio log writer.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\VViserio\Component\Log\Writer
     */
    public static function createConfiguredWriter(ContainerInterface $container, ?callable $getPrevious = null): ?Writer
    {
        if ($getPrevious !== null) {
            $log = $getPrevious();

            self::resolveOptions($container);
            self::configureHandlers($container, $log);

            return $log;
        }
        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
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
        $method = 'configure' . ucfirst(self::$options['log']['handler']) . 'Handler';

        self::{$method}($container, $log, self::$options['log']['level']);
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
            self::$options['path']['storage'] . '/logs/narrowspark.log',
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
        $log->useDailyFiles(
            self::$options['path']['storage'] . '/logs/narrowspark.log',
            self::$options['log']['max_files'],
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

    /**
     * Resolve component options.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (self::$options === null) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
