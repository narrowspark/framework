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

namespace Viserio\Component\Validation\Tests\Container\Provider;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Validation\Container\Provider\SanitizerServiceProvider;
use Viserio\Component\Validation\Sanitizer;

/**
 * @internal
 *
 * @small
 */
final class SanitizerServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(Sanitizer::class, $this->container->get(Sanitizer::class));
        self::assertInstanceOf(Sanitizer::class, $this->container->get('sanitizer'));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new SanitizerServiceProvider());
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
