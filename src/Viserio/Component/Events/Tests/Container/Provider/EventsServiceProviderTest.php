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

namespace Viserio\Component\Events\Tests\Provider;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Events\Container\Provider\EventsServiceProvider;
use Viserio\Component\Events\EventManager;
use Viserio\Contract\Events\EventManager as EventManagerContract;

/**
 * @internal
 *
 * @small
 */
final class EventsServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(EventManagerContract::class, $this->container->get(EventManagerContract::class));
        self::assertInstanceOf(EventManagerContract::class, $this->container->get(EventManager::class));
        self::assertInstanceOf(EventManagerContract::class, $this->container->get('events'));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new EventsServiceProvider());
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
