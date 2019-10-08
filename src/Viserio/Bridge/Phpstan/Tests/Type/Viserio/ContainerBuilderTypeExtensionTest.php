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

namespace Viserio\Bridge\Phpstan\Tests\Type\Viserio;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPStan\Reflection\MethodReflection;
use Viserio\Bridge\Phpstan\Tests\Type\AbstractExtensionTestCase;
use Viserio\Bridge\Phpstan\Type\Viserio\ContainerBuilderTypeExtension;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 *
 * @small
 */
final class ContainerBuilderTypeExtensionTest extends AbstractExtensionTestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Viserio\Bridge\Phpstan\Type\Viserio\ContainerBuilderTypeExtension */
    private $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new ContainerBuilderTypeExtension();
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
}
