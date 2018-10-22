<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\View\Factory as FactoryContract;
use Viserio\Component\Filesystem\Provider\FilesServiceProvider;
use Viserio\Component\View\Engine\EngineResolver;
use Viserio\Component\View\Provider\ViewServiceProvider;
use Viserio\Component\View\ViewFactory;
use Viserio\Component\View\ViewFinder;

/**
 * @internal
 */
final class ViewServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->instance('config', [
            'viserio' => [
                'view' => [
                    'paths' => [
                        \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR,
                        __DIR__,
                    ],
                    'extensions' => ['phtml', 'php'],
                ],
            ],
        ]);

        static::assertInstanceOf(FactoryContract::class, $container->get(FactoryContract::class));
        static::assertInstanceOf(FactoryContract::class, $container->get(ViewFactory::class));
        static::assertInstanceOf(FactoryContract::class, $container->get('view'));
        static::assertInstanceOf(ViewFinder::class, $container->get('view.finder'));
        static::assertInstanceOf(ViewFinder::class, $container->get(ViewFinder::class));
        static::assertInstanceOf(EngineResolver::class, $container->get('view.engine.resolver'));
        static::assertInstanceOf(EngineResolver::class, $container->get(EngineResolver::class));
    }
}
