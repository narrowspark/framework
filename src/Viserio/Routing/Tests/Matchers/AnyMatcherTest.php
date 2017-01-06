<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use PHPUnit\Framework\TestCase;
use Viserio\Routing\Matchers\AnyMatcher;

class AnyMatcherTest extends TestCase
{
    public function testGetConditionExpression()
    {
        $matcher = new AnyMatcher([0]);

        self::assertSame('segment/[test] !== \'\'', $matcher->getConditionExpression('segment/[test]'));
    }

    public function testAnyMergingParameterKeys()
    {
        $matcher1 = new AnyMatcher([123]);
        $matcher2 = new AnyMatcher([12, 3]);
        $matcher1->mergeParameterKeys($matcher2);

        self::assertSame([123, 12, 3], $matcher1->getParameterKeys());
    }
}
