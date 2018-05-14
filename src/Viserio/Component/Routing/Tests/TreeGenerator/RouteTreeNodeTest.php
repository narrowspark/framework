<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Routing\Tests\TreeGenerator;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Routing\Matcher\AbstractMatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection;
use Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap;
use Viserio\Component\Routing\TreeGenerator\RouteTreeNode;

/**
 * @internal
 *
 * @small
 */
final class RouteTreeNodeTest extends MockeryTestCase
{
    public function testMaintainsMatcherOrder(): void
    {
        $matcher1 = \Mockery::mock(AbstractMatcher::class);
        $matcher2 = \Mockery::mock(AbstractMatcher::class);

        $node = new RouteTreeNode([1 => $matcher2, 0 => $matcher1], new ChildrenNodeCollection());

        self::assertSame([1 => $matcher2, 0 => $matcher1], $node->getMatchers());
        self::assertSame($matcher1, $node->getFirstMatcher());
    }

    public function testParentRouteTreeNode(): void
    {
        $matcher = \Mockery::mock(AbstractMatcher::class);
        $contents = new ChildrenNodeCollection();
        $node = new RouteTreeNode([$matcher], $contents);

        self::assertSame([$matcher], $node->getMatchers());
        self::assertSame($matcher, $node->getFirstMatcher());
        self::assertSame($contents, $node->getContents());
        self::assertTrue($node->isParentNode());
        self::assertFalse($node->isLeafNode());
    }

    public function testLeafRouteTreeNode(): void
    {
        $matcher = \Mockery::mock(AbstractMatcher::class);
        $contents = new MatchedRouteDataMap();
        $node = new RouteTreeNode([$matcher], $contents);

        self::assertSame([$matcher], $node->getMatchers());
        self::assertSame($matcher, $node->getFirstMatcher());
        self::assertSame($contents, $node->getContents());
        self::assertTrue($node->isLeafNode());
        self::assertFalse($node->isParentNode());
    }

    public function testChildrenCollectionOperations(): void
    {
        $matcher1 = \Mockery::mock(AbstractMatcher::class);
        $matcher2 = \Mockery::mock(AbstractMatcher::class);
        $matcher2->shouldReceive('getHash')
            ->times(7)
            ->andReturn('some-hash');
        $matcher3 = \Mockery::mock(AbstractMatcher::class);
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

    public function testMatchedRouteDataMapOperations(): void
    {
        $node = new RouteTreeNode([\Mockery::mock(AbstractMatcher::class)], new MatchedRouteDataMap());
        $node->getContents()->addRoute(new Route(['GET', 'POST'], '', null), []);

        self::assertSame(['GET', 'POST', 'HEAD'], $node->getContents()->allowedHttpMethods());
        self::assertEquals(
            [
                [['GET', 'POST', 'HEAD'], [[], 'GET|POST|HEAD']],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );

        $node->getContents()->addRoute(new Route('PATCH', '', null), [0 => 'param']);

        self::assertSame(['GET', 'POST', 'HEAD', 'PATCH'], $node->getContents()->allowedHttpMethods());
        self::assertEquals(
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
