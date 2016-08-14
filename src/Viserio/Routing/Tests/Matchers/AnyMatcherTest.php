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
}
