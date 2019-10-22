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

namespace Viserio\Component\Bus\Tests\Container\Provider;

use Viserio\Component\Bus\Container\Provider\BusServiceProvider;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Contract\Bus\Dispatcher as DispatcherContract;

/**
 * @internal
 *
 * @small
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
