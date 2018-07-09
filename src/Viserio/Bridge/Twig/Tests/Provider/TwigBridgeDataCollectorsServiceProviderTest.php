<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Profiler\Profile;
use Viserio\Bridge\Twig\Provider\TwigBridgeDataCollectorsServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Filesystem\Provider\FilesServiceProvider;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
use Viserio\Component\View\Provider\ViewServiceProvider;

/**
 * @internal
 */
final class TwigBridgeDataCollectorsServiceProviderTest extends MockeryTestCase
{
    public function testGetServices(): void
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new TwigBridgeDataCollectorsServiceProvider());

        $container->instance(Environment::class, new Environment(new ArrayLoader([])));

        $container->instance('config', [
            'viserio' => [
                'profiler' => [
                    'enable'    => true,
                    'collector' => [
                        'twig' => true,
                    ],
                ],
                'view' => [
                    'paths' => [
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

        static::assertInstanceOf(ProfilerContract::class, $profiler);

        static::assertArrayHasKey('time-data-collector', $profiler->getCollectors());
        static::assertArrayHasKey('memory-data-collector', $profiler->getCollectors());
        static::assertArrayHasKey('twig-data-collector', $profiler->getCollectors());

        static::assertInstanceOf(Profile::class, $container->get(Profile::class));
        static::assertInstanceOf(Environment::class, $container->get(Environment::class));
    }

    private function getRequest()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->with('request_time_float')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('request_time')
            ->andReturn(false);

        return $request;
    }
}
