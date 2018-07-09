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

        static::assertSame([1 => $matcher2, 0 => $matcher1], $node->getMatchers());
        static::assertSame($matcher1, $node->getFirstMatcher());
    }

    public function testParentRouteTreeNode(): void
    {
        $matcher  = $this->mock(AbstractMatcher::class);
        $contents = new ChildrenNodeCollection();
        $node     = new RouteTreeNode([$matcher], $contents);

        static::assertSame([$matcher], $node->getMatchers());
        static::assertSame($matcher, $node->getFirstMatcher());
        static::assertSame($contents, $node->getContents());
        static::assertTrue($node->isParentNode());
        static::assertFalse($node->isLeafNode());
    }

    public function testLeafRouteTreeNode(): void
    {
        $matcher  = $this->mock(AbstractMatcher::class);
        $contents = new MatchedRouteDataMap();
        $node     = new RouteTreeNode([$matcher], $contents);

        static::assertSame([$matcher], $node->getMatchers());
        static::assertSame($matcher, $node->getFirstMatcher());
        static::assertSame($contents, $node->getContents());
        static::assertTrue($node->isLeafNode());
        static::assertFalse($node->isParentNode());
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

        static::assertSame([$child->getFirstMatcher()->getHash() => $child], $node->getContents()->getChildren());
        static::assertTrue($node->getContents()->hasChild($child));
        static::assertTrue($node->getContents()->hasChildFor($child->getFirstMatcher()));
        static::assertTrue($node->getContents()->hasChild(clone $child));
        static::assertTrue($node->getContents()->hasChildFor(clone $child->getFirstMatcher()));
        static::assertFalse($node->getContents()->hasChildFor($matcher3));
        static::assertSame($child, $node->getContents()->getChild($child->getFirstMatcher()));
    }

    public function testMatchedRouteDataMapOperations(): void
    {
        $node = new RouteTreeNode([$this->mock(AbstractMatcher::class)], new MatchedRouteDataMap());
        $node->getContents()->addRoute(new Route(['GET', 'POST'], '', null), []);

        static::assertSame(['GET', 'POST', 'HEAD'], $node->getContents()->allowedHttpMethods());
        static::assertEquals(
            [
                [['GET', 'POST', 'HEAD'], [[], 'GET|POST|HEAD']],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );

        $node->getContents()->addRoute(new Route('PATCH', '', null), [0 => 'param']);

        static::assertSame(['GET', 'POST', 'HEAD', 'PATCH'], $node->getContents()->allowedHttpMethods());
        static::assertEquals(
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
