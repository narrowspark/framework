<?php
declare(strict_types=1);
namespace Viserio\View\Tests\Providers;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Container\Container;
use Viserio\Filesystem\Providers\FilesServiceProvider;
use Viserio\View\Engines\EngineResolver;
use Viserio\View\Factory;
use Viserio\View\Providers\ViewServiceProvider;
use Viserio\View\ViewFinder;

class ViewServiceProviderTest extends TestCase
{
    use MockeryTrait;

    public function testProvider()
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());

        $container->instance(ServerRequestInterface::class, $this->mock(ServerRequestInterface::class));

        self::assertInstanceOf(Factory::class, $container->get(Factory::class));
        self::assertInstanceOf(Factory::class, $container->get('view'));
        self::assertInstanceOf(ViewFinder::class, $container->get('view.finder'));
        self::assertInstanceOf(ViewFinder::class, $container->get(ViewFinder::class));
        self::assertInstanceOf(EngineResolver::class, $container->get('view.engine.resolver'));
        self::assertInstanceOf(EngineResolver::class, $container->get(EngineResolver::class));
    }
}
