<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Provider;

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Console\Application;

class ConsoleCommandsServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        return [
            Application::class => [self::class, 'extendConsole'],
        ];
    }

    /**
     * Extend viserio console with commands.
     *
     * @param \Psr\Container\ContainerInterface           $container
     * @param null|\Viserio\Component\Console\Application $console
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function extendConsole(
        ContainerInterface $container,
        ?Application $console = null
    ): ?Application {
        if ($console !== null) {
            $manager = $container->get(EntityManagerInterface::class);

            $console->getHelperSet()->set(new ConnectionHelper($manager->getConnection()), 'db');
            $console->getHelperSet()->set(new EntityManagerHelper($manager), 'em');

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
