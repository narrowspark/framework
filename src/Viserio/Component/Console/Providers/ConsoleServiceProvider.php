<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\Application as SymfonyConsole;
use Viserio\Component\Console\Application;
use Viserio\Component\Contracts\Console\Application as ApplicationContract;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;

class ConsoleServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.console';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ApplicationContract::class => [self::class, 'createCerebro'],
            Application::class         => function (ContainerInterface $container) {
                return $container->get(ApplicationContract::class);
            },
            SymfonyConsole::class      => function (ContainerInterface $container) {
                return $container->get(ApplicationContract::class);
            },
            'console' => function (ContainerInterface $container) {
                return $container->get(ApplicationContract::class);
            },
            'cerebro' => function (ContainerInterface $container) {
                return $container->get(ApplicationContract::class);
            },
        ];
    }

    public static function createCerebro(ContainerInterface $container): Application
    {
        $console = new Application(
            $container,
            self::getConfig($container, 'version'),
            self::getConfig($container, 'name', 'Cerebro')
        );

        // Add auto-complete for Symfony Console application
        $console->add(new CompletionCommand());

        return $console;
    }
}
