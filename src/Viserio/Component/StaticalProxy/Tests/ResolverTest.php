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

        static::assertSame('stdClass', $resolver->resolve('pattern'));
    }

    public function testResolveWithRegex(): void
    {
        $resolver = new Resolver('Pattern\*', '$1');

        static::assertSame('stdClass', $resolver->resolve('Pattern\stdClass'));
    }

    public function testFailingResolve(): void
    {
        $resolver = new Resolver('pattern', 'translation');

        static::assertFalse((bool) $resolver->resolve('other_pattern'));
        static::assertFalse((bool) $resolver->resolve('pattern'));
    }

    public function testMatches(): void
    {
        $resolver = new Resolver('pattern', 'translation');

        static::assertTrue($resolver->matches('pattern'));
        static::assertTrue($resolver->matches('pattern', 'translation'));
        static::assertFalse($resolver->matches('other_pattern', 'translation'));
        static::assertFalse($resolver->matches('pattern', 'other_translation'));
        static::assertFalse($resolver->matches('other_pattern', 'other_translation'));
        static::assertFalse($resolver->matches('other_pattern'));
    }
}
