<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Generator;

use Viserio\Contracts\Routing\Pattern;
use Viserio\Routing\Generator\ChildrenNodeCollection;
use Viserio\Routing\Generator\MatchedRouteDataMap;
use Viserio\Routing\Generator\RouteTreeBuilder;
use Viserio\Routing\Generator\RouteTreeNode;
use Viserio\Routing\Matchers\RegexMatcher;
use Viserio\Routing\Matchers\StaticMatcher;
use Viserio\Routing\Route;

class RouteTreeBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function routeTreeBuilderCases()
    {
        return [
            [
                [(new Route('ANY', '', null))],
                new MatchedRouteDataMap([], [[], 'ANY']),
                [],
            ],
            [
                [(new Route('ANY', '/', null))],
                null,
                [
                    1 => new ChildrenNodeCollection([
                        (new StaticMatcher(''))->getHash() => new RouteTreeNode(
                            [0 => new StaticMatcher('')],
                            new MatchedRouteDataMap([], [[], 'ANY/'])
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
                    (new Route('ANY', '', null)),
                    (new Route('ANY', '/main', null)),
                    (new Route(['GET'], '/main/place', null)),
                    (new Route(['POST'], '/main/place', null)),
                    (new Route('ANY', '/main/thing', null)),
                    (new Route('ANY', '/main/thing/abc', null)),
                    (new Route('ANY', '/user/{name}', null))->where('name', Pattern::ANY),
                    (new Route('ANY', '/user/{name}/edit', null))->where('name', Pattern::ANY),
                    (new Route('ANY', '/user/create', null))->setParameter('user.create', ''),
                ],
                new MatchedRouteDataMap([], [[], 'ANY']),
                [
                    1 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode(
                            [0 => new StaticMatcher('main')],
                            new MatchedRouteDataMap([], [[], 'ANY/main'])
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
                                new MatchedRouteDataMap([], [[], 'ANY/main/thing'])
                            ),
                        ])),
                        (new StaticMatcher('user'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('user')], new ChildrenNodeCollection([
                            (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode(
                                [1 => new RegexMatcher(Pattern::ANY, 0)],
                                new MatchedRouteDataMap([], [[0 => 'name'], 'ANY/user/{name}'])
                            ),
                            (new StaticMatcher('create'))->getHash() => new RouteTreeNode(
                                [1 => new StaticMatcher('create')],
                                new MatchedRouteDataMap([], [[], 'ANY/user/create'])
                            ),
                        ])),
                    ]),
                    3 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('main')], new ChildrenNodeCollection([
                            (new StaticMatcher('thing'))->getHash() => new RouteTreeNode([1 => new StaticMatcher('thing')], new ChildrenNodeCollection([
                                (new StaticMatcher('abc'))->getHash() => new RouteTreeNode(
                                    [2 => new StaticMatcher('abc')],
                                    new MatchedRouteDataMap([], [[], 'ANY/main/thing/abc'])
                                ),
                            ])),
                        ])),
                        (new StaticMatcher('user'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('user')], new ChildrenNodeCollection([
                            (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode([1 => new RegexMatcher(Pattern::ANY, 0)], new ChildrenNodeCollection([
                                (new StaticMatcher('edit'))->getHash() => new RouteTreeNode(
                                    [2 => new StaticMatcher('edit')],
                                    new MatchedRouteDataMap([], [[0 => 'name'], 'ANY/user/{name}/edit'])
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
     */
    public function testRouteTreeBuilder($routes, $rootRoute, $segmentDepthNodesMap)
    {
        list($rootRouteData, $segmentDepthNodeMap) = (new RouteTreeBuilder())->build($routes);

        $this->assertSame($rootRoute !== null, $rootRouteData !== null && ! $rootRouteData->isEmpty());
        $this->assertEquals($rootRoute, $rootRouteData);
        $this->assertEquals($segmentDepthNodesMap, $segmentDepthNodeMap);
    }
}
