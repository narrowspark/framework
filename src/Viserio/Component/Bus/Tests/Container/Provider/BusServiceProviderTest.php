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

namespace Viserio\Component\Bus\Tests\Container\Provider;

use Viserio\Component\Bus\Container\Provider\BusServiceProvider;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Contract\Bus\Dispatcher as DispatcherContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class BusServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(Dispatcher::class, $this->container->get(Dispatcher::class));
        self::assertInstanceOf(DispatcherContract::class, $this->container->get(DispatcherContract::class));
        self::assertInstanceOf(DispatcherContract::class, $this->container->get('bus'));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new BusServiceProvider());
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
