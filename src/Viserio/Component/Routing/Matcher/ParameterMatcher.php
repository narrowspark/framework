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

namespace Viserio\Component\Routing\Matcher;

use Viserio\Contract\Routing\SegmentMatcher as SegmentMatcherContract;

class ParameterMatcher
{
    /**
     * Segments names.
     *
     * @var array
     */
    protected $names;

    /**
     * A regex string.
     *
     * @var string
     */
    protected $regex;

    /**
     * Create a new any matcher instance.
     *
     * @param array|string $names
     */
    public function __construct($names, string $regex)
    {
        $this->names = (array) $names;
        $this->regex = $regex;
    }

    /**
     * Returns an equivalent segment matcher and adds the parameters to the map.
     */
    public function getMatcher(array &$parameterIndexNameMap): SegmentMatcherContract
    {
        $parameterKey = \count($parameterIndexNameMap) === 0 ? 0 : \max(\array_keys($parameterIndexNameMap)) + 1;
        $parameterKeyGroupMap = [];
        $group = 0;

        foreach ($this->names as $name) {
            $parameterIndexNameMap[$parameterKey] = $name;
            $parameterKeyGroupMap[$parameterKey] = $group++;
            $parameterKey++;
        }

        return new RegexMatcher($this->regex, $parameterKeyGroupMap);
    }
}
