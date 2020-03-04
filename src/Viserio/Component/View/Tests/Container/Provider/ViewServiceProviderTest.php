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

namespace Viserio\Component\View\Tests\Provider;

use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
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
 * @coversNothing
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
        $containerBuilder->setParameter('viserio', [
            'view' => [
                'paths' => [
                    \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR,
                    __DIR__,
                ],
                'extensions' => ['phtml', 'php'],
            ],
        ]);
        $containerBuilder->register(new ConfigServiceProvider());
        $containerBuilder->register(new ViewServiceProvider());
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
