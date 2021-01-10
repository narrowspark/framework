<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing\Tests\TreeGenerator;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Routing\Matcher\AnyMatcher;
use Viserio\Component\Routing\Matcher\CompoundMatcher;
use Viserio\Component\Routing\Matcher\ExpressionMatcher;
use Viserio\Component\Routing\Matcher\RegexMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;
use Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection;
use Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap;
use Viserio\Component\Routing\TreeGenerator\Optimizer\RouteTreeOptimizer;
use Viserio\Component\Routing\TreeGenerator\RouteTreeNode;
use Viserio\Contract\Routing\Pattern;
use Viserio\Contract\Routing\SegmentMatcher as SegmentMatcherContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class RouteTreeOptimizerTest extends MockeryTestCase
{
    public static function provideRouteTreeOptimizerCases(): iterable
    {
        return [
            [
                'original' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([])),
                    ]),
                ]],
                'optimized' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([])),
                    ]),
                ]],
            ],
            [
                'original' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([
                            new RouteTreeNode([1 => new StaticMatcher('deg')], new ChildrenNodeCollection([
                            ])),
                        ])),
                    ]),
                ]],
                'optimized' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc'), 1 => new StaticMatcher('deg')], new ChildrenNodeCollection([
                        ])),
                    ]),
                ]],
            ],
            [
                'original' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([
                            new RouteTreeNode([1 => new StaticMatcher('def')], new ChildrenNodeCollection([
                                new RouteTreeNode([2 => new StaticMatcher('ghi')], new ChildrenNodeCollection([
                                ])),
                            ])),
                        ])),
                    ]),
                ]],
                'optimized' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc'), 1 => new StaticMatcher('def'), 2 => new StaticMatcher('ghi')], new ChildrenNodeCollection([
                        ])),
                    ]),
                ]],
            ],
            [
                'original' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([
                            new RouteTreeNode([1 => new StaticMatcher('def'), 2 => new StaticMatcher('ghi')], new ChildrenNodeCollection([
                            ])),
                        ])),
                    ]),
                ]],
                'optimized' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc'), 1 => new StaticMatcher('def'), 2 => new StaticMatcher('ghi')], new ChildrenNodeCollection([
                        ])),
                    ]),
                ]],
            ],
            [
                // Should optimize common regex patterns to more efficient PHP equivalents
                'original' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                new RegexMatcher(Pattern::ANY, 0),
                                new RegexMatcher(Pattern::DIGITS, 1),
                                new RegexMatcher(Pattern::ALPHA, 2),
                                new RegexMatcher(Pattern::ALPHA_LOWER, 3),
                                new RegexMatcher(Pattern::ALPHA_UPPER, 4),
                                new RegexMatcher(Pattern::ALPHA_NUM, 5),
                                new RegexMatcher(Pattern::ALPHA_NUM_DASH, 6),
                                new RegexMatcher('some\-custom\-pattern!{1,100}', 7),
                            ],
                            new ChildrenNodeCollection([])
                        ),
                    ]),
                ]],
                'optimized' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                new AnyMatcher([0]),
                                new ExpressionMatcher('ctype_digit({segment})', [1]),
                                new ExpressionMatcher('ctype_alpha({segment})', [2]),
                                new ExpressionMatcher('ctype_lower({segment})', [3]),
                                new ExpressionMatcher('ctype_upper({segment})', [4]),
                                new ExpressionMatcher('ctype_alnum({segment})', [5]),
                                new ExpressionMatcher('ctype_alnum(str_replace(\'-\', \'\', {segment}))', [6]),
                                new RegexMatcher('some\-custom\-pattern!{1,100}', 7),
                            ],
                            new ChildrenNodeCollection([])
                        ),
                    ]),
                ]],
            ],
            [
                // Should order checks from least expensive to most expensive
                'original' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                0 => $customSegmentMatcher = Mockery::mock(SegmentMatcherContract::class),
                                1 => new ExpressionMatcher('some_expression({segment})', [0]),
                                2 => new StaticMatcher('fdsf'),
                                3 => new AnyMatcher([2]),
                                4 => new RegexMatcher('[1-5a-g]+', 3),
                                5 => new ExpressionMatcher('some_other_expression({segment})', [4]),
                                6 => new AnyMatcher([5]),
                                7 => new StaticMatcher('aqw'),
                            ],
                            new ChildrenNodeCollection([])
                        ),
                    ]),
                ]],
                'optimized' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                3 => new AnyMatcher([2]),
                                6 => new AnyMatcher([5]),
                                2 => new StaticMatcher('fdsf'),
                                7 => new StaticMatcher('aqw'),
                                1 => new ExpressionMatcher('some_expression({segment})', [0]),
                                5 => new ExpressionMatcher('some_other_expression({segment})', [4]),
                                4 => new RegexMatcher('[1-5a-g]+', 3),
                                0 => $customSegmentMatcher,
                            ],
                            new ChildrenNodeCollection([])
                        ),
                    ]),
                ]],
            ],
            [
                'original' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([
                            new RouteTreeNode([1 => new RegexMatcher('[abc]+', 0)], new ChildrenNodeCollection([
                                new RouteTreeNode([2 => new StaticMatcher('def')], new ChildrenNodeCollection([
                                ])),
                            ])),
                        ])),
                    ]),
                ]],
                'optimized' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                0 => new StaticMatcher('abc'),
                                2 => new StaticMatcher('def'),
                                1 => new RegexMatcher('[abc]+', 0),
                            ],
                            new ChildrenNodeCollection([])
                        ),
                    ]),
                ]],
            ],
            [
                // Should factor out common matchers into a parent node
                'original' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                0 => new StaticMatcher('abc'),
                                1 => new AnyMatcher([0]),
                                2 => new StaticMatcher('1'),
                                3 => new StaticMatcher('def'),
                            ],
                            new ChildrenNodeCollection([])
                        ),
                        new RouteTreeNode(
                            [
                                0 => new StaticMatcher('abc'),
                                1 => new AnyMatcher([0]),
                                2 => new StaticMatcher('1'),
                                3 => new StaticMatcher('def'),
                            ],
                            new ChildrenNodeCollection([])
                        ),
                        new RouteTreeNode(
                            [
                                0 => new StaticMatcher('abc'),
                                1 => new AnyMatcher([0]),
                                2 => new StaticMatcher('3'),
                                3 => new StaticMatcher('def'),
                            ],
                            new ChildrenNodeCollection([])
                        ),
                    ]),
                ]],
                'optimized' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                1 => new AnyMatcher([0]),
                                0 => new StaticMatcher('abc'),
                                3 => new StaticMatcher('def'),
                            ],
                            new ChildrenNodeCollection([
                                new RouteTreeNode(
                                    [
                                        2 => new StaticMatcher('1'),
                                    ],
                                    new ChildrenNodeCollection([])
                                ),
                                new RouteTreeNode(
                                    [
                                        2 => new StaticMatcher('3'),
                                    ],
                                    new ChildrenNodeCollection([])
                                ),
                            ])
                        ),
                    ]),
                ]],
            ],
            [
                'original' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [0 => new StaticMatcher('abc')],
                            new ChildrenNodeCollection([
                                new RouteTreeNode(
                                    [0 => new StaticMatcher('def')],
                                    new ChildrenNodeCollection([])
                                ),
                            ])
                        ),
                    ]),
                ]],
                'optimized' => [null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [0 => new CompoundMatcher([new StaticMatcher('abc'), new StaticMatcher('def')])],
                            new ChildrenNodeCollection([])
                        ),
                    ]),
                ]],
            ],
            [
                'original' => [null, [
                    2 => new ChildrenNodeCollection([
                        (new StaticMatcher('first'))->getHash() => new RouteTreeNode(
                            [0 => new StaticMatcher('first')],
                            new ChildrenNodeCollection([
                                (new RegexMatcher(Pattern::ANY, 0))->getHash() => new RouteTreeNode(
                                    [1 => new RegexMatcher(Pattern::ANY, 0)],
                                    new MatchedRouteDataMap([
                                        [['GET', 'HEAD'], [[0 => 'param1'], ['static-first']]],
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
                                        [
                                            ['GET', 'HEAD'],
                                            [[0 => 'param1', 1 => 'param2'], ['dynamic']],
                                        ],
                                    ])
                                ),
                            ])
                        ),
                    ]),
                ]],
                'optimized' => [null, [
                    2 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [1 => new AnyMatcher([0, 1])],
                            new ChildrenNodeCollection([
                                new RouteTreeNode(
                                    [0 => new StaticMatcher('first')],
                                    new MatchedRouteDataMap([
                                        [['GET', 'HEAD'], [[0 => 'param1'], ['static-first']]],
                                    ])
                                ),
                                new RouteTreeNode(
                                    [0 => new AnyMatcher([0])],
                                    new MatchedRouteDataMap([
                                        [['GET', 'HEAD'], [[0 => 'param1', 1 => 'param2'], ['dynamic']]],
                                    ])
                                ),
                            ])
                        ),
                    ]),
                ]],
            ],
        ];
    }

    /**
     * @dataProvider provideRouteTreeOptimizerCases
     */
    public function testRouteTreeOptimizer(array $original, array $expected): void
    {
        self::assertEquals($expected, (new RouteTreeOptimizer())->optimize($original));
    }
}
