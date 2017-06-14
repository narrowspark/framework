<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Profiler\Profile;
use Viserio\Bridge\Twig\Bridge\TwigProviderDataCollectorsServiceProvider;
use Viserio\Bridge\Twig\Bridge\TwigProviderServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Filesystem\Bridge\FilesServiceProvider;
use Viserio\Component\HttpFactory\Bridge\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\Bridge\ProfilerServiceProvider;
use Viserio\Component\View\Bridge\ViewServiceProvider;

class TwigBridgeDataCollectorsServiceProviderTest extends MockeryTestCase
{
    public function testGetServices()
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new TwigProviderServiceProvider());
        $container->register(new TwigProviderDataCollectorsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'profiler' => [
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

        $profiler = $container->get(ProfilerContract::class);

        self::assertInstanceOf(ProfilerContract::class, $profiler);

        self::assertTrue(array_key_exists('time-data-collector', $profiler->getCollectors()));
        self::assertTrue(array_key_exists('memory-data-collector', $profiler->getCollectors()));
        self::assertTrue(array_key_exists('twig-data-collector', $profiler->getCollectors()));

        self::assertInstanceOf(Profile::class, $container->get(Profile::class));
        self::assertInstanceOf(Environment::class, $container->get(Environment::class));
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
