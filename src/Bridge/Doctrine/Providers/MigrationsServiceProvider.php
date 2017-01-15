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
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Bridge\Doctrine\Connection;

class MigrationsServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.database';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            'migrations.commands' => [self::class, 'createMigrationsCommands'],
        ];
    }

    public static function createMigrationsCommands(ContainerInterface $container): array
    {
        $config = self::getConfig($container, 'migrations', []);

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
