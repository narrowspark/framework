<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Process\Process;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Console\Command\DownCommand;
use Viserio\Component\Foundation\Console\Command\KeyGenerateCommand;
use Viserio\Component\Foundation\Console\Command\ServeCommand;
use Viserio\Component\Foundation\Console\Command\UpCommand;

class ConsoleCommandsServiceProvider implements ServiceProviderInterface
{
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
    public static function extendConsole(
    	ContainerInterface $container,
    	?Application $console = null
    ): ?Application {
        if ($console !== null) {
            $console->addCommands([
                new DownCommand(),
                new UpCommand(),
            ]);

            if ($container->has(KernelContract::class) && $container->get(KernelContract::class)->isLocal()) {
                $console->add(new KeyGenerateCommand());

                if (\class_exists(Process::class)) {
                    $console->add(new ServeCommand());
                }
            }
        }

        return $console;
    }
}
