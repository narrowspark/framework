<?php
declare(strict_types=1);
namespace Viserio\View\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\View\Engines\EngineResolver;
use Viserio\Contracts\View\Engine as EngineContract;

class ViewEngineResolverTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testResolversMayBeResolved()
    {
        $resolver = new EngineResolver();
        $resolver->register(
            'foo',
            function () {
                return $this->mock(EngineContract::class);
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
        $resolver = new EngineResolver();
        $resolver->resolve('foo');
    }
}
