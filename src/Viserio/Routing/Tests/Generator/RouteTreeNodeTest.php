<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Generator;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Routing\Generator\ChildrenNodeCollection;
use Viserio\Routing\Generator\MatchedRouteDataMap;
use Viserio\Routing\Generator\RouteTreeNode;
use Viserio\Routing\Matchers\AbstractMatcher;
use Viserio\Routing\Route;

class RouteTreeNodeTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testMaintainsMatcherOrder()
    {
        $matcher1 = $this->mock(AbstractMatcher::class);
        $matcher2 = $this->mock(AbstractMatcher::class);

        $node = new RouteTreeNode([1 => $matcher2, 0 => $matcher1], new ChildrenNodeCollection());

        self::assertSame([1 => $matcher2, 0 => $matcher1], $node->getMatchers());
        self::assertSame($matcher1, $node->getFirstMatcher());
    }

    public function testParentRouteTreeNode()
    {
        $matcher = $this->mock(AbstractMatcher::class);
        $contents = new ChildrenNodeCollection();
        $node = new RouteTreeNode([$matcher], $contents);

        self::assertSame([$matcher], $node->getMatchers());
        self::assertSame($matcher, $node->getFirstMatcher());
        self::assertSame($contents, $node->getContents());
        self::assertTrue($node->isParentNode());
        self::assertFalse($node->isLeafNode());
    }

    public function testLeafRouteTreeNode()
    {
        $matcher = $this->mock(AbstractMatcher::class);
        $contents = new MatchedRouteDataMap();
        $node = new RouteTreeNode([$matcher], $contents);

        self::assertSame([$matcher], $node->getMatchers());
        self::assertSame($matcher, $node->getFirstMatcher());
        self::assertSame($contents, $node->getContents());
        self::assertTrue($node->isLeafNode());
        self::assertFalse($node->isParentNode());
    }

    public function testChildrenCollectionOperations()
    {
        $matcher1 = $this->mock(AbstractMatcher::class);
        $matcher2 = $this->mock(AbstractMatcher::class);
        $matcher2->shouldReceive('getHash')
            ->times(7)
            ->andReturn('some-hash');
        $matcher3 = $this->mock(AbstractMatcher::class);
        $matcher3->shouldReceive('getHash')
            ->once()
            ->andReturn('some-other-hash');

        $node = new RouteTreeNode([$matcher1], new ChildrenNodeCollection());
        $child = new RouteTreeNode([$matcher2], new ChildrenNodeCollection());
        $node->getContents()->addChild($child);

        self::assertSame([$child->getFirstMatcher()->getHash() => $child], $node->getContents()->getChildren());
        self::assertTrue($node->getContents()->hasChild($child));
        self::assertTrue($node->getContents()->hasChildFor($child->getFirstMatcher()));
        self::assertTrue($node->getContents()->hasChild(clone $child));
        self::assertTrue($node->getContents()->hasChildFor(clone $child->getFirstMatcher()));
        self::assertFalse($node->getContents()->hasChildFor($matcher3));
        self::assertSame($child, $node->getContents()->getChild($child->getFirstMatcher()));
    }

    public function testMatchedRouteDataMapOperations()
    {
        $node = new RouteTreeNode([$this->mock(AbstractMatcher::class)], new MatchedRouteDataMap());
        $node->getContents()->addRoute((new Route(['GET', 'POST'], '', null)), []);

        self::assertSame(['GET', 'POST', 'HEAD'], $node->getContents()->getAllowedHttpMethods());
        self::assertEquals(
            [
                [['GET', 'POST', 'HEAD'], [[], 'GET|POST|HEAD']],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );

        $node->getContents()->addRoute((new Route('PATCH', '', null)), [0 => 'param']);

        self::assertSame(['GET', 'POST', 'HEAD', 'PATCH'], $node->getContents()->getAllowedHttpMethods());
        self::assertEquals(
            [
                [['GET', 'POST', 'HEAD'], [[], 'GET|POST|HEAD']],
                [['PATCH'], [[0 => 'param'], 'PATCH']],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot construct Viserio\Routing\Generator\RouteTreeNode: matchers must not be empty.
     */
    public function testThrowsExceptionForEmptyMatchers()
    {
        new RouteTreeNode([], new ChildrenNodeCollection([]));
    }
}
