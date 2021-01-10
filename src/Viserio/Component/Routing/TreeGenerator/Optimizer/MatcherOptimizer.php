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

namespace Viserio\Component\Routing\TreeGenerator\Optimizer;

use Viserio\Component\Routing\Matcher\AnyMatcher;
use Viserio\Component\Routing\Matcher\CompoundMatcher;
use Viserio\Component\Routing\Matcher\ExpressionMatcher;
use Viserio\Component\Routing\Matcher\RegexMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;
use Viserio\Contract\Routing\Pattern;
use Viserio\Contract\Routing\SegmentMatcher as SegmentMatcherContract;

final class MatcherOptimizer
{
    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Merge same matcher together.
     */
    public static function mergeMatchers(array $parentMatchers, array $childMatchers): array
    {
        $mergedMatchers = $parentMatchers;

        foreach ($childMatchers as $segment => $childMatcher) {
            if (isset($mergedMatchers[$segment])) {
                $mergedMatchers[$segment] = new CompoundMatcher([$mergedMatchers[$segment], $childMatcher]);
            } else {
                $mergedMatchers[$segment] = $childMatcher;
            }
        }

        return $mergedMatchers;
    }

    /**
     * Optimize matcher and matcher order.
     */
    public static function optimizeMatchers(array $matchers): array
    {
        foreach ($matchers as $key => $matcher) {
            $matchers[$key] = self::optimizeMatcher($matcher);
        }

        return self::optimizeMatcherOrder($matchers);
    }

    /**
     * Change matcher for a faster one, if available.
     */
    private static function optimizeMatcher(SegmentMatcherContract $matcher): SegmentMatcherContract
    {
        if ($matcher instanceof RegexMatcher && $matcher->getGroupCount() === 1) {
            $parameterKeys = $matcher->getParameterKeys();

            switch ($matcher->getRegex()) {
                case '/^(' . Pattern::ANY . ')$/':
                    return new AnyMatcher($parameterKeys);
                case '/^(' . Pattern::DIGITS . ')$/':
                    return new ExpressionMatcher('ctype_digit({segment})', $parameterKeys);
                case '/^(' . Pattern::ALPHA . ')$/':
                    return new ExpressionMatcher('ctype_alpha({segment})', $parameterKeys);
                case '/^(' . Pattern::ALPHA_LOWER . ')$/':
                    return new ExpressionMatcher('ctype_lower({segment})', $parameterKeys);
                case '/^(' . Pattern::ALPHA_UPPER . ')$/':
                    return new ExpressionMatcher('ctype_upper({segment})', $parameterKeys);
                case '/^(' . Pattern::ALPHA_NUM . ')$/':
                    return new ExpressionMatcher('ctype_alnum({segment})', $parameterKeys);
                case '/^(' . Pattern::ALPHA_NUM_DASH . ')$/':
                    return new ExpressionMatcher('ctype_alnum(str_replace(\'-\', \'\', {segment}))', $parameterKeys);

                default:
                    return $matcher;
            }
        }

        return $matcher;
    }

    /**
     * Optimizing the matcher order, unknown types are added last.
     */
    private static function optimizeMatcherOrder(array $matchers): array
    {
        $computationalCostOrder = [
            AnyMatcher::class,
            StaticMatcher::class,
            ExpressionMatcher::class,
            RegexMatcher::class,
            // Unknown types last
            SegmentMatcherContract::class,
        ];

        $groups = [];

        foreach ($computationalCostOrder as $type) {
            foreach ($matchers as $index => $matcher) {
                if ($matcher instanceof $type) {
                    unset($matchers[$index]);
                    $groups[$index] = $matcher;
                }
            }
        }

        return $groups;
    }
}
