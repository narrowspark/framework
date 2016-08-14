<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use Viserio\Routing\Matchers\AnyMatcher;

class AnyMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConditionExpression()
    {
        $matcher = new AnyMatcher([0]);

        $this->assertSame('segment/[test] !== \'\'', $matcher->getConditionExpression('segment/[test]'));
    }

    public function testAnyMergingParameterKeys()
    {
        $matcher1 = new AnyMatcher([123]);
        $matcher2 = new AnyMatcher([12, 3]);
        $matcher1->mergeParameterKeys($matcher2);

        $this->assertSame([123, 12, 3], $matcher1->getParameterKeys());
    }
}
