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

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Exception\Console\Handler;
use Viserio\Component\Exception\Container\Provider\ConsoleExceptionServiceProvider;
use Viserio\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;

/**
 * @internal
 *
 * @small
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
