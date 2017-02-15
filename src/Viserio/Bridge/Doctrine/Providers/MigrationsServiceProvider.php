<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Providers;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Bridge\Doctrine\Connection;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class MigrationsServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            'migrations.commands' => [self::class, 'createMigrationsCommands'],
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

    public static function createMigrationsCommands(ContainerInterface $container): array
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
     * Resolve component options.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (self::$options === null) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
