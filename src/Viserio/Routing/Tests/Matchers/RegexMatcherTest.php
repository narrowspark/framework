<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use Viserio\Contracts\Routing\Pattern;
use Viserio\Routing\Matchers\RegexMatcher;
use PHPUnit\Framework\TestCase;

class RegexMatcherTest extends TestCase
{
    public function testGetGroupCount()
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        self::assertSame(1, $matcher->getGroupCount());
    }

    public function testGetRegex()
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        self::assertSame('/^(' . Pattern::ALPHA . ')$/', $matcher->getRegex());
    }

    public function testGetParameterKeyGroupMap()
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        self::assertSame([12 => 0], $matcher->getParameterKeyGroupMap());
    }

    public function testGetConditionExpression()
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        self::assertSame('preg_match(\'/^([a-zA-Z]+)$/\', test, $matches)', $matcher->getConditionExpression('test'));
    }

    public function testGetMatchedParameterExpressions()
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        self::assertSame([12 => '$matches[1]'], $matcher->getMatchedParameterExpressions('test'));
    }

    public function testRegexMergingParameterKeys()
    {
        $matcher1 = new RegexMatcher('/^(' . Pattern::ANY . ')$/', 12);
        $matcher2 = new RegexMatcher('/^(' . Pattern::ANY . ')$/', 11);
        $matcher1->mergeParameterKeys($matcher2);

        self::assertSame([12, 11], $matcher1->getParameterKeys());
        self::assertSame([12 => 0, 11 => 0], $matcher1->getParameterKeyGroupMap());
    }
}
