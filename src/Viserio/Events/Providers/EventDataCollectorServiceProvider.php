<?php
declare(strict_types=1);
namespace Viserio\Events\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Events\DataCollectors\ViserioEventDataCollector;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Psr\Http\Message\ServerRequestInterface;

class EventDataCollectorServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.webprofiler';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            WebProfilerContract::class => [self::class, 'createWebProfiler'],
        ];
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        $profiler = $container->get(WebProfilerContract::class);

        if (self::getConfig($container, 'collector.events', false)) {
            $profiler->addCollector(new ViserioEventDataCollector(
                $container->get(ServerRequestInterface::class),
                $container->get(EventManagerContract::class)
            ));
        }

        return $profiler;
    }
}
