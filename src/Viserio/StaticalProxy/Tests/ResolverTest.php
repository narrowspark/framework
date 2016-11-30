<?php
declare(strict_types=1);
namespace Viserio\StaticalProxy\Tests;

use Viserio\StaticalProxy\Resolver;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolveWithoutRegex()
    {
        $resolver = new Resolver('pattern', 'stdClass');

        self::assertSame('stdClass', $resolver->resolve('pattern'));
    }

    public function testResolveWithRegex()
    {
        $resolver = new Resolver('Pattern\*', '$1');

        self::assertSame('stdClass', $resolver->resolve('Pattern\stdClass'));
    }

    public function testFailingResolve()
    {
        $resolver = new Resolver('pattern', 'translation');

        self::assertFalse($resolver->resolve('other_pattern'));
        self::assertFalse($resolver->resolve('pattern'));
    }

    public function testMatches()
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
