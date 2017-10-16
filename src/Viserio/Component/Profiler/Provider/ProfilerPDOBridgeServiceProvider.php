<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Provider;

use Interop\Container\ServiceProviderInterface;
use PDO;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Profiler\DataCollector\Bridge\PDO\PDODataCollector;
use Viserio\Component\Profiler\DataCollector\Bridge\PDO\TraceablePDODecorater;

class ProfilerPDOBridgeServiceProvider implements ServiceProviderInterface
{
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
            PDO::class                   => [self::class, 'createTraceablePDODecorater'],
            TraceablePDODecorater::class => function (ContainerInterface $container) {
                return $container->get(PDO::class);
            },
            ProfilerContract::class => [self::class, 'extendProfiler'],
        ];
    }

    /**
     * Extend PDO with our TraceablePDODecorater.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|\PDO                         $pdo
     *
     * @return null|\Viserio\Component\Profiler\DataCollector\Bridge\PDO\TraceablePDODecorater
     */
    public static function createTraceablePDODecorater(ContainerInterface $container, ?PDO $pdo = null): ?TraceablePDODecorater
    {
        if ($pdo === null) {
            return null;
        }

        return new TraceablePDODecorater($pdo);
    }

    /**
     * Extend viserio profiler with data collector.
     *
     * @param \Psr\Container\ContainerInterface                  $container
     * @param null|\Viserio\Component\Contract\Profiler\Profiler $profiler
     *
     * @return null|\Viserio\Component\Contract\Profiler\Profiler
     */
    public static function extendProfiler(
        ContainerInterface $container,
        ?ProfilerContract $profiler = null
    ): ?ProfilerContract {
        if ($profiler !== null) {
            $profiler->addCollector(new PDODataCollector(
                $container->get(TraceablePDODecorater::class)
            ));
        }

        return $profiler;
    }
}
