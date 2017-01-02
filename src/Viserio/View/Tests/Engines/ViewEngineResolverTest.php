<?php
declare(strict_types=1);
namespace Viserio\View\Tests\Engines;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Contracts\View\Engine as EngineContract;
use Viserio\View\Engines\EngineResolver;

class ViewEngineResolverTest extends TestCase
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
        self::assertEquals(spl_object_hash($result), spl_object_hash($resolver->resolve('foo')));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResolverThrowsExceptionOnUnknownEngine()
    {
        $resolver = new EngineResolver();
        $resolver->resolve('foo');
    }
}
