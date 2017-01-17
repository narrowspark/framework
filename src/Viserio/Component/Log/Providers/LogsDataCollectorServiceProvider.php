<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Log\DataCollectors\LogParser;
use Viserio\Component\Log\DataCollectors\LogsDataCollector;

class LogsDataCollectorServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.webprofiler';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            LogParser::class         => [self::class, 'createLogParser'],
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
            $default = $container->get(RepositoryContract::class)->get('path.storage') . '/logs/';
        }

        return new LogsDataCollector(
            $container->get(LogParser::class),
            self::getConfig($container, 'storages', $default)
        );
    }
}
