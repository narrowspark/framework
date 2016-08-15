<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use Viserio\Routing\Matchers\RegexMatcher;
use Viserio\Contracts\Routing\Pattern;

class RegexMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGroupCount()
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        $this->assertSame(1, $matcher->getGroupCount());
    }

    public function testGetRegex()
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        $this->assertSame('/^(' . Pattern::ALPHA . ')$/', $matcher->getRegex());
    }

    public function testGetParameterKeyGroupMap()
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        $this->assertSame([12 => 0], $matcher->getParameterKeyGroupMap());
    }

    public function testGetConditionExpression()
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        $this->assertSame('preg_match(\'/^([a-zA-Z]+)$/\', test, $matches)', $matcher->getConditionExpression('test'));
    }

    public function testGetMatchedParameterExpressions()
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        $this->assertSame([12 => '$matches[1]'], $matcher->getMatchedParameterExpressions('test'));
    }

    public function testRegexMergingParameterKeys()
    {
        $matcher1 = new RegexMatcher('/^(' . Pattern::ANY . ')$/', 12);
        $matcher2 = new RegexMatcher('/^(' . Pattern::ANY . ')$/', 11);
        $matcher1->mergeParameterKeys($matcher2);

        $this->assertSame([12, 11], $matcher1->getParameterKeys());
        $this->assertSame([12 => 0, 11 => 0], $matcher1->getParameterKeyGroupMap());
    }
}
