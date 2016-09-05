<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger as MonologLogger;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Foundation\Application;
use Viserio\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Log\Writer;
use Viserio\Log\Providers\LoggerServiceProvider;

class ConfigureLogging implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        $app->register(new LoggerServiceProvider());

        $log = $this->registerLogger($app);

        // If a custom Monolog configurator has been registered for the application
        // we will call that, passing Monolog along. Otherwise, we will grab the
        // the configurations for the log system and use it for configuration.
        if ($app->hasMonologConfigurator()) {
            call_user_func(
                $app->getMonologConfigurator(), $log->getMonolog()
            );
        } else {
            $this->configureHandlers($app, $log);
        }
    }

    /**
     * Register the logger instance in the container.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     *
     * @return \Viserio\Contracts\Log\Writer
     */
    protected function registerLogger(Application $app): Writer
    {
        $log = $app->get(Writer::class);
        $log->setEventsDispatcher($app->get(DispatcherContract::class));

        return $log;
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     * @param \\Viserio\Contracts\Log\Writer            $log
     */
    protected function configureHandlers(Application $app, Writer $log)
    {
        $config = $app->get(ConfigManager::class);
        $level = $app->get(ConfigManager::class)->get('app.log_level', 'debug');

        $method = 'configure' . ucfirst($config->get('app.log')) . 'Handler';

        $this->{$method}($app, $log, $level);
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     * @param \\Viserio\Contracts\Log\Writer            $log
     * @param string                                    $level
     */
    protected function configureSingleHandler(Application $app, Writer $log, string $level)
    {
        $log->useFiles(
            $app->storagePath() . '/logs/narrowspark.log',
            $level
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     * @param \\Viserio\Contracts\Log\Writer            $log
     * @param string                                    $level
     */
    protected function configureDailyHandler(Application $app, Writer $log, string $level)
    {
        $config = $app->get(ConfigManager::class);
        $maxFiles = $config->get('app.log_max_files');

        $log->useDailyFiles(
            $app->storagePath() . '/logs/narrowspark.log',
            is_null($maxFiles) ? 5 : $maxFiles,
            $level
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     * @param \\Viserio\Contracts\Log\Writer            $log
     * @param string                                    $level
     */
    protected function configureErrorlogHandler(Application $app, Writer $log, string $level)
    {
        $log->getHandlerParser()->parseHandler(
            new ErrorLogHandler(
                ErrorLogHandler::OPERATING_SYSTEM,
                $level
            ),
            '',
            '',
            null,
            'line'
        );
    }
}
