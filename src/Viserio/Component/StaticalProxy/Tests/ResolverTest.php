<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\StaticalProxy\Resolver;

class ResolverTest extends TestCase
{
    public function testResolveWithoutRegex(): void
    {
        $resolver = new Resolver('pattern', 'stdClass');

        self::assertSame('stdClass', $resolver->resolve('pattern'));
    }

    public function testResolveWithRegex(): void
    {
        $resolver = new Resolver('Pattern\*', '$1');

        self::assertSame('stdClass', $resolver->resolve('Pattern\stdClass'));
    }

    public function testFailingResolve(): void
    {
        $resolver = new Resolver('pattern', 'translation');

        self::assertFalse((bool) $resolver->resolve('other_pattern'));
        self::assertFalse((bool) $resolver->resolve('pattern'));
    }

    public function testMatches(): void
    {
        $resolver = new Resolver('pattern', 'translation');

        self::assertTrue($resolver->matches('pattern'));
        self::assertTrue($resolver->matches('pattern', 'translation'));
        self::assertFalse($resolver->matches('other_pattern', 'translation'));
        self::assertFalse($resolver->matches('pattern', 'other_translation'));
        self::assertFalse($resolver->matches('other_pattern', 'other_translation'));
        self::assertFalse($resolver->matches('other_pattern'));
    }
}
