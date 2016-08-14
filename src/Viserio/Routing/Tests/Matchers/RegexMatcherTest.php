<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use Viserio\Routing\{
    Pattern,
    Matchers\RegexMatcher
};

class RegexMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGroupCount()
    {
        $matcher = new RegexMatcher(Pattern::ALPHA, 12);

        $this->assertSame(1, $matcher->getGroupCount());
    }

    public function testGetRegex()
    {
        $matcher = new RegexMatcher(Pattern::ALPHA, 12);

        $this->assertSame(Pattern::asRegex(Pattern::ALPHA), $matcher->getRegex());
    }

    public function testGetParameterKeyGroupMap()
    {
        $matcher = new RegexMatcher(Pattern::ALPHA, 12);

        $this->assertSame([12 => 0], $matcher->getParameterKeyGroupMap());
    }

    public function testGetConditionExpression()
    {
        $matcher = new RegexMatcher(Pattern::ALPHA, 12);

        $this->assertSame('preg_match(\'/^([a-zA-Z]+)$/\', test, $matches)', $matcher->getConditionExpression('test'));
    }

    public function testGetMatchedParameterExpressions()
    {
        $matcher = new RegexMatcher(Pattern::ALPHA, 12);

        $this->assertSame([12 => '$matches[1]'], $matcher->getMatchedParameterExpressions('test'));
    }
}
