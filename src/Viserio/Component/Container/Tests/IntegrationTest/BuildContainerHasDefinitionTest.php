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

namespace Viserio\Component\Container\Tests\IntegrationTest;

/**
 * @internal
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
