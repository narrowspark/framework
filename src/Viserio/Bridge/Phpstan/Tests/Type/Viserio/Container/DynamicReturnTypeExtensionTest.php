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
 * @coversNothing
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
        // fix global change of autoloader
        $this->setBeStrictAboutChangesToGlobalState(false);

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

    public static function provideGetTypeFromMethodCallCases(): iterable
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
            ['$service15', ObjectDefinitionContract::class],
        ];
    }
}
