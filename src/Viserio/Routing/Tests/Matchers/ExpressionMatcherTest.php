<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use Viserio\Routing\Matchers\ExpressionMatcher;

class ExpressionMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testGetExpression()
    {
        $matcher = new ExpressionMatcher('ctype_digit({segment})', [1]);

        $this->assertSame('ctype_digit({segment})', $matcher->getExpression());
    }

    public function testGetConditionExpression()
    {
        $matcher = new ExpressionMatcher('ctype_digit({segment})', [1]);

        $this->assertSame('ctype_digit(test)', $matcher->getConditionExpression('test'));
    }
}
