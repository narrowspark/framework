<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\StaticalProxy\Resolver;

/**
 * @internal
 */
final class ResolverTest extends TestCase
{
    public function testResolveWithoutRegex(): void
    {
        $resolver = new Resolver('pattern', 'stdClass');

        $this->assertSame('stdClass', $resolver->resolve('pattern'));
    }

    public function testResolveWithRegex(): void
    {
        $resolver = new Resolver('Pattern\*', '$1');

        $this->assertSame('stdClass', $resolver->resolve('Pattern\stdClass'));
    }

    public function testFailingResolve(): void
    {
        $resolver = new Resolver('pattern', 'translation');

        $this->assertFalse((bool) $resolver->resolve('other_pattern'));
        $this->assertFalse((bool) $resolver->resolve('pattern'));
    }

    public function testMatches(): void
    {
        $resolver = new Resolver('pattern', 'translation');

        $this->assertTrue($resolver->matches('pattern'));
        $this->assertTrue($resolver->matches('pattern', 'translation'));
        $this->assertFalse($resolver->matches('other_pattern', 'translation'));
        $this->assertFalse($resolver->matches('pattern', 'other_translation'));
        $this->assertFalse($resolver->matches('other_pattern', 'other_translation'));
        $this->assertFalse($resolver->matches('other_pattern'));
    }
}
