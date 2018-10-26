<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Engines;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\View\Engine as EngineContract;
use Viserio\Component\View\Engine\EngineResolver;

/**
 * @internal
 */
final class ViewEngineResolverTest extends MockeryTestCase
{
    public function testResolversMayBeResolved(): void
    {
        $resolver = new EngineResolver();
        $resolver->register(
            'foo',
            function () {
                return $this->mock(EngineContract::class);
            }
        );
        $result = $resolver->resolve('foo');
        $this->assertEquals(\spl_object_hash($result), \spl_object_hash($resolver->resolve('foo')));
    }

    public function testResolverThrowsExceptionOnUnknownEngine(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Engine [foo] not found.');

        $resolver = new EngineResolver();
        $resolver->resolve('foo');
    }
}
