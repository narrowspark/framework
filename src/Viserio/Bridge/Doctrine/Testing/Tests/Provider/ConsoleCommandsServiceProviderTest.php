<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\DBAL\Provider\DoctrineDBALServiceProvider;
use Viserio\Bridge\Doctrine\Testing\Provider\ConsoleCommandsServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new DoctrineDBALServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'console' => [
                    'version' => '1',
                ],
                'doctrine' => [
                    'default'     => 'mysql',
                    'connections' => [
                        'mysql' => [
                            'driver'        => 'pdo_mysql',
                            'host'          => 'DB_HOST',
                            'port'          => 'DB_PORT',
                            'database'      => 'DB_DATABASE_NAME',
                            'username'      => 'DB_DATABASE_USER',
                            'password'      => 'DB_DATABASE_PASSWORD',
                            'charset'       => 'DB_CHARSET', 'UTF8',
                            'driverOptions' => [1002 => 'SET NAMES utf8'],
                        ],
                    ],
                ],
            ],
        ]);

        $console  = $container->get(Application::class);
        $commands = $console->all();
    }
}
