<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use Swift_Mailer;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\WebProfiler\DataCollectors\Bridge\SwiftMailDataCollector;

class WebProfilerSwiftMailerBridgeServiceProvider implements ServiceProvider
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

        $profiler->addCollector(new SwiftMailDataCollector(
            $container->get(Swift_Mailer::class)
        ));

        return $profiler;
    }

    private function getRequest()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->with('REQUEST_TIME_FLOAT')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('REQUEST_TIME')
            ->andReturn(false);

        return $request;
    }
}
