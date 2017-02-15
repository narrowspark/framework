<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use PDO;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\WebProfiler\DataCollectors\Bridge\PDO\PDODataCollector;
use Viserio\Component\WebProfiler\DataCollectors\Bridge\PDO\TraceablePDODecorater;

class WebProfilerPDOBridgeServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            PDO::class                   => [self::class, 'createTraceablePDODecorater'],
            TraceablePDODecorater::class => function (ContainerInterface $container) {
                return $container->get(PDO::class);
            },
            WebProfilerContract::class   => [self::class, 'createWebProfiler'],
        ];
    }

    public static function createTraceablePDODecorater(ContainerInterface $container): TraceablePDODecorater
    {
        return new TraceablePDODecorater($container->get(PDO::class));
    }

    public static function createWebProfiler(ContainerInterface $container, callable $getPrevious): WebProfilerContract
    {
        $profiler = $getPrevious();

        $profiler->addCollector(new PDODataCollector(
            $container->get(TraceablePDODecorater::class)
        ));

        return $profiler;
    }
}
