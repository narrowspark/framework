<?php
declare(strict_types=1);
namespace Viserio\Provider\Doctrine\Tests\Provider;

use Doctrine\DBAL\Tools\Console\Command\ImportCommand;
use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Provider\Doctrine\Provider\ConsoleCommandsServiceProvider;
use Viserio\Provider\Doctrine\Provider\DoctrineDBALServiceProvider;

/**
 * @internal
 */
final class ConsoleCommandsServiceProviderTest extends TestCase
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

        $this->assertInstanceOf(ImportCommand::class, $commands['dbal:import']);
        $this->assertInstanceOf(ReservedWordsCommand::class, $commands['dbal:reserved-words']);
        $this->assertInstanceOf(RunSqlCommand::class, $commands['dbal:run-sql']);
    }
}
