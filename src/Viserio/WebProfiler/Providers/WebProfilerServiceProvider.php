<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\WebProfiler\WebProfiler;
use DebugBar\DebugBar;

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

        return $profiler;
    }
}
