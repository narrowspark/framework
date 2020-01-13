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

namespace Viserio\Component\Config\Tests\Container\Provider;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Provider\Debug\Container\Provider\FrameworkServiceProvider;

/**
 * @internal
 *
 * @small
 */
final class FrameworkServiceProviderTest extends AbstractContainerTestCase
{
    public function testBuild(): void
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new FrameworkServiceProvider());
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
