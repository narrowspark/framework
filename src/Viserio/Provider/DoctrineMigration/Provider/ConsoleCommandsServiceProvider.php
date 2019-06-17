<?php
declare(strict_types=1);
namespace Viserio\Provider\Doctrine\Migration\Provider;

use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Provider\Doctrine\Migration\Commands\Helper\ConfigurationHelper;

class ConsoleCommandsServiceProvider implements ServiceProviderInterface
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
     * Extend viserio console with commands.
     *
     * @param \Psr\Container\ContainerInterface           $container
     * @param null|\Viserio\Component\Console\Application $console
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function extendConsole(ContainerInterface $container, ?Application $console = null): ?Application
    {
        if ($console !== null) {
            $console->getHelperSet()
                ->set(new ConfigurationHelper($container), 'connection');

            $console->addCommands([
                new DiffCommand(),
                new ExecuteCommand(),
                new GenerateCommand(),
                new MigrateCommand(),
                new StatusCommand(),
                new VersionCommand(),
            ]);
        }

        return $console;
    }
}
