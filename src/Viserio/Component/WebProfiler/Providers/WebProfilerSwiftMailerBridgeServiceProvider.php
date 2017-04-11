<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Swift_Mailer;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\WebProfiler\DataCollectors\Bridge\SwiftMailDataCollector;

class WebProfilerSwiftMailerBridgeServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            WebProfilerContract::class => [self::class, 'createWebProfiler'],
        ];
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
        $profiler = $getPrevious();

        if ($profiler !== null) {
            $profiler->addCollector(new SwiftMailDataCollector(
                $container->get(Swift_Mailer::class)
            ));

            return $profiler;
        }

        return $profiler;
    }
}
