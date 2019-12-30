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

namespace Viserio\Component\Exception\Tests\Provider;

use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Exception\Container\Provider\HttpExceptionServiceProvider;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonApiDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\SymfonyDisplayer;
use Viserio\Component\Exception\Displayer\ViewDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer;
use Viserio\Component\Exception\Filter\CanDisplayFilter;
use Viserio\Component\Exception\Filter\ContentTypeFilter;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Http\Handler;
use Viserio\Component\Filesystem\Container\Provider\FilesystemServiceProvider;
use Viserio\Component\HttpFactory\Container\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Log\Container\Provider\LoggerServiceProvider;
use Viserio\Component\View\Container\Provider\ViewServiceProvider;

/**
 * @internal
 *
 * @small
 */
final class HttpExceptionServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(HtmlDisplayer::class, $this->container->get(HtmlDisplayer::class));
        self::assertInstanceOf(JsonDisplayer::class, $this->container->get(JsonDisplayer::class));
        self::assertInstanceOf(JsonApiDisplayer::class, $this->container->get(JsonApiDisplayer::class));
        self::assertInstanceOf(SymfonyDisplayer::class, $this->container->get(SymfonyDisplayer::class));
        self::assertInstanceOf(ViewDisplayer::class, $this->container->get(ViewDisplayer::class));
        self::assertInstanceOf(WhoopsPrettyDisplayer::class, $this->container->get(WhoopsPrettyDisplayer::class));

        self::assertInstanceOf(VerboseFilter::class, $this->container->get(VerboseFilter::class));
        self::assertInstanceOf(CanDisplayFilter::class, $this->container->get(CanDisplayFilter::class));
        self::assertInstanceOf(ContentTypeFilter::class, $this->container->get(ContentTypeFilter::class));

        self::assertInstanceOf(Handler::class, $this->container->get(Handler::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new ConfigServiceProvider());

        $containerBuilder->findDefinition('config')
            ->addMethodCall('setArray', [
                [
                    'viserio' => [
                        'exception' => [
                            'env' => 'dev',
                            'debug' => false,
                            'http' => [
                                'default_displayer' => '',
                            ],
                        ],
                        'view' => [
                            'paths' => [],
                        ],
                        'logging' => [
                            'env' => 'dev',
                            'path' => __DIR__,
                        ],
                    ],
                ],
            ]);

        $containerBuilder->register(new ViewServiceProvider());
        $containerBuilder->register(new FilesystemServiceProvider());
        $containerBuilder->register(new LoggerServiceProvider());
        $containerBuilder->register(new HttpFactoryServiceProvider());
        $containerBuilder->register(new HttpExceptionServiceProvider());

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
