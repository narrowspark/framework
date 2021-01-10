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
use stdClass;
use Viserio\Component\Container\Argument\ConditionArgument;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ConditionDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\CheckDefinitionConditionsPipe;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\CheckDefinitionConditionsPipe
 *
 * @small
 */
final class CheckDefinitionConditionsPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $container->singleton('foo', stdClass::class)
            ->addCondition(new ConditionArgument(['\class_exists(\stdClass::class)'], static function (ConditionDefinition $definition): void {
                $definition->setProperty('bar', 'bar');
            }))
            ->setPublic(true);

        $container->singleton('baz', stdClass::class)
            ->addCondition(new ConditionArgument(['$this->has(\'foo\')'], static function (ConditionDefinition $definition): void {
                $definition->setProperty('foo', new ReferenceDefinition('foo'));
            }))
            ->addCondition(new ConditionArgument(['$this->has(\'bar\')'], static function (ConditionDefinition $definition): void {
                $definition->setProperty('bar', new ReferenceDefinition('bar', ReferenceDefinition::NULL_ON_INVALID_REFERENCE));
                $definition->addMethodCall('foo');
            }))
            ->setPublic(true);

        $container->singleton('complex', stdClass::class)
            ->addCondition(new ConditionArgument(['$this->has(\'foo\') && \class_exists(\stdClass::class) && $instance instanceof \stdClass'], static function (ConditionDefinition $definition): void {
                $definition->setProperty('foo', new ReferenceDefinition('foo'));
            }))
            ->setPublic(true);

        $container->singleton(
            'closure',
            static function () {
                return 'test';
            }
        )
            ->addCondition(new ConditionArgument(['\class_exists(\stdClass::class)'], static function (ConditionDefinition $definition): void {
                $definition->addMethodCall('bar');
            }))
            ->setPublic(true);

        $this->process($container);

        self::assertStringContainsString('Removed condition from [closure]; reason: Definition is missing implementation of [Viserio\Contract\Container\Definition\MethodCallsAwareDefinition] or [Viserio\Contract\Container\Definition\PropertiesAwareDefinition] interface.', $container->getLogs()[0]);
    }

    private function process(ContainerBuilderContract $container): void
    {
        $pipe = new CheckDefinitionConditionsPipe();

        $pipe->process($container);
    }
}
