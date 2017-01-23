<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig;

use Interop\Container\ServiceProvider;
use Viserio\Bridge\Twig\Commands\CleanCommand;
use Viserio\Bridge\Twig\Commands\DebugCommand;
use Viserio\Bridge\Twig\Commands\LintCommand;

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
