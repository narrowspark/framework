<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Commands\CleanCommand;
use Viserio\Bridge\Twig\Commands\DebugCommand;
use Viserio\Bridge\Twig\Commands\LintCommand;
use Viserio\Bridge\Twig\Providers\TwigBridgeCommandsServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class TwigBridgeCommandsServiceProviderTest extends TestCase
{
    public function testGetServices()
    {
        $container = new Container();
        $container->register(new TwigBridgeCommandsServiceProvider());
        $container->register(new OptionsResolverServiceProvider());

        static::assertEquals(
            [
                new CleanCommand(),
                new DebugCommand(),
                new LintCommand(),
            ],
            $container->get('twig.commands')
        );
    }
}
