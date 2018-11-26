<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\DBAL\Tests\Provider;

use Doctrine\DBAL\Tools\Console\Command\ImportCommand;
use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\DBAL\Provider\ConsoleCommandsServiceProvider;
use Viserio\Bridge\Doctrine\DBAL\Provider\DoctrineDBALServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices(): void
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new DoctrineDBALServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'doctrine' => [
                    'dbal' => [
                        'default' => 'mysql',
                    ],
                ],
            ],
        ]);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        self::assertInstanceOf(ImportCommand::class, $commands['dbal:import']);
        self::assertInstanceOf(ReservedWordsCommand::class, $commands['dbal:reserved-words']);
        self::assertInstanceOf(RunSqlCommand::class, $commands['dbal:run-sql']);
    }
}
