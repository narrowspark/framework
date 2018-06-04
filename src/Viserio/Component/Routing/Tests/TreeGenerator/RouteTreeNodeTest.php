<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\TreeGenerator;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Routing\Matcher\AbstractMatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection;
use Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap;
use Viserio\Component\Routing\TreeGenerator\RouteTreeNode;

/**
 * @internal
 */
final class RouteTreeNodeTest extends MockeryTestCase
{
    public function testMaintainsMatcherOrder(): void
    {
        $matcher1 = $this->mock(AbstractMatcher::class);
        $matcher2 = $this->mock(AbstractMatcher::class);

        $node = new RouteTreeNode([1 => $matcher2, 0 => $matcher1], new ChildrenNodeCollection());

        $this->assertSame([1 => $matcher2, 0 => $matcher1], $node->getMatchers());
        $this->assertSame($matcher1, $node->getFirstMatcher());
    }

    public function testParentRouteTreeNode(): void
    {
        $matcher  = $this->mock(AbstractMatcher::class);
        $contents = new ChildrenNodeCollection();
        $node     = new RouteTreeNode([$matcher], $contents);

        $this->assertSame([$matcher], $node->getMatchers());
        $this->assertSame($matcher, $node->getFirstMatcher());
        $this->assertSame($contents, $node->getContents());
        $this->assertTrue($node->isParentNode());
        $this->assertFalse($node->isLeafNode());
    }

    public function testLeafRouteTreeNode(): void
    {
        $matcher  = $this->mock(AbstractMatcher::class);
        $contents = new MatchedRouteDataMap();
        $node     = new RouteTreeNode([$matcher], $contents);

        $this->assertSame([$matcher], $node->getMatchers());
        $this->assertSame($matcher, $node->getFirstMatcher());
        $this->assertSame($contents, $node->getContents());
        $this->assertTrue($node->isLeafNode());
        $this->assertFalse($node->isParentNode());
    }

    public function testChildrenCollectionOperations(): void
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

        $node  = new RouteTreeNode([$matcher1], new ChildrenNodeCollection());
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

    public function testMatchedRouteDataMapOperations(): void
    {
        $node = new RouteTreeNode([$this->mock(AbstractMatcher::class)], new MatchedRouteDataMap());
        $node->getContents()->addRoute(new Route(['GET', 'POST'], '', null), []);

        $this->assertSame(['GET', 'POST', 'HEAD'], $node->getContents()->allowedHttpMethods());
        $this->assertEquals(
            [
                [['GET', 'POST', 'HEAD'], [[], 'GET|POST|HEAD']],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );

        $node->getContents()->addRoute(new Route('PATCH', '', null), [0 => 'param']);

        $this->assertSame(['GET', 'POST', 'HEAD', 'PATCH'], $node->getContents()->allowedHttpMethods());
        $this->assertEquals(
            [
                [['GET', 'POST', 'HEAD'], [[], 'GET|POST|HEAD']],
                [['PATCH'], [[0 => 'param'], 'PATCH']],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );
    }

    public function testThrowsExceptionForEmptyMatchers(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot construct [Viserio\\Component\\Routing\\TreeGenerator\\RouteTreeNode], matchers must not be empty.');

        new RouteTreeNode([], new ChildrenNodeCollection([]));
    }
}
