<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Providers;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Twig_Environment;
use Twig_Profiler_Profile;
use Viserio\Bridge\Twig\Providers\TwigBridgeDataCollectorsServiceProvider;
use Viserio\Bridge\Twig\Providers\TwigBridgeServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\Filesystem\Providers\FilesServiceProvider;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\View\Providers\ViewServiceProvider;
use Viserio\Component\WebProfiler\Providers\WebProfilerServiceProvider;

/**
 * @runTestsInSeparateProcesses
 */
class TwigBridgeDataCollectorsServiceProviderTest extends MockeryTestCase
{
    public function testGetServices()
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new WebProfilerServiceProvider());
        $container->register(new TwigBridgeServiceProvider());
        $container->register(new TwigBridgeDataCollectorsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'webprofiler' => [
                    'enable'    => true,
                    'collector' => [
                        'twig'  => true,
                    ],
                ],
                'view' => [
                    'paths'      => [
                        __DIR__ . '/../Fixture/',
                        __DIR__,
                    ],
                    'engines' => [
                        'twig' => [
                            'options' => [
                                'debug' => true,
                            ],
                            'file_extension' => 'html',
                            'templates'      => [
                                'test.html' => 'tests',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $profiler = $container->get(WebProfilerContract::class);

        static::assertInstanceOf(WebProfilerContract::class, $profiler);

        static::assertTrue(array_key_exists('time-data-collector', $profiler->getCollectors()));
        static::assertTrue(array_key_exists('memory-data-collector', $profiler->getCollectors()));
        static::assertTrue(array_key_exists('twig-data-collector', $profiler->getCollectors()));

        self::assertInstanceOf(Twig_Profiler_Profile::class, $container->get(Twig_Profiler_Profile::class));
        self::assertInstanceOf(Twig_Environment::class, $container->get(Twig_Environment::class));
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
