<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Lexer;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\View\Factory as FactoryContract;
use Viserio\Component\Filesystem\Provider\FilesServiceProvider;
use Viserio\Component\View\Provider\ViewServiceProvider;
use Viserio\Provider\Twig\Engine\TwigEngine;
use Viserio\Provider\Twig\Loader as TwigLoader;
use Viserio\Provider\Twig\Provider\TwigServiceProvider;

/**
 * @internal
 */
final class TwigServiceProviderTest extends MockeryTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new TwigServiceProvider());
        $container->instance(Lexer::class, $this->mock(Lexer::class));

        $container->instance('config', [
            'viserio' => [
                'view' => [
                    'paths' => [
                        \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR,
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

        $this->assertInstanceOf(TwigEngine::class, $container->get(TwigEngine::class));
        $this->assertInstanceOf(ChainLoader::class, $container->get(TwigLoader::class));
        $this->assertInstanceOf(ChainLoader::class, $container->get(LoaderInterface::class));
        $this->assertInstanceOf(Environment::class, $container->get(Environment::class));
        $this->assertInstanceOf(FactoryContract::class, $container->get(FactoryContract::class));
    }
}
