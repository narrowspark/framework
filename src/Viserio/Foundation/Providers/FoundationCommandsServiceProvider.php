<?php
declare(strict_types=1);
namespace Viserio\Foundation\Providers;

use Interop\Container\ServiceProvider;
use Viserio\Foundation\Commands\DownCommand;
use Viserio\Foundation\Commands\UpCommand;
use Viserio\Foundation\Commands\KeyGenerateCommand;

class FoundationCommandsServiceProvider implements ServiceProvider
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

    public static function createMaintenanceCommands(): array
    {
        return [
            // new DownCommand(),
            new UpCommand(),
            // new KeyGenerateCommand(),
        ];
    }
}
