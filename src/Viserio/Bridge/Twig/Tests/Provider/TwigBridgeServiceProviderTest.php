<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Lexer;
use Viserio\Bridge\Twig\Provider\TwigBridgeServiceProvider;
use Viserio\Component\Container\Container;

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
