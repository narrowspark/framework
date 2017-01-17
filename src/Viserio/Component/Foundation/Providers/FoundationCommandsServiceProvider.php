<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Providers;

use Interop\Container\ServiceProvider;
use Viserio\Component\Foundation\Commands\DownCommand;
use Viserio\Component\Foundation\Commands\KeyGenerateCommand;
use Viserio\Component\Foundation\Commands\UpCommand;

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
