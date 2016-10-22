<?php
declare(strict_types=1);
namespace Viserio\Console\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Viserio\Console\Application;
use Viserio\Contracts\Console\Application as ApplicationContract;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;

class ConsoleServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.console';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ApplicationContract::class => [self::class, 'createCerebro'],
            Application::class => function (ContainerInterface $container) {
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
            $container->get(DispatcherContract::class),
            self::getConfig($container, 'version'),
            self::getConfig($container, 'name', 'Cerebro')
        );

        // Add auto-complete for Symfony Console application
        $console->add(new CompletionCommand());

        return $console;
    }
}
