<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use Viserio\Routing\Matchers\{
    AnyMatcher,
    CompoundMatcher,
    StaticMatcher
};

class CompoundMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConditionExpression()
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0])
        ]);

        $this->assertSame('test === \'test\' && test !== \'\'', $matcher->getConditionExpression('test', '2'));
    }

    public function testGetMatchedParameterExpressions()
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0])
        ]);

        $this->assertSame([1 => 'test', 0 => 'test'], $matcher->getMatchedParameterExpressions('test', '2'));
    }

    public function testGetHash()
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0])
        ]);

        $this->assertSame('Viserio\Routing\Matchers\CompoundMatcher:Viserio\Routing\Matchers\StaticMatcher:test::Viserio\Routing\Matchers\AnyMatcher:', $matcher->getHash());
    }
}
