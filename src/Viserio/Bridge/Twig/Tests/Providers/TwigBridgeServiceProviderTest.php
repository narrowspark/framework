<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Providers;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Lexer;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Engine\TwigEngine;
use Viserio\Bridge\Twig\Loader as TwigLoader;
use Viserio\Bridge\Twig\Providers\TwigBridgeServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Filesystem\Providers\FilesServiceProvider;
use Viserio\Component\View\Providers\ViewServiceProvider;

class TwigBridgeServiceProviderTest extends MockeryTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new TwigBridgeServiceProvider());
        $container->instance(Lexer::class, $this->mock(Lexer::class));

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
                                new ArrayLoader(['test2.html' => 'testsa']),
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(TwigEngine::class, $container->get(TwigEngine::class));
        self::assertInstanceOf(ChainLoader::class, $container->get(TwigLoader::class));
        self::assertInstanceOf(ChainLoader::class, $container->get(LoaderInterface::class));
        self::assertInstanceOf(Environment::class, $container->get(Environment::class));
        self::assertInstanceOf(FactoryContract::class, $container->get(FactoryContract::class));
    }
}
