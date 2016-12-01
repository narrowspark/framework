<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use DebugBar\DebugBar;
use DebugBar\DebugBar;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\StreamFactoryInterface;
use Viserio\WebProfiler\WebProfiler;

class WebProfilerServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            WebProfiler::class => [self::class, 'createWebProfiler'],
            DebugBar::class => function (ContainerInterface $container) {
                return $container->get(WebProfiler::class);
            },
        ];
    }

    public static function createWebProfiler(ContainerInterface $container)
    {
        $profiler = new WebProfiler();
        $profiler->setStreamFactory(
            $container->get(StreamFactoryInterface::class)->createStream()
        );

        return $profiler;
    }
}
