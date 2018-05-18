<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\ContainerResolver;
use Viserio\Component\Container\Tests\Fixture\ContainerResolverResolveMethodClass;

class ContainerResolverTest extends TestCase
{
    /**
     * @var \Viserio\Component\Container\ContainerResolver
     */
    private $containerResolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->containerResolver = new ContainerResolver();
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @expectedExceptionMessage Class [Viserio\Component\Container\Tests\stdClass] needed by [Parameter #0 [ <required> Viserio\Component\Container\Tests\stdClass $x ]] not found. Check type hint and 'use' statements.
     */
    public function testResolveWithNotFoundClassType(): void
    {
        $this->containerResolver->resolve(function (stdClass $x): void {
        });
    }

    public function testResolveWithCallbackAllowsNull(): void
    {
        $this->containerResolver->resolve(function (?sting $x): void {
            TestCase::assertNull($x);
        });
    }

    public function testResolveCanResolveClassSelfInMethod(): void
    {
        $array = $this->containerResolver->resolve(ContainerResolverResolveMethodClass::class . '::selfMethod');

        self::assertInstanceOf(ContainerResolverResolveMethodClass::class, $array[0]);
        self::assertInstanceOf(ContainerResolverResolveMethodClass::class, $array[1]);
        self::assertNull($array[2]);
        self::assertNull($array[3]);
    }

    public function testResolveCanResolveClassSelfInNullableMethod(): void
    {
        $array = $this->containerResolver->resolve(ContainerResolverResolveMethodClass::class . '::methodNullable');

        self::assertInstanceOf(ContainerResolverResolveMethodClass::class, $array[0]);
        self::assertInstanceOf(ContainerResolverResolveMethodClass::class, $array[1]);
        self::assertNull($array[2]);
        self::assertNull($array[3]);
    }
}
