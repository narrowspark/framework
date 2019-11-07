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

namespace Viserio\Provider\Debug\Tests\Container\Provider;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\VarDumper;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\ArrayLoader;
use Viserio\Bridge\Twig\Container\Provider\TwigBridgeServiceProvider;
use Viserio\Bridge\Twig\Extension\DumpExtension;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\Filesystem\Container\Provider\FilesServiceProvider;
use Viserio\Component\OptionsResolver\Container\Provider\OptionsResolverServiceProvider;
use Viserio\Component\View\Container\Provider\ViewServiceProvider;
use Viserio\Provider\Debug\Container\Provider\DebugServiceProvider;
use Viserio\Provider\Debug\HtmlDumper;
use Viserio\Provider\Twig\Container\Provider\TwigServiceProvider;

/**
 * @internal
 *
 * @small
 */
final class DebugServiceProviderTest extends AbstractContainerTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testProvider(): void
    {
        self::assertInstanceOf(VarDumper::class, $this->container->get(VarDumper::class));
        self::assertInstanceOf(DataDumperInterface::class, $this->container->get(DataDumperInterface::class));
        self::assertInstanceOf(DataDumperInterface::class, $this->container->get(HtmlDumper::class));
        self::assertInstanceOf(ClonerInterface::class, $this->container->get(ClonerInterface::class));
        self::assertInstanceOf(ClonerInterface::class, $this->container->get(VarCloner::class));
        self::assertInstanceOf(DumpExtension::class, $this->container->get(DumpExtension::class));

        $twig = $this->container->get(TwigEnvironment::class);

        self::assertInstanceOf(DumpExtension::class, $twig->getExtension(DumpExtension::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new TwigBridgeServiceProvider());
        $containerBuilder->register(new FilesServiceProvider());
        $containerBuilder->register(new ViewServiceProvider());
        $containerBuilder->register(new TwigServiceProvider());
        $containerBuilder->register(new DebugServiceProvider());
        $containerBuilder->register(new OptionsResolverServiceProvider());

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
