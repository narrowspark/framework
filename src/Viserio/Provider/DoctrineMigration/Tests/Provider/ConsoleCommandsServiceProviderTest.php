<?php
declare(strict_types=1);
namespace Viserio\Provider\Doctrine\Migration\Tests\Provider;

use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Provider\Doctrine\DBAL\Provider\DoctrineDBALServiceProvider;
use Viserio\Provider\Doctrine\Migration\Provider\ConsoleCommandsServiceProvider;

/**
 * @internal
 */
final class ConsoleCommandsServiceProviderTest extends TestCase
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
                    'dbal' => [
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
                    'migrations' => [
                        'path'       => __DIR__ . '/../Stub/',
                        'namespace'  => 'Database\\Migrations',
                        'name'       => 'migration',
                        'table_name' => 'migration',
                    ],
                ],
            ],
        ]);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        $this->assertInstanceOf(DiffCommand::class, $commands['migrations:diff']);
        $this->assertInstanceOf(ExecuteCommand::class, $commands['migrations:execute']);
        $this->assertInstanceOf(GenerateCommand::class, $commands['migrations:generate']);
        $this->assertInstanceOf(MigrateCommand::class, $commands['migrations:migrate']);
        $this->assertInstanceOf(StatusCommand::class, $commands['migrations:status']);
        $this->assertInstanceOf(VersionCommand::class, $commands['migrations:version']);
    }
}
