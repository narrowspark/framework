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
use Viserio\Component\Container\Pipeline\ReplaceAliasByActualDefinitionPipe;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Exception\LogicException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\ReplaceAliasByActualDefinitionPipe
 *
 * @small
 */
final class ReplaceAliasByActualDefinitionPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->bind('a', FactoryClass::class . '@returnsParameters')
            ->setPublic(true);

        $container->bind(FactoryClass::class);

        $container->setAlias('a', 'a_alias');
        $container->setAlias(FactoryClass::class, 'b_alias');

        $this->process($container);

        self::assertTrue($container->hasDefinition('a'), '->process() does nothing to public definitions.');
        self::assertTrue($container->hasAlias('a_alias'));
        self::assertFalse($container->hasDefinition(FactoryClass::class), '->process() removes non-public definitions.');

        self::assertTrue(
            $container->hasDefinition('b_alias') && ! $container->hasAlias('b_alias'),
            '->process() replaces alias to actual.'
        );
    }

    public function testProcessWithInvalidAlias(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Alias [a] cant be set to a undefined entry [a_alias].');

        $container = new ContainerBuilder();
        $container->setAlias('a_alias', 'a');

        $this->process($container);
    }

    /**
     * @param \Viserio\Contract\Container\ContainerBuilder $container
     */
    private function process(ContainerBuilderContract $container): void
    {
        $pipe = new ReplaceAliasByActualDefinitionPipe();

        $pipe->process($container);
    }
}
