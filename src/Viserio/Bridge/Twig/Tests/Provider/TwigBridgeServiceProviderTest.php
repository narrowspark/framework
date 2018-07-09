<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Lexer;
use Viserio\Bridge\Twig\Provider\TwigBridgeServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Contract\Translation\TranslationManager as TranslationManagerContract;

/**
 * @internal
 */
final class TwigBridgeServiceProviderTest extends MockeryTestCase
{
    public function testProviderIsNull(): void
    {
        $container = new Container();
        $container->register(new TwigBridgeServiceProvider());
        $container->instance(StoreContract::class, $this->mock(StoreContract::class));
        $container->instance(RepositoryContract::class, $this->mock(RepositoryContract::class));
        $container->instance(TranslationManagerContract::class, $this->mock(TranslationManagerContract::class));
        $container->instance(Lexer::class, $this->mock(Lexer::class));

        static::assertNull($container->get(Environment::class));
    }
}
