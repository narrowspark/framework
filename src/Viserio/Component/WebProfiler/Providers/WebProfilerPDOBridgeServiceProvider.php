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

    /**
     * Extend PDO with our TraceablePDODecorater.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\WebProfiler\DataCollectors\Bridge\PDO\TraceablePDODecorater
     */
    public static function createTraceablePDODecorater(ContainerInterface $container, ?callable $getPrevious = null): ?TraceablePDODecorater
    {
        if ($getPrevious !== null) {
            $pdo = $getPrevious();

            return new TraceablePDODecorater($pdo);
        }

        return null;
    }

    /**
     * Extend viserio profiler with data collector.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\WebProfiler\WebProfiler
     */
    public static function createWebProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?WebProfilerContract
    {
        if ($getPrevious !== null) {
            $profiler = $getPrevious();

            $profiler->addCollector(new PDODataCollector(
                $container->get(TraceablePDODecorater::class)
            ));

            return $profiler;
        }

        return null;
    }
}
