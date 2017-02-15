<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Twig_Environment;
use Twig_Loader_Array;
use Twig_Loader_Chain;
use Twig_LoaderInterface;
use Viserio\Bridge\Twig\Loader as TwigLoader;
use Viserio\Bridge\Twig\Providers\TwigBridgeServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Filesystem\Providers\FilesServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\View\Providers\ViewServiceProvider;
use Viserio\Bridge\Twig\Engine\TwigEngine;

class TwigBridgeServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new TwigBridgeServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'view' => [
                    'paths'      => [
                        __DIR__ . '/../Fixture/',
                        __DIR__,
                    ],
                    'engines' => [
                        'twig' => [
                            'options' => [
                                'debug' => true,
                                'cache' => '',
                            ],
                            'file_extension' => 'html',
                            'templates'      => [
                                'test.html' => 'tests',
                            ],
                            'loaders' => [
                                new Twig_Loader_Array(['test2.html' => 'testsa']),
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(TwigEngine::class, $container->get(TwigEngine::class));
        self::assertInstanceOf(Twig_Loader_Chain::class, $container->get(TwigLoader::class));
        self::assertInstanceOf(Twig_Loader_Chain::class, $container->get(Twig_LoaderInterface::class));
        self::assertInstanceOf(Twig_Environment::class, $container->get(Twig_Environment::class));
        self::assertInstanceOf(FactoryContract::class, $container->get(FactoryContract::class));
    }
}
