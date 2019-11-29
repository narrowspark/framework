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
use Viserio\Component\Validation\Container\Provider\ValidationServiceProvider;
use Viserio\Component\Validation\Validator;
use Viserio\Contract\Validation\Validator as ValidatorContract;

/**
 * @internal
 *
 * @small
 */
final class ValidationServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(Validator::class, $this->container->get(Validator::class));
        self::assertInstanceOf(Validator::class, $this->container->get(ValidatorContract::class));
        self::assertInstanceOf(Validator::class, $this->container->get('validator'));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new ValidationServiceProvider());
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
