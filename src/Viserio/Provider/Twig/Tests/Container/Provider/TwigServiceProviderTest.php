<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Provider\Twig\Tests\Container\Provider;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Twig\Environment;
use Twig\Lexer;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Container\Provider\TwigBridgeServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\Filesystem\Container\Provider\FilesServiceProvider;
use Viserio\Component\OptionsResolver\Container\Provider\OptionsResolverServiceProvider;
use Viserio\Component\View\Container\Provider\ViewServiceProvider;
use Viserio\Contract\View\Factory as FactoryContract;
use Viserio\Provider\Twig\Command\CleanCommand;
use Viserio\Provider\Twig\Command\LintCommand;
use Viserio\Provider\Twig\Container\Provider\TwigServiceProvider;
use Viserio\Provider\Twig\Engine\TwigEngine;
use Viserio\Provider\Twig\Loader as TwigLoader;

/**
 * @internal
 *
 * @small
 */
final class TwigServiceProviderTest extends AbstractContainerTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @runInSeparateProcess
     */
    public function testProvider(): void
    {
        $this->container->set(Lexer::class, Mockery::mock(Lexer::class));

        self::assertInstanceOf(TwigEngine::class, $this->container->get(TwigEngine::class));
        self::assertInstanceOf(TwigLoader::class, $this->container->get(TwigLoader::class));
        self::assertInstanceOf(ChainLoader::class, $this->container->get(ChainLoader::class));
        self::assertInstanceOf(ChainLoader::class, $this->container->get(LoaderInterface::class));
        self::assertInstanceOf(Environment::class, $this->container->get(Environment::class));
        self::assertInstanceOf(FactoryContract::class, $this->container->get(FactoryContract::class));

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(CleanCommand::getDefaultName()));
        self::assertTrue($console->has(LintCommand::getDefaultName()));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new FilesServiceProvider());
        $containerBuilder->register(new ViewServiceProvider());
        $containerBuilder->register(new TwigServiceProvider());
        $containerBuilder->register(new TwigBridgeServiceProvider());
        $containerBuilder->register(new OptionsResolverServiceProvider());
        $containerBuilder->register(new ConsoleServiceProvider());
        $containerBuilder->singleton(Lexer::class)
            ->setSynthetic(true);

        $containerBuilder->bind('config', [
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
                            'templates' => [
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

        $containerBuilder->setParameter('container.dumper.inline_factories', true);
        $containerBuilder->setParameter('container.dumper.inline_class_loader', false);
        $containerBuilder->setParameter('container.dumper.as_files', true);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
