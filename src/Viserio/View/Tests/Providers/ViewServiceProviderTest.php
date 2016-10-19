<?php
declare(strict_types=1);
namespace Viserio\View\Tests\Providers;

use Defuse\Crypto\Key;
use Viserio\Container\Container;
use Viserio\Filesystem\Providers\FilesServiceProvider;
use Viserio\View\Engines\EngineResolver;
use Viserio\View\Factory;
use Viserio\View\Providers\ViewServiceProvider;
use Viserio\View\ViewFinder;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ServerRequestInterface;

class ViewServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testProvider()
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());

        $container->instance(ServerRequestInterface::class, $this->mock(ServerRequestInterface::class));

        $this->assertInstanceOf(Factory::class, $container->get(Factory::class));
        $this->assertInstanceOf(Factory::class, $container->get('view'));
        $this->assertInstanceOf(ViewFinder::class, $container->get('view.finder'));
        $this->assertInstanceOf(ViewFinder::class, $container->get(ViewFinder::class));
        $this->assertInstanceOf(EngineResolver::class, $container->get('view.engine.resolver'));
        $this->assertInstanceOf(EngineResolver::class, $container->get(EngineResolver::class));
    }
}
