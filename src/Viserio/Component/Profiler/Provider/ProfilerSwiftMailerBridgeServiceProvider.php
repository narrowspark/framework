<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Provider;

use Psr\Container\ContainerInterface;
use Swift_Mailer;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Profiler\DataCollector\Bridge\SwiftMailDataCollector;

class ProfilerSwiftMailerBridgeServiceProvider implements ServiceProviderContract
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
            ProfilerContract::class => [self::class, 'extendProfiler'],
        ];
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
            $profiler->addCollector(new SwiftMailDataCollector(
                $container->get(Swift_Mailer::class)
            ));
        }

        return $profiler;
    }
}
