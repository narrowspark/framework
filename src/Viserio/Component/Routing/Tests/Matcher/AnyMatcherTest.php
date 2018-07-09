<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Matchers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Matcher\AnyMatcher;

/**
 * @internal
 */
final class AnyMatcherTest extends TestCase
{
    public function testGetConditionExpression(): void
    {
        $matcher = new AnyMatcher([0]);

        static::assertSame('segment/[test] !== \'\'', $matcher->getConditionExpression('segment/[test]'));
    }

    public function testAnyMergingParameterKeys(): void
    {
        $matcher1 = new AnyMatcher([123]);
        $matcher2 = new AnyMatcher([12, 3]);
        $matcher1->mergeParameterKeys($matcher2);

        static::assertSame([123, 12, 3], $matcher1->getParameterKeys());
    }
}
