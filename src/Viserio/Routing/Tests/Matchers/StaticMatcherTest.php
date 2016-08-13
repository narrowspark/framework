<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use Viserio\Routing\Matchers\StaticMatcher;

class StaticMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testCannotContainSlash()
    {
        new StaticMatcher('abc/foo');
    }

    public function testGetConditionExpression()
    {
        $matcher = new StaticMatcher('one');

        $this->assertSame('one === \'one\'', $matcher->getConditionExpression('one'));
    }

    public function testGetMatchedParameterExpressions()
    {
        $matcher = new StaticMatcher('two', [1]);

        $this->assertSame([1 => 'two'], $matcher->getMatchedParameterExpressions('two'));

        $matcher = new StaticMatcher('three');

        $this->assertSame([], $matcher->getMatchedParameterExpressions('three'));
    }

    public function testMergeParameterKeys()
    {
        $matcher = new StaticMatcher('two', [2]);
        $matcher2 = new StaticMatcher('two', [3]);
        $matcher->mergeParameterKeys($matcher2);

        $this->assertSame([2 => 'two'], $matcher->getMatchedParameterExpressions('two'));
    }
}
