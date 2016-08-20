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

        $this->assertSame([1 => $matcher2, 0 => $matcher1], $node->getMatchers());
        $this->assertSame($matcher1, $node->getFirstMatcher());
    }

    public function testParentRouteTreeNode()
    {
        $matcher = $this->mock(AbstractMatcher::class);
        $contents = new ChildrenNodeCollection();
        $node = new RouteTreeNode([$matcher], $contents);

        $this->assertSame([$matcher], $node->getMatchers());
        $this->assertSame($matcher, $node->getFirstMatcher());
        $this->assertSame($contents, $node->getContents());
        $this->assertTrue($node->isParentNode());
        $this->assertFalse($node->isLeafNode());
    }

    public function testLeafRouteTreeNode()
    {
        $matcher = $this->mock(AbstractMatcher::class);
        $contents = new MatchedRouteDataMap();
        $node = new RouteTreeNode([$matcher], $contents);

        $this->assertSame([$matcher], $node->getMatchers());
        $this->assertSame($matcher, $node->getFirstMatcher());
        $this->assertSame($contents, $node->getContents());
        $this->assertTrue($node->isLeafNode());
        $this->assertFalse($node->isParentNode());
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

        $this->assertSame([$child->getFirstMatcher()->getHash() => $child], $node->getContents()->getChildren());
        $this->assertTrue($node->getContents()->hasChild($child));
        $this->assertTrue($node->getContents()->hasChildFor($child->getFirstMatcher()));
        $this->assertTrue($node->getContents()->hasChild(clone $child));
        $this->assertTrue($node->getContents()->hasChildFor(clone $child->getFirstMatcher()));
        $this->assertFalse($node->getContents()->hasChildFor($matcher3));
        $this->assertSame($child, $node->getContents()->getChild($child->getFirstMatcher()));
    }

    public function testMatchedRouteDataMapOperations()
    {
        $node = new RouteTreeNode([$this->mock(AbstractMatcher::class)], new MatchedRouteDataMap());
        $node->getContents()->addRoute((new Route(['GET', 'POST'], '', null))->setParameter('first_route', ''), []);

        $this->assertSame(['GET', 'POST', 'HEAD'], $node->getContents()->getAllowedHttpMethods());
        $this->assertEquals(
            [
                [['GET', 'POST', 'HEAD'], [[], ['first_route' => '']]],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );
        $this->assertNull($node->getContents()->getDefaultRouteData());
        $this->assertFalse($node->getContents()->hasDefaultRouteData());

        $node->getContents()->addRoute((new Route('PATCH', '', null))->setParameter('second_route', ''), [0 => 'param']);

        $this->assertSame(['GET', 'POST', 'HEAD', 'PATCH'], $node->getContents()->getAllowedHttpMethods());
        $this->assertEquals(
            [
                [['GET', 'POST', 'HEAD'], [[], ['first_route' => '']]],
                [['PATCH'], [[0 => 'param'], ['second_route' => '']]],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );
        $this->assertNull($node->getContents()->getDefaultRouteData());
        $this->assertFalse($node->getContents()->hasDefaultRouteData());

        $node->getContents()->addRoute((new Route(['ANY'], '', null))->setParameter('third_route', ''), []);

        $this->assertSame('GET', $node->getContents()->getAllowedHttpMethods());
        $this->assertEquals(
            [
                [['GET', 'POST', 'HEAD'], [[], ['first_route' => '']]],
                [['PATCH'], [[0 => 'param'], ['second_route' => '']]],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );
        $this->assertEquals([[], ['third_route' => '']], $node->getContents()->getDefaultRouteData());
        $this->assertTrue($node->getContents()->hasDefaultRouteData());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Cannot construct Viserio\Routing\Generator\RouteTreeNode: matchers must not be empty.
     */
    public function testThrowsExceptionForEmptyMatchers()
    {
        new RouteTreeNode([], new ChildrenNodeCollection([]));
    }
}
