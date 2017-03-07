<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Tests\Providers;

use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\DBAL\Providers\DoctrineDBALServiceProvider;
use Viserio\Bridge\Doctrine\Migration\Providers\ConsoleCommandsServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
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

        self::assertInstanceOf(DiffCommand::class, $commands['migrations:diff']);
        self::assertInstanceOf(ExecuteCommand::class, $commands['migrations:execute']);
        self::assertInstanceOf(GenerateCommand::class, $commands['migrations:generate']);
        self::assertInstanceOf(MigrateCommand::class, $commands['migrations:migrate']);
        self::assertInstanceOf(StatusCommand::class, $commands['migrations:status']);
        self::assertInstanceOf(VersionCommand::class, $commands['migrations:version']);
    }
}
