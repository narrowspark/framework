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
                [(new Route('ANY', '', null))->setParameter('route', '')],
                new MatchedRouteDataMap([], [[], ['route' => '']]),
                [],
            ],
            [
                [(new Route('ANY', '/', null))->setParameter('route', '')],
                null,
                [
                    1 => new ChildrenNodeCollection([
                        (new StaticMatcher(''))->getHash() => new RouteTreeNode(
                            [0 => new StaticMatcher('')],
                            new MatchedRouteDataMap([], [[], ['route' => '']])
                        ),
                    ]),
                ],
            ],
            [
                [(new Route(['GET'], '/{param}', null))->where('param', Pattern::ANY)->setParameter('root-route', '')],
                null,
                [
                    1 => new ChildrenNodeCollection([
                        (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode(
                            [0 => new RegexMatcher(Pattern::ANY, 0)],
                            new MatchedRouteDataMap([
                                [['GET', 'HEAD'], [[0 => 'param'], ['root-route' => '']]],
                            ])
                        ),
                    ]),
                ],
            ],
            [
                [
                    (new Route(['GET'], '/first/{param1}', null))->setParameter('static-first', ''),
                    (new Route(['GET'], '/{param1}/{param2}', null))->where(['param1', 'param2'], Pattern::ANY)->setParameter('dynamic', ''),
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
                                        [['GET', 'HEAD'], [[0 => 'param1'], ['static-first' => '']]],
                                    ])
                                ),
                            ])
                        ),
                        (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode(
                            [0 => new RegexMatcher(Pattern::ANY, 0)],
                            new ChildrenNodeCollection([
                                (new RegexMatcher(Pattern::ANY, 1))->getHash() => new RouteTreeNode(
                                    [1 => new RegexMatcher(Pattern::ANY, 1)],
                                    new MatchedRouteDataMap([
                                        [['GET', 'HEAD'], [[0 => 'param1', 1 => 'param2'], ['dynamic' => '']]],
                                    ])
                                ),
                            ])
                        ),
                    ]),
                ],
            ],
            [
                [
                    (new Route('ANY', '', null))->setParameter('home', ''),
                    (new Route('ANY', '/main', null))->setParameter('main.root', ''),
                    (new Route(['GET'], '/main/place', null))->setParameter('main.place-get', ''),
                    (new Route(['POST'], '/main/place', null))->setParameter('main.place-post', ''),
                    (new Route('ANY', '/main/thing', null))->setParameter('main.thing', ''),
                    (new Route('ANY', '/main/thing/abc', null))->setParameter('main.thing.abc', ''),
                    (new Route('ANY', '/user/{name}', null))->where('name', Pattern::ANY)->setParameter('user.show', ''),
                    (new Route('ANY', '/user/{name}/edit', null))->where('name', Pattern::ANY)->setParameter('user.edit', ''),
                    (new Route('ANY', '/user/create', null))->setParameter('user.create', ''),
                ],
                new MatchedRouteDataMap([], [[], ['home' => '']]),
                [
                    1 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode(
                            [0 => new StaticMatcher('main')],
                            new MatchedRouteDataMap([], [[], ['main.root' => '']])
                        ),
                    ]),
                    2 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('main')], new ChildrenNodeCollection([
                            (new StaticMatcher('place'))->getHash() => new RouteTreeNode(
                                [1 => new StaticMatcher('place')],
                                new MatchedRouteDataMap([
                                    [['GET', 'HEAD'], [[], ['main.place-get' => '']]],
                                    [['POST'], [[], ['main.place-post' => '']]],
                                ])
                            ),
                            (new StaticMatcher('thing'))->getHash() => new RouteTreeNode(
                                [1 => new StaticMatcher('thing')],
                                new MatchedRouteDataMap([], [[], ['main.thing' => '']])
                            ),
                        ])),
                        (new StaticMatcher('user'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('user')], new ChildrenNodeCollection([
                            (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode(
                                [1 => new RegexMatcher(Pattern::ANY, 0)],
                                new MatchedRouteDataMap([], [[0 => 'name'], ['user.show' => '']])
                            ),
                            (new StaticMatcher('create'))->getHash() => new RouteTreeNode(
                                [1 => new StaticMatcher('create')],
                                new MatchedRouteDataMap([], [[], ['user.create' => '']])
                            ),
                        ])),
                    ]),
                    3 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('main')], new ChildrenNodeCollection([
                            (new StaticMatcher('thing'))->getHash() => new RouteTreeNode([1 => new StaticMatcher('thing')], new ChildrenNodeCollection([
                                (new StaticMatcher('abc'))->getHash() => new RouteTreeNode(
                                    [2 => new StaticMatcher('abc')],
                                    new MatchedRouteDataMap([], [[], ['main.thing.abc' => '']])
                                ),
                            ])),
                        ])),
                        (new StaticMatcher('user'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('user')], new ChildrenNodeCollection([
                            (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode([1 => new RegexMatcher(Pattern::ANY, 0)], new ChildrenNodeCollection([
                                (new StaticMatcher('edit'))->getHash() => new RouteTreeNode(
                                    [2 => new StaticMatcher('edit')],
                                    new MatchedRouteDataMap([], [[0 => 'name'], ['user.edit' => '']])
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
