<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Bridge\Twig\Commands\CleanCommand;
use Viserio\Bridge\Twig\Commands\DebugCommand;
use Viserio\Bridge\Twig\Commands\LintCommand;
use Viserio\Bridge\Twig\Providers\TwigBridgeCommandsServiceProvider;

class TwigBridgeCommandsServiceProviderTest extends TestCase
{
    public function testGetServices()
    {
        $container = new Container();
        $container->register(new TwigBridgeCommandsServiceProvider());

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
