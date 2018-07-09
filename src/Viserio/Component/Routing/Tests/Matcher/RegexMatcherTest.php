<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Matchers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Routing\Pattern;
use Viserio\Component\Routing\Matcher\RegexMatcher;

/**
 * @internal
 */
final class RegexMatcherTest extends TestCase
{
    public function testGetGroupCount(): void
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        static::assertSame(1, $matcher->getGroupCount());
    }

    public function testGetRegex(): void
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        static::assertSame('/^(' . Pattern::ALPHA . ')$/', $matcher->getRegex());
    }

    public function testGetParameterKeyGroupMap(): void
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        static::assertSame([12 => 0], $matcher->getParameterKeyGroupMap());
    }

    public function testGetConditionExpression(): void
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        static::assertSame('preg_match(\'/^([a-zA-Z]+)$/\', test, $matches)', $matcher->getConditionExpression('test'));
    }

    public function testGetMatchedParameterExpressions(): void
    {
        $matcher = new RegexMatcher('/^(' . Pattern::ALPHA . ')$/', 12);

        static::assertSame([12 => '$matches[1]'], $matcher->getMatchedParameterExpressions('test'));
    }

    public function testRegexMergingParameterKeys(): void
    {
        $matcher1 = new RegexMatcher('/^(' . Pattern::ANY . ')$/', 12);
        $matcher2 = new RegexMatcher('/^(' . Pattern::ANY . ')$/', 11);
        $matcher1->mergeParameterKeys($matcher2);

        static::assertSame([12, 11], $matcher1->getParameterKeys());
        static::assertSame([12 => 0, 11 => 0], $matcher1->getParameterKeyGroupMap());
    }
}
