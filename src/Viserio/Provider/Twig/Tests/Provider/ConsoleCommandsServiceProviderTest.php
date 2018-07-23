<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Filesystem\Provider\FilesServiceProvider;
use Viserio\Component\View\Provider\ViewServiceProvider;
use Viserio\Provider\Twig\Command\CleanCommand;
use Viserio\Provider\Twig\Command\LintCommand;
use Viserio\Provider\Twig\Provider\ConsoleCommandsServiceProvider;
use Viserio\Provider\Twig\Provider\TwigServiceProvider;

/**
 * @internal
 */
final class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices(): void
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new TwigServiceProvider());
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());
        $container->instance(
            'config',
            [
                'viserio' => [
                    'view' => [
                        'paths' => [
                            __DIR__,
                        ],
                        'engines' => [
                            'twig' => [
                                'options' => [
                                    'cache' => __DIR__,
                                    'debug' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $console  = $container->get(Application::class);
        $commands = $console->all();

        static::assertInstanceOf(CleanCommand::class, $commands['twig:clear']);
        static::assertInstanceOf(LintCommand::class, $commands['lint:twig']);
    }

    public function testGetDimensions(): void
    {
        static::assertSame(['viserio', 'console'], ConsoleCommandsServiceProvider::getDimensions());
    }

    public function testGetDefaultOptions(): void
    {
        static::assertSame(
            [
                'lazily_commands' => [
                    'lint:twig'  => LintCommand::class,
                    'twig:clear' => CleanCommand::class,
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
