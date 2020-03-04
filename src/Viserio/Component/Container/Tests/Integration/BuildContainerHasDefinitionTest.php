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

namespace Viserio\Component\Container\Tests\Integration;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\ContainerBuilder
 *
 * @small
 */
final class BuildContainerHasDefinitionTest extends BaseContainerTest
{
    public function testHasDefinitionWhenSetDirectly(): void
    {
        $container = $this->containerBuilder;
        $container->bind('foo', 'bar');

        self::assertTrue($container->hasDefinition('foo'));
    }

    public function testHasDefinitionNot(): void
    {
        self::assertFalse($this->containerBuilder->hasDefinition('wow'));
    }

    public function testHasDefinition(): void
    {
        $this->containerBuilder->bind('foo', 'bar');

        self::assertTrue($this->containerBuilder->hasDefinition('foo'));
    }
}
