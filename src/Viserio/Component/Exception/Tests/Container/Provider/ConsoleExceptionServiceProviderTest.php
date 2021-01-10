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

namespace Viserio\Component\Exception\Tests\Provider;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Exception\Console\Handler;
use Viserio\Component\Exception\Container\Provider\ConsoleExceptionServiceProvider;
use Viserio\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ConsoleExceptionServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(ConsoleHandlerContract::class, $this->container->get(ConsoleHandlerContract::class));
        self::assertInstanceOf(ConsoleHandlerContract::class, $this->container->get(Handler::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->bind('config', [
            'viserio' => [
                'exception' => [
                ],
            ],
        ]);
        $containerBuilder->register(new ConsoleExceptionServiceProvider());
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
