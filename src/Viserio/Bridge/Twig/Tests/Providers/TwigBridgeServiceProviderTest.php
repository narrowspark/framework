<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Twig_LoaderInterface;
use Viserio\Bridge\Twig\Loader as TwigLoader;
use Viserio\Bridge\Twig\Providers\TwigBridgeServiceProvider;
use Viserio\Bridge\Twig\TwigEnvironment;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Filesystem\Providers\FilesServiceProvider;
use Viserio\Component\View\Providers\ViewServiceProvider;

class TwigBridgeServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new TwigBridgeServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'view' => [
                    'paths'      => [
                        __DIR__ . '/../Fixture/',
                        __DIR__,
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(TwigLoader::class, $container->get(TwigLoader::class));
        self::assertInstanceOf(TwigLoader::class, $container->get(Twig_LoaderInterface::class));
        self::assertInstanceOf(TwigEnvironment::class, $container->get(TwigEnvironment::class));
        self::assertInstanceOf(FactoryContract::class, $container->get(FactoryContract::class));
    }
}
