<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Swift_Mailer;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Profiler\DataCollector\Bridge\SwiftMailDataCollector;

class ProfilerSwiftMailerBridgeServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ProfilerContract::class => [self::class, 'createProfiler'],
        ];
    }

    /**
     * Extend viserio profiler with data collector.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\Profiler\Profiler
     */
    public static function createProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
    {
        $profiler = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($profiler !== null) {
            $profiler->addCollector(new SwiftMailDataCollector(
                $container->get(Swift_Mailer::class)
            ));
        }

        return $profiler;
    }
}
