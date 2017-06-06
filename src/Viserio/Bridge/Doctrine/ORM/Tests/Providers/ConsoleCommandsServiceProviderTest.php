<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Providers;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Doctrine\ORM\Providers\ConsoleCommandsServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class ConsoleCommandsServiceProviderTest extends MockeryTestCase
{
    public function testProvider()
    {
        $entityManager = $this->mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getConnection')
            ->once()
            ->andReturn($this->mock(Connection::class));
        $container = new Container();
        $container->instance(EntityManagerInterface::class, $entityManager);
        $container->register(new ConsoleServiceProvider());
        $container->register(new OptionsResolverServiceProvider());
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

        self::assertInstanceOf(MetadataCommand::class, $commands['orm:clear-cache:metadata']);
        self::assertInstanceOf(QueryCommand::class, $commands['orm:clear-cache:query']);
        self::assertInstanceOf(ResultCommand::class, $commands['orm:clear-cache:result']);
        self::assertInstanceOf(ConvertDoctrine1SchemaCommand::class, $commands['orm:convert-d1-schema']);
        self::assertInstanceOf(ConvertMappingCommand::class, $commands['orm:convert-mapping']);
        self::assertInstanceOf(EnsureProductionSettingsCommand::class, $commands['orm:ensure-production-settings']);
        self::assertInstanceOf(GenerateProxiesCommand::class, $commands['orm:generate-proxies']);
        self::assertInstanceOf(GenerateRepositoriesCommand::class, $commands['orm:generate-repositories']);
        self::assertInstanceOf(InfoCommand::class, $commands['orm:info']);
        self::assertInstanceOf(MappingDescribeCommand::class, $commands['orm:mapping:describe']);
        self::assertInstanceOf(RunDqlCommand::class, $commands['orm:run-dql']);
        self::assertInstanceOf(CreateCommand::class, $commands['orm:schema-tool:create']);
        self::assertInstanceOf(DropCommand::class, $commands['orm:schema-tool:drop']);
        self::assertInstanceOf(UpdateCommand::class, $commands['orm:schema-tool:update']);
        self::assertInstanceOf(ValidateSchemaCommand::class, $commands['orm:validate-schema']);
    }
}
