<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use PDO;
use PDOStatement;
use Viserio\WebProfiler\DataCollectors\Bridge\PDO\PDODataCollector;
use Viserio\WebProfiler\DataCollectors\Bridge\PDO\TraceablePDODecorater;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class WebProfilerPDOBridgeServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.webprofiler';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            PDO::class                 => [self::class, 'createTraceablePDODecorater'],
            WebProfilerContract::class => [self::class, 'createWebProfiler'],
        ];
    }

    public static function createTraceablePDODecorater(ContainerInterface $container): TraceablePDODecorater
    {
        return new TraceablePDODecorater($container->get(PDO::class));
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        $profiler = $container->get(WebProfilerContract::class);

        if (self::getConfig($container, 'collector.pdo', false)) {
            $profiler->addCollector(new PDODataCollector(
                $container->get(TraceablePDODecorater::class)
            ));
        }

        return $profiler;
    }
}
