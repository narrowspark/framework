<?php
namespace Viserio\View\Tests;

use StdClass;
use Viserio\View\Engines\EngineResolver;

class ViewEngineResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolversMayBeResolved()
    {
        $resolver = new EngineResolver();
        $resolver->register(
            'foo',
            function () {
                return new StdClass();
            }
        );
        $result = $resolver->resolve('foo');
        $this->assertEquals(spl_object_hash($result), spl_object_hash($resolver->resolve('foo')));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testResolverThrowsExceptionOnUnknownEngine()
    {
        $this->setExpectedException('InvalidArgumentException');
        $resolver = new EngineResolver();
        $resolver->resolve('foo');
    }
}
