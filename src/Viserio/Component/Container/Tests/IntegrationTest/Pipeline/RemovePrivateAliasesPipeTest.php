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

namespace Viserio\Component\Container\Tests\IntegrationTest\Pipeline;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Pipeline\RemovePrivateAliasesPipe;
use Viserio\Component\Container\Tests\Fixture\Method\ClassWithMethods;

/**
 * @internal
 *
 * @small
 */
final class RemovePrivateAliasesPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->singleton(ClassWithMethods::class);
        $container->setAlias(ClassWithMethods::class, 'foo')
            ->setPublic(true);
        $container->setAlias(ClassWithMethods::class, 'bar')
            ->setPublic(false);

        $this->process($container);

        self::assertCount(1, $container->getAliases());
    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipe = new RemovePrivateAliasesPipe();

        $pipe->process($container);
    }
}
