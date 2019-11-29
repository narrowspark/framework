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

namespace Viserio\Component\View\Tests\Provider;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\View\Container\Provider\ViewServiceProvider;
use Viserio\Component\View\ViewFactory;
use Viserio\Component\View\ViewFinder;
use Viserio\Contract\View\Factory as FactoryContract;

/**
 * @internal
 *
 * @small
 */
final class ViewServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(FactoryContract::class, $this->container->get(FactoryContract::class));
        self::assertInstanceOf(FactoryContract::class, $this->container->get(ViewFactory::class));
        self::assertInstanceOf(FactoryContract::class, $this->container->get('view'));
        self::assertInstanceOf(ViewFinder::class, $this->container->get('view.finder'));
        self::assertInstanceOf(ViewFinder::class, $this->container->get(ViewFinder::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new ViewServiceProvider());
        $containerBuilder->bind('config', [
            'viserio' => [
                'view' => [
                    'paths' => [
                        \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR,
                        __DIR__,
                    ],
                    'extensions' => ['phtml', 'php'],
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
