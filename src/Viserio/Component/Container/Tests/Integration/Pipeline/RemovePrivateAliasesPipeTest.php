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

namespace Viserio\Component\Container\Tests\Integration\Pipeline;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Pipeline\RemovePrivateAliasesPipe;
use Viserio\Component\Container\Tests\Fixture\Method\ClassWithMethods;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\RemovePrivateAliasesPipe
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
     * @param \Viserio\Contract\Container\ContainerBuilder $container
     */
    private function process(ContainerBuilderContract $container): void
    {
        $pipe = new RemovePrivateAliasesPipe();

        $pipe->process($container);
    }
}
