<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Lexer;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Engine\TwigEngine;
use Viserio\Bridge\Twig\Loader as TwigLoader;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Bridge\Twig\Provider\TwigBridgeServiceProvider;

class TwigBridgeServiceProviderTest extends MockeryTestCase
{
    public function testProviderIsNull()
    {
        $container = new Container();
        $container->register(new TwigBridgeServiceProvider());
        $container->instance(Lexer::class, $this->mock(Lexer::class));

        self::assertNull($container->get(Environment::class));
    }
}
