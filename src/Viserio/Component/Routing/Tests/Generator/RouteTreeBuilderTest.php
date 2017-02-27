<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Routing\Pattern;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection;
use Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap;
use Viserio\Component\Routing\TreeGenerator\RouteTreeBuilder;
use Viserio\Component\Routing\TreeGenerator\RouteTreeNode;
use Viserio\Component\Routing\Matchers\RegexMatcher;
use Viserio\Component\Routing\Matchers\StaticMatcher;
use Viserio\Component\Routing\Route;

class RouteTreeBuilderTest extends TestCase
{
    public function routeTreeBuilderCases()
    {
        return [
            [
                [(new Route(RouterContract::HTTP_METHOD_VARS, '', null))],
                new MatchedRouteDataMap([[RouterContract::HTTP_METHOD_VARS, [[], 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS']]]),
                [],
            ],
            [
                [(new Route(RouterContract::HTTP_METHOD_VARS, '/', null))],
                null,
                [
                    1 => new ChildrenNodeCollection([
                        (new StaticMatcher(''))->getHash() => new RouteTreeNode(
                            [0 => new StaticMatcher('')],
                            new MatchedRouteDataMap([[RouterContract::HTTP_METHOD_VARS, [[], 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS/']]])
                        ),
                    ]),
                ],
            ],
            [
                [(new Route(['GET'], '/{param}', null))->where('param', Pattern::ANY)],
                null,
                [
                    1 => new ChildrenNodeCollection([
                        (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode(
                            [0 => new RegexMatcher(Pattern::ANY, 0)],
                            new MatchedRouteDataMap([
                                [['GET', 'HEAD'], [[0 => 'param'], 'GET|HEAD/{param}']],
                            ])
                        ),
                    ]),
                ],
            ],
            [
                [
                    (new Route(['GET'], '/first/{param1}', null)),
                    (new Route(['GET'], '/{param1}/{param2}', null))->where(['param1', 'param2'], Pattern::ALPHA),
                ],
                null,
                [
                    2 => new ChildrenNodeCollection([
                        (new StaticMatcher('first'))->getHash() => new RouteTreeNode(
                            [0 => new StaticMatcher('first')],
                            new ChildrenNodeCollection([
                                (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode(
                                    [1 => new RegexMatcher(Pattern::ANY, 0)],
                                    new MatchedRouteDataMap([
                                        [['GET', 'HEAD'], [[0 => 'param1'], 'GET|HEAD/first/{param1}']],
                                    ])
                                ),
                            ])
                        ),
                        (new RegexMatcher(Pattern::ALPHA, 0))->getHash() => new RouteTreeNode(
                            [0 => new RegexMatcher(Pattern::ALPHA, 0)],
                            new ChildrenNodeCollection([
                                (new RegexMatcher(Pattern::ALPHA, 1))->getHash() => new RouteTreeNode(
                                    [1 => new RegexMatcher(Pattern::ALPHA, 1)],
                                    new MatchedRouteDataMap([
                                        [['GET', 'HEAD'], [[0 => 'param1', 1 => 'param2'], 'GET|HEAD/{param1}/{param2}']],
                                    ])
                                ),
                            ])
                        ),
                    ]),
                ],
            ],
            [
                [
                    (new Route(RouterContract::HTTP_METHOD_VARS, '', null)),
                    (new Route(RouterContract::HTTP_METHOD_VARS, '/main', null)),
                    (new Route(['GET'], '/main/place', null)),
                    (new Route(['POST'], '/main/place', null)),
                    (new Route(RouterContract::HTTP_METHOD_VARS, '/main/thing', null)),
                    (new Route(RouterContract::HTTP_METHOD_VARS, '/main/thing/abc', null)),
                    (new Route(RouterContract::HTTP_METHOD_VARS, '/user/{name}', null))->where('name', Pattern::ANY),
                    (new Route(RouterContract::HTTP_METHOD_VARS, '/user/{name}/edit', null))->where('name', Pattern::ANY),
                    (new Route(RouterContract::HTTP_METHOD_VARS, '/user/create', null))->setParameter('user.create', ''),
                ],
                new MatchedRouteDataMap([[RouterContract::HTTP_METHOD_VARS, [[], 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS']]]),
                [
                    1 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode(
                            [0 => new StaticMatcher('main')],
                            new MatchedRouteDataMap([[RouterContract::HTTP_METHOD_VARS, [[], 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS/main']]])
                        ),
                    ]),
                    2 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('main')], new ChildrenNodeCollection([
                            (new StaticMatcher('place'))->getHash() => new RouteTreeNode(
                                [1 => new StaticMatcher('place')],
                                new MatchedRouteDataMap([
                                    [['GET', 'HEAD'], [[], 'GET|HEAD/main/place']],
                                    [['POST'], [[], 'POST/main/place']],
                                ])
                            ),
                            (new StaticMatcher('thing'))->getHash() => new RouteTreeNode(
                                [1 => new StaticMatcher('thing')],
                                new MatchedRouteDataMap([[RouterContract::HTTP_METHOD_VARS, [[], 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS/main/thing']]])
                            ),
                        ])),
                        (new StaticMatcher('user'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('user')], new ChildrenNodeCollection([
                            (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode(
                                [1 => new RegexMatcher(Pattern::ANY, 0)],
                                new MatchedRouteDataMap([[RouterContract::HTTP_METHOD_VARS, [[0 => 'name'], 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS/user/{name}']]])
                            ),
                            (new StaticMatcher('create'))->getHash() => new RouteTreeNode(
                                [1 => new StaticMatcher('create')],
                                new MatchedRouteDataMap([[RouterContract::HTTP_METHOD_VARS, [[], 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS/user/create']]])
                            ),
                        ])),
                    ]),
                    3 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('main')], new ChildrenNodeCollection([
                            (new StaticMatcher('thing'))->getHash() => new RouteTreeNode([1 => new StaticMatcher('thing')], new ChildrenNodeCollection([
                                (new StaticMatcher('abc'))->getHash() => new RouteTreeNode(
                                    [2 => new StaticMatcher('abc')],
                                    new MatchedRouteDataMap([[RouterContract::HTTP_METHOD_VARS, [[], 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS/main/thing/abc']]])
                                ),
                            ])),
                        ])),
                        (new StaticMatcher('user'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('user')], new ChildrenNodeCollection([
                            (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode([1 => new RegexMatcher(Pattern::ANY, 0)], new ChildrenNodeCollection([
                                (new StaticMatcher('edit'))->getHash() => new RouteTreeNode(
                                    [2 => new StaticMatcher('edit')],
                                    new MatchedRouteDataMap([[RouterContract::HTTP_METHOD_VARS, [[0 => 'name'], 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS/user/{name}/edit']]])
                                ),
                            ])),
                        ])),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @dataProvider routeTreeBuilderCases
     *
     * @param mixed $routes
     * @param mixed $rootRoute
     * @param mixed $segmentDepthNodesMap
     */
    public function testRouteTreeBuilder($routes, $rootRoute, $segmentDepthNodesMap)
    {
        list($rootRouteData, $segmentDepthNodeMap) = (new RouteTreeBuilder())->build($routes);

        self::assertSame($rootRoute !== null, $rootRouteData !== null);
        self::assertEquals($rootRoute, $rootRouteData);
        self::assertEquals($segmentDepthNodesMap, $segmentDepthNodeMap);
    }
}
