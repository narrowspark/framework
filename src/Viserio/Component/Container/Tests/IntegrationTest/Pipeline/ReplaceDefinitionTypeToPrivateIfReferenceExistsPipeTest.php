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
use stdClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\ReplaceDefinitionTypeToPrivateIfReferenceExistsPipe;
use Viserio\Contract\Container\Definition\Definition;

/**
 * @internal
 *
 * @small
 */
final class ReplaceDefinitionTypeToPrivateIfReferenceExistsPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->singleton(stdClass::class);
        $container->singleton('foo', stdClass::class)
            ->addMethodCall('baz', [new ReferenceDefinition(stdClass::class)])
            ->setPublic(true);

        $this->process($container);

        self::assertSame(Definition::SINGLETON + Definition::PRIVATE, $container->getDefinition(stdClass::class)->getType());
    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipe = new ReplaceDefinitionTypeToPrivateIfReferenceExistsPipe();

        $pipe->process($container);
    }
}
