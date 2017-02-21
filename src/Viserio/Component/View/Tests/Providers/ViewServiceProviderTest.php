<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Providers;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Filesystem\Providers\FilesServiceProvider;
use Viserio\Component\View\Engines\EngineResolver;
use Viserio\Component\View\Factory;
use Viserio\Component\View\Providers\ViewServiceProvider;
use Viserio\Component\View\ViewFinder;

class ViewServiceProviderTest extends MockeryTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->instance('config', [
            'viserio' => [
                'view' => [
                    'paths'      => [
                        __DIR__ . '/../Fixture/',
                        __DIR__,
                    ],
                    'extensions' => ['phtml', 'php'],
                ],
            ],
        ]);

        self::assertInstanceOf(FactoryContract::class, $container->get(FactoryContract::class));
        self::assertInstanceOf(FactoryContract::class, $container->get(Factory::class));
        self::assertInstanceOf(FactoryContract::class, $container->get('view'));
        self::assertInstanceOf(ViewFinder::class, $container->get('view.finder'));
        self::assertInstanceOf(ViewFinder::class, $container->get(ViewFinder::class));
        self::assertInstanceOf(EngineResolver::class, $container->get('view.engine.resolver'));
        self::assertInstanceOf(EngineResolver::class, $container->get(EngineResolver::class));
    }
}
