<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests;

use Interop\Container\ContainerInterface;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\StaticalProxy\StaticalProxy;
use Viserio\Component\StaticalProxy\StaticalProxyResolver;
use Viserio\Component\StaticalProxy\Tests\Fixture\FacadeStub;

class StaticalProxyResolverTest extends TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        parent::setUp();

        StaticalProxy::clearResolvedInstances();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->andReturn(new stdClass());

        FacadeStub::setContainer($container);
    }

    public function testResolve()
    {
        $resolver = new StaticalProxyResolver();

        self::assertEquals(
            'The registered static proxy [Viserio\component\staticalproxy\tests\fixture\facadestub] maps to [stdClass]',
            $resolver->resolve(FacadeStub::class)
        );

        self::assertEquals('No static proxy found!', $resolver->resolve(Mock::class));
    }

    public function testGetStaticProxyNameFromInput()
    {
        $resolver = new StaticalProxyResolver();

        self::assertEquals('TEST', $resolver->getStaticProxyNameFromInput('TEST'));
    }

    public function testIsStaticProxy()
    {
        $resolver = new StaticalProxyResolver();

        self::assertTrue($resolver->isStaticProxy(FacadeStub::class));
        self::assertFalse($resolver->isStaticProxy('ThisClassDontExist'));
    }
}
