<?php
declare(strict_types=1);
namespace Viserio\StaticalProxy\Tests;

use Interop\Container\ContainerInterface;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use StdClass;
use Viserio\StaticalProxy\StaticalProxy;
use Viserio\StaticalProxy\StaticalProxyResolver;
use Viserio\StaticalProxy\Tests\Fixture\FacadeStub;

class StaticalProxyResolverTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        parent::setUp();

        StaticalProxy::clearResolvedInstances();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->andReturn(new StdClass());

        FacadeStub::setContainer($container);
    }

    public function testResolve()
    {
        $resolver = new StaticalProxyResolver();

        self::assertEquals(
            'The registered static proxy [Viserio\staticalproxy\tests\fixture\facadestub] maps to [stdClass]',
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
