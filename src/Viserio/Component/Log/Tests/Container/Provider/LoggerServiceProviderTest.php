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

namespace Viserio\Component\Log\Tests\Provider;

use Psr\Log\LoggerInterface;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Events\Container\Provider\EventsServiceProvider;
use Viserio\Component\Log\Container\Provider\LoggerServiceProvider;
use Viserio\Component\Log\Logger;
use Viserio\Component\Log\LogManager;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class LoggerServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(LogManager::class, $this->container->get(LogManager::class));
        self::assertInstanceOf(LogManager::class, $this->container->get('log'));
        self::assertInstanceOf(Logger::class, $this->container->get(LoggerInterface::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->bind('config', [
            'viserio' => [
                'logging' => [
                    'path' => '',
                    'env' => 'local',
                    'name' => '',
                ],
            ],
        ]);
        $containerBuilder->register(new EventsServiceProvider());
        $containerBuilder->register(new LoggerServiceProvider());
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
