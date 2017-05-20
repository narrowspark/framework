<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use PDO;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Profiler\DataCollectors\Bridge\PDO\PDODataCollector;
use Viserio\Component\Profiler\DataCollectors\Bridge\PDO\TraceablePDODecorater;

class ProfilerPDOBridgeServiceProvider implements ServiceProvider
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
            ProfilerContract::class   => [self::class, 'createProfiler'],
        ];
    }

    /**
     * Extend PDO with our TraceablePDODecorater.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Profiler\DataCollectors\Bridge\PDO\TraceablePDODecorater
     */
    public static function createTraceablePDODecorater(ContainerInterface $container, ?callable $getPrevious = null): ?TraceablePDODecorater
    {
        $pdo = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($pdo !== null) {
            return new TraceablePDODecorater($pdo);
        }

        return $pdo;
    }

    /**
     * Extend viserio profiler with data collector.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\Profiler\Profiler
     */
    public static function createProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
    {
        $profiler = $getPrevious();

        if ($profiler !== null) {
            $profiler->addCollector(new PDODataCollector(
                $container->get(TraceablePDODecorater::class)
            ));

            return $profiler;
        }

        return $profiler;
    }
}
