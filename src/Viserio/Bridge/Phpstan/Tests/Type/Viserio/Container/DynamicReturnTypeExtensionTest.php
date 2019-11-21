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

namespace Viserio\Bridge\Phpstan\Tests\Type\Viserio\Container;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPStan\Reflection\MethodReflection;
use Viserio\Bridge\Phpstan\Tests\Type\AbstractExtensionTestCase;
use Viserio\Bridge\Phpstan\Type\Viserio\Container\DynamicReturnTypeExtension;
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 *
 * @small
 */
final class DynamicReturnTypeExtensionTest extends AbstractExtensionTestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Viserio\Bridge\Phpstan\Type\Viserio\Container\DynamicReturnTypeExtension */
    private $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new DynamicReturnTypeExtension();
    }

    public function testGetClass(): void
    {
        self::assertSame(ContainerBuilderContract::class, $this->extension->getClass());
    }

    public function testIsMethodSupported(): void
    {
        $methodReflectionMock = Mockery::mock(MethodReflection::class);

        $methodReflectionMock->shouldReceive('getName')
            ->once()
            ->andReturn('bind');

        self::assertTrue($this->extension->isMethodSupported($methodReflectionMock));

        $methodReflectionMock->shouldReceive('getName')
            ->once()
            ->andReturn('singleton');

        self::assertTrue($this->extension->isMethodSupported($methodReflectionMock));

        $methodReflectionMock->shouldReceive('getName')
            ->once()
            ->andReturn('foo');

        self::assertFalse($this->extension->isMethodSupported($methodReflectionMock));
    }

    /**
     * @dataProvider provideGetTypeFromMethodCallCases
     *
     * @param string $expression
     * @param string $type
     */
    public function testGetTypeFromMethodCall(string $expression, string $type): void
    {
        $this->processFile(
            dirname(__DIR__, 3) . '/Fixture/ServiceProvider.php',
            $expression,
            $type,
            new DynamicReturnTypeExtension()
        );
    }

    public function provideGetTypeFromMethodCallCases(): iterable
    {
        return [
            ['$service1', ObjectDefinitionContract::class],
            ['$service2', ClosureDefinitionContract::class],
            ['$service3', FactoryDefinitionContract::class],
            ['$service4', FactoryDefinitionContract::class],
            ['$service5', ObjectDefinitionContract::class],
            ['$service6', DefinitionContract::class],
            ['$service7', FactoryDefinitionContract::class],
            ['$service8', FactoryDefinitionContract::class],
            ['$service9', FactoryDefinitionContract::class],
            ['$service10', DefinitionContract::class],
            ['$service11', DefinitionContract::class],
            ['$service12', FactoryDefinitionContract::class],
            ['$service13', DefinitionContract::class],
            ['$service14', UndefinedDefinitionContract::class],
        ];
    }
}
