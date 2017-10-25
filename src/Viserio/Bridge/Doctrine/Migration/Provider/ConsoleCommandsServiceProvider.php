<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Provider;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ConsoleCommandsServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            Application::class => [self::class, 'extendConsole'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'doctrine'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return ['migrations'];
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
            $console->addCommands(self::createMigrationsCommands($container));
        }

        return $console;
    }

    /**
     * Create and configure migrations commands.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return array
     */
    private static function createMigrationsCommands(ContainerInterface $container): array
    {
        $options = self::resolveOptions($container);

        $config = $options['migrations'];

        $doctrineConfig = new Configuration($container->get(Connection::class));

        $doctrineConfig->setMigrationsNamespace($config['namespace']);

        if (isset($config['path'])) {
            $doctrineConfig->setMigrationsDirectory($config['path']);
            $doctrineConfig->registerMigrationsFromDirectory($config['path']);
        }

        if (isset($config['name'])) {
            $doctrineConfig->setName($config['name']);
        }

        if (isset($config['table_name'])) {
            $doctrineConfig->setMigrationsTableName($config['table_name']);
        }

        $commands = [
            new DiffCommand(),
            new ExecuteCommand(),
            new GenerateCommand(),
            new MigrateCommand(),
            new StatusCommand(),
            new VersionCommand(),
        ];

        foreach ($commands as $key => $command) {
            $command->setMigrationConfiguration($doctrineConfig);

            $commands[$key] = $command;
        }

        return $commands;
    }
}
