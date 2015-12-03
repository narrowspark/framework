<?php
namespace Viserio\View\Test;

use Viserio\View\Engines\EngineResolver;

/**
 * ViewEngineResolverTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class ViewEngineResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolversMayBeResolved()
    {
        $resolver = new EngineResolver();
        $resolver->register('foo', function () { return new \StdClass(); });
        $result = $resolver->resolve('foo');
        $this->assertEquals(spl_object_hash($result), spl_object_hash($resolver->resolve('foo')));
    }

    public function testResolverThrowsExceptionOnUnknownEngine()
    {
        $this->setExpectedException('InvalidArgumentException');
        $resolver = new EngineResolver();
        $resolver->resolve('foo');
    }
}
