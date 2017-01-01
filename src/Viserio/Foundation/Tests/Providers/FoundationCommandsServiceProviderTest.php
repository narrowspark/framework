<?php
declare(strict_types=1);
namespace Viserio\Foundation\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Foundation\Commands\DownCommand;
use Viserio\Foundation\Commands\KeyGenerateCommand;
use Viserio\Foundation\Commands\UpCommand;
use Viserio\Foundation\Providers\FoundationCommandsServiceProvider;

class FoundationCommandsServiceProviderTest extends \PHPUnit_Framework_TestCase
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
