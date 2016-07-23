<?php

declare(strict_types=1);
namespace Viserio\StaticalProxy\Tests;

use Mockery as Mock;
use StdClass;
use Viserio\StaticalProxy\StaticalProxy;
use Viserio\StaticalProxy\StaticalProxyResolver;
use Viserio\StaticalProxy\Tests\Fixture\FacadeStub;

class StaticalProxyResolverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        StaticalProxy::clearResolvedInstances();

        $container = Mock::mock('Interop\Container\ContainerInterface');
        $container->shouldReceive('get')->andReturn(new StdClass());
        FacadeStub::setContainer($container);
    }

    public function tearDown()
    {
        Mock::close();
    }

    public function testResolve()
    {
        $resolver = new StaticalProxyResolver();

        $this->assertEquals('The registered static proxy [Viserio\staticalproxy\tests\fixture\facadestub] maps to [stdClass]', $resolver->resolve(FacadeStub::class));

        $this->assertEquals('No static proxy found!', $resolver->resolve(Mock::class));
    }

    public function testGetStaticProxyNameFromInput()
    {
        $resolver = new StaticalProxyResolver();

        $this->assertEquals('TEST', $resolver->getStaticProxyNameFromInput('TEST'));
    }

    public function testIsStaticProxy()
    {
        $resolver = new StaticalProxyResolver();

        $this->assertTrue($resolver->isStaticProxy(FacadeStub::class));
        $this->assertFalse($resolver->isStaticProxy('ThisClassDontExist'));
    }
}
