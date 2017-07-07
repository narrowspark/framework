<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Providers;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Component\Console\Application;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class ConsoleCommandsServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use StaticOptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Application::class => [self::class, 'createConsoleCommands'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'doctrine'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return ['migrations'];
    }

    /**
     * Extend viserio console with new commands.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function createConsoleCommands(ContainerInterface $container, ?callable $getPrevious = null): ?Application
    {
        $console = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($console !== null) {
            $console->addCommands(self::createMigrationsCommands($container));

            return $console;
        }

        return null;
    }

    /**
     * Create and configure migrations commands.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return array
     */
    private static function createMigrationsCommands(ContainerInterface $container): array
    {
        self::resolveOptions($container);

        $config = self::$options['migrations'];

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

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}
