<?php
declare(strict_types=1);
namespace Viserio\Connect\Tests\Providers;

use Viserio\Connect\Providers\ConnectManagerServiceProvider;
use Viserio\Container\Container;
use Viserio\Connect\ConnectManager;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Contracts\Connect\ConnectManager as ConnectManagerContract;

class ConnectManagerServiceProviderTest extends \PHPUnit_Framework_TestCase
{
   public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new ConnectManagerServiceProvider());

        $connect = $container->get(ConnectManager::class);

        $this->assertInstanceOf(ConnectManagerContract::class, $container->get(ConnectManagerContract::class));
        $this->assertInstanceOf(ConnectManager::class, $connect);
        $this->assertInstanceOf(ConnectManager::class, $container->get('connect'));
    }
}
