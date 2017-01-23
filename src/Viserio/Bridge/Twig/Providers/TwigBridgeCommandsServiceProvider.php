<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig;

use Viserio\Bridge\Twig\Commands\CleanCommand;
use Viserio\Bridge\Twig\Commands\DebugCommand;
use Viserio\Bridge\Twig\Commands\LintCommand;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class TwigBridgeCommandsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            'twig.commands' => [self::class, 'createTwigCommands'],
        ];
    }

    public static function createTwigCommands(): array
    {
        return [
            new CleanCommand(),
            new DebugCommand(),
            new LintCommand(),
        ];
    }
}
