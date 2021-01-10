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

    private function process(ContainerBuilderContract $container): void
    {
        $pipe = new RemovePrivateAliasesPipe();

        $pipe->process($container);
    }
}
