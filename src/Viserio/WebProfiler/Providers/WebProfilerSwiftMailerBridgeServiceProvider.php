<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Swift_Mailer;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\WebProfiler\DataCollectors\Bridge\SwiftMailDataCollector;

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

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        $profiler = $container->get(WebProfilerContract::class);

        $profiler->addCollector(new SwiftMailDataCollector(
            $container->get(Swift_Mailer::class)
        ));

        return $profiler;
    }
}
