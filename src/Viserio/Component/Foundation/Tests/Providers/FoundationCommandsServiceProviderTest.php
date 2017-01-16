<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Foundation\Commands\DownCommand;
use Viserio\Component\Foundation\Commands\KeyGenerateCommand;
use Viserio\Component\Foundation\Commands\UpCommand;
use Viserio\Component\Foundation\Providers\FoundationCommandsServiceProvider;

class FoundationCommandsServiceProviderTest extends TestCase
{
    public function testGetServices()
    {
        $container = new Container();
        $container->register(new FoundationCommandsServiceProvider());

        static::assertEquals(
            [
                // new DownCommand(),
                new UpCommand(),
                // new KeyGenerateCommand(),
            ],
            $container->get('maintenance.commands')
        );
    }
}
