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

namespace Viserio\Component\Filesystem\Tests\Container\Provider;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Filesystem\Container\Provider\FilesystemServiceProvider;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Contract\Filesystem\Filesystem as FilesystemContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class FilesystemServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(Filesystem::class, $this->container->get(Filesystem::class));
        self::assertInstanceOf(Filesystem::class, $this->container->get(FilesystemContract::class));
        self::assertInstanceOf(Filesystem::class, $this->container->get('files'));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new FilesystemServiceProvider());
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
