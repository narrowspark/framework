<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Providers;

use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
// use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
// use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
// use Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand;
// use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
// use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;
// use Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand;
// use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
// use Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand;
// use Doctrine\ORM\Tools\Console\Command\InfoCommand;
// use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
// use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
// use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
// use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
// use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
// use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Bridge\Doctrine\ORM\Providers\ConsoleCommandsServiceProvider;

class ConsoleCommandsServiceProviderTest extends MockeryTestCase
{
    public function testProvider()
    {
        $container = new Container();
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
    }
}
