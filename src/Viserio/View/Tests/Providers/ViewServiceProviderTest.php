<?php
declare(strict_types=1);
namespace Viserio\View\Tests\Providers;

use Defuse\Crypto\Key;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Filesystem\Providers\FilesServiceProvider;
use Viserio\View\Engines\EngineResolver;
use Viserio\View\Factory;
use Viserio\View\Providers\ViewServiceProvider;
use Viserio\View\ViewFinder;

class ViewServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());

        $key = Key::createNewRandomKey();

        $container->get('config')->set('view.template.paths', []);

        $this->assertInstanceOf(Factory::class, $container->get(Factory::class));
        $this->assertInstanceOf(Factory::class, $container->get('view'));
        $this->assertInstanceOf(ViewFinder::class, $container->get('view.finder'));
        $this->assertInstanceOf(ViewFinder::class, $container->get(ViewFinder::class));
        $this->assertInstanceOf(EngineResolver::class, $container->get('view.engine.resolver'));
        $this->assertInstanceOf(EngineResolver::class, $container->get(EngineResolver::class));
    }
}
