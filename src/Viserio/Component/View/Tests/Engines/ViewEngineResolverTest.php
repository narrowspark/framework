<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Engines;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\View\Engine as EngineContract;
use Viserio\Component\View\Engines\EngineResolver;

class ViewEngineResolverTest extends MockeryTestCase
{
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
        self::assertEquals(spl_object_hash($result), spl_object_hash($resolver->resolve('foo')));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Engine [foo] not found.
     */
    public function testResolverThrowsExceptionOnUnknownEngine()
    {
        $resolver = new EngineResolver();
        $resolver->resolve('foo');
    }
}
