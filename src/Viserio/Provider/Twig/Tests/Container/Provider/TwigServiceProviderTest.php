<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Provider\Twig\Tests\Container\Provider;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Twig\Environment;
use Twig\Lexer;
use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Container\Provider\TwigBridgeServiceProvider;
use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Filesystem\Container\Provider\FilesystemServiceProvider;
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
 * @coversNothing
 */
final class TwigServiceProviderTest extends AbstractContainerTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
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
        $containerBuilder->register(new FilesystemServiceProvider());
        $containerBuilder->register(new ViewServiceProvider());
        $containerBuilder->register(new TwigServiceProvider());
        $containerBuilder->register(new TwigBridgeServiceProvider());
        $containerBuilder->register(new ConfigServiceProvider());
        $containerBuilder->register(new ConsoleServiceProvider());
        $containerBuilder->singleton(Lexer::class)
            ->setSynthetic(true);

        $containerBuilder->setParameter('viserio', [
            'console' => [
                'name' => 'test',
                'version' => '1',
            ],
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
                    ],
                ],
            ],
        ]);
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
