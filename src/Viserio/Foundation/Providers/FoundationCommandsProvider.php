<?php
declare(strict_types=1);
namespace Viserio\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Foundation\Commands\DownCommand;
use Viserio\Foundation\Commands\UpCommand;

class FoundationCommandsProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            'maintenance.commands' => [self::class, 'createMaintenanceCommands'],
        ];
    }

    public static function createMigrationsCommands(): array
    {
        return [
            new DownCommand(),
            new UpCommand(),
        ];
    }
}
