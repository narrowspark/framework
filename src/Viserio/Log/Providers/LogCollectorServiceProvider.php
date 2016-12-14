<?php
declare(strict_types=1);
namespace Viserio\Log\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Log\DataCollectors\LogParser;
use Viserio\Log\DataCollectors\LogsDataCollector;

class LogCollectorServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.webprofiler';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            LogParser::class => [self::class, 'createLogParser'],
            LogsDataCollector::class => [self::class, 'createLogsDataCollector'],
        ];
    }

    public static function createLogParser(ContainerInterface $container): LogParser
    {
        return new LogParser();
    }

    public static function createLogsDataCollector(ContainerInterface $container): LogsDataCollector
    {
        $default = '';

        if ($container->has(RepositoryContract::class)) {
            $default = $container->get(RepositoryContract::class)->get('path.storage') . '/logs/*';
        }

        return new LogsDataCollector(
            $container->get(LogParser::class),
            self::getConfig($container, 'storages', $default)
        );
    }
}
