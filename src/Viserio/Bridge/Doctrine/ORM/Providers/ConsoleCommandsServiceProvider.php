<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Providers;

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Symfony\Component\Console\Helper\HelperSet;
use Viserio\Component\Contracts\Console\Application as ApplicationContract;

class ConsoleCommandsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ApplicationContract::class => [self::class, 'createConsoleCommands'],
        ];
    }

    public static function createConsoleCommands(ContainerInterface $container, ?callable $getPrevious = null): ?ApplicationContract
    {
        if ($getPrevious !== null) {
            $console = $getPrevious();
            $manager = $container->get(EntityManagerInterface::class);

            $console->setHelperSet(new HelperSet([
                'db' => new ConnectionHelper($manager->getConnection()),
                'em' => new EntityManagerHelper($manager),
            ]));

            $console->addCommands([
                new MetadataCommand(),
                new ResultCommand(),
                new QueryCommand(),
                new CreateCommand(),
                new UpdateCommand(),
                new DropCommand(),
                new EnsureProductionSettingsCommand(),
                new ConvertDoctrine1SchemaCommand(),
                new GenerateRepositoriesCommand(),
                new GenerateEntitiesCommand(),
                new GenerateProxiesCommand(),
                new ConvertMappingCommand(),
                new RunDqlCommand(),
                new ValidateSchemaCommand(),
                new InfoCommand(),
                new MappingDescribeCommand(),
            ]);

            return $console;
        }

        return null;
    }
}
