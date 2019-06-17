<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Doctrine\ORM\Provider\ConsoleCommandsServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;

/**
 * @internal
 */
final class ConsoleCommandsServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $entityManager = $this->mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getConnection')
            ->once()
            ->andReturn($this->mock(Connection::class));
        $container = new Container();
        $container->instance(EntityManagerInterface::class, $entityManager);
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'console' => [
                    'version' => '1',
                ],
            ],
        ]);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        $this->assertInstanceOf(MetadataCommand::class, $commands['orm:clear-cache:metadata']);
        $this->assertInstanceOf(QueryCommand::class, $commands['orm:clear-cache:query']);
        $this->assertInstanceOf(ResultCommand::class, $commands['orm:clear-cache:result']);
        $this->assertInstanceOf(ConvertDoctrine1SchemaCommand::class, $commands['orm:convert-d1-schema']);
        $this->assertInstanceOf(ConvertMappingCommand::class, $commands['orm:convert-mapping']);
        $this->assertInstanceOf(EnsureProductionSettingsCommand::class, $commands['orm:ensure-production-settings']);
        $this->assertInstanceOf(GenerateRepositoriesCommand::class, $commands['orm:generate-repositories']);
        $this->assertInstanceOf(InfoCommand::class, $commands['orm:info']);
        $this->assertInstanceOf(MappingDescribeCommand::class, $commands['orm:mapping:describe']);
        $this->assertInstanceOf(RunDqlCommand::class, $commands['orm:run-dql']);
        $this->assertInstanceOf(CreateCommand::class, $commands['orm:schema-tool:create']);
        $this->assertInstanceOf(DropCommand::class, $commands['orm:schema-tool:drop']);
        $this->assertInstanceOf(UpdateCommand::class, $commands['orm:schema-tool:update']);
        $this->assertInstanceOf(ValidateSchemaCommand::class, $commands['orm:validate-schema']);
    }
}
